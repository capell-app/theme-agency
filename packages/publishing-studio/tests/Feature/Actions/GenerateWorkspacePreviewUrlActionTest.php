<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\PublishingStudio\Actions\GenerateWorkspacePreviewUrlAction;
use Capell\PublishingStudio\Http\Middleware\ResolveWorkspaceContext;
use Capell\PublishingStudio\Models\PreviewLink;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceContext;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpFoundation\Cookie;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    Route::get('/', fn (): string => 'ok')->name('capell-frontend.index');
    Route::get('{url}', fn (): string => 'ok')
        ->where('url', '.*')
        ->name('capell-frontend.page');

    $this->actingAsAdmin();
});

it('builds a temporary signed URL containing the workspace uuid for the home page', function (): void {
    $workspace = Workspace::factory()->create();

    $url = (new GenerateWorkspacePreviewUrlAction)->handle($workspace);

    expect($url)
        ->toContain(ResolveWorkspaceContext::QUERY_PARAM . '=' . $workspace->uuid)
        ->toContain('signature=')
        ->toContain('expires=');
});

it('uses the current frontend home route when it is registered', function (): void {
    Route::get('/home-route-preview', fn (): string => 'ok')->name('capell-frontend.home');

    $workspace = Workspace::factory()->create();

    $url = (new GenerateWorkspacePreviewUrlAction)->handle($workspace);

    expect(parse_url($url, PHP_URL_PATH))->toBe('/home-route-preview');
});

it('uses the configured preview home route when it is registered', function (): void {
    config()->set('capell.publishing-studio.preview.home_route', 'capell-frontend.custom-home');
    Route::get('/custom-home-route-preview', fn (): string => 'ok')->name('capell-frontend.custom-home');

    $workspace = Workspace::factory()->create();

    $url = (new GenerateWorkspacePreviewUrlAction)->handle($workspace);

    expect(parse_url($url, PHP_URL_PATH))->toBe('/custom-home-route-preview');
});

it('returns a signed URL using the page route for non-root paths', function (): void {
    $workspace = Workspace::factory()->create();

    $url = (new GenerateWorkspacePreviewUrlAction)->handle($workspace, '/about/team');

    expect($url)
        ->toContain('about/team')
        ->toContain(ResolveWorkspaceContext::QUERY_PARAM . '=' . $workspace->uuid)
        ->toContain('signature=');
});

it('resolves the signed URL through the middleware and sets the workspace context', function (): void {
    $workspace = Workspace::factory()->create();

    $url = (new GenerateWorkspacePreviewUrlAction)->handle($workspace);

    $request = Request::create($url);

    (new ResolveWorkspaceContext)->handle($request, function () use ($workspace): Response {
        expect(WorkspaceContext::currentId())->toBe($workspace->id);

        return new Response('ok');
    });
});

it('issues and resolves a workspace cookie only when it has a valid session-bound signature', function (): void {
    $workspace = Workspace::factory()->create();
    $url = (new GenerateWorkspacePreviewUrlAction)->handle($workspace);
    $session = new Store('testing', new ArraySessionHandler(120));
    $session->setId('session-one');

    $request = Request::create($url);
    $request->setLaravelSession($session);

    $response = (new ResolveWorkspaceContext)->handle($request, fn (): Response => new Response('ok'));
    $cookie = collect($response->headers->getCookies())
        ->first(fn (Cookie $cookie): bool => $cookie->getName() === ResolveWorkspaceContext::COOKIE_NAME);

    $cookie = publishingStudioTestInstance($cookie, Cookie::class);

    expect($cookie->getValue())->not->toBe($workspace->uuid)
        ->and($cookie->getValue())->toStartWith('v1|' . $workspace->uuid . '|');

    $followUpRequest = Request::create('/');
    $followUpRequest->setLaravelSession($session);
    $followUpRequest->cookies->set(ResolveWorkspaceContext::COOKIE_NAME, $cookie->getValue());

    (new ResolveWorkspaceContext)->handle($followUpRequest, function () use ($workspace): Response {
        expect(WorkspaceContext::currentId())->toBe($workspace->id);

        return new Response('ok');
    });

    $rawCookieRequest = Request::create('/');
    $rawCookieRequest->setLaravelSession($session);
    $rawCookieRequest->cookies->set(ResolveWorkspaceContext::COOKIE_NAME, $workspace->uuid);

    (new ResolveWorkspaceContext)->handle($rawCookieRequest, function (): Response {
        expect(WorkspaceContext::currentId())->toBeNull();

        return new Response('ok');
    });
});

it('persists a PreviewLink row and embeds its token in the signed URL', function (): void {
    $workspace = Workspace::factory()->create();

    $url = (new GenerateWorkspacePreviewUrlAction)->handle($workspace);

    $link = PreviewLink::query()->where('workspace_id', $workspace->id)->firstOrFail();

    expect($url)
        ->toContain(ResolveWorkspaceContext::TOKEN_PARAM . '=' . $link->token)
        ->and($link->isUsable())->toBeTrue()
        ->and($link->expires_at)->not->toBeNull();
});

it('requires preview authorization before issuing a signed preview URL', function (): void {
    Permission::findOrCreate('View:Workspace');

    $allowedSite = Site::factory()->create();
    $blockedSite = Site::factory()->create();
    $viewer = test()->createUserWithPermission('View:Workspace');
    $viewer->assignedSiteIds = collect([(int) $allowedSite->getKey()]);

    test()->actingAs($viewer);

    $workspace = Workspace::factory()->create();
    Page::factory()->create([
        'workspace_id' => $workspace->getKey(),
        'site_id' => $blockedSite->getKey(),
    ]);

    expect(fn (): string => (new GenerateWorkspacePreviewUrlAction)->handle($workspace))
        ->toThrow(AuthorizationException::class);

    expect(PreviewLink::query()->where('workspace_id', $workspace->getKey())->exists())->toBeFalse();
});
