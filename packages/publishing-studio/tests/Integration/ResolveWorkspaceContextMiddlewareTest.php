<?php

declare(strict_types=1);

use Capell\PublishingStudio\Http\Middleware\ResolveWorkspaceContext;
use Capell\PublishingStudio\Models\PreviewLink;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceContext;
use Capell\Tests\Fixtures\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Session\ArraySessionHandler;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

beforeEach(function (): void {
    WorkspaceContext::clear();

    Route::get('/_workspace-preview-test', fn (): Response => new Response('ok'))
        ->name('workspace-preview-test');
});

afterEach(function (): void {
    WorkspaceContext::clear();
});

function invokeWorkspaceMiddleware(Request $request): Response
{
    $middleware = new ResolveWorkspaceContext;

    return $middleware->handle($request, function (): Response {
        $response = new Response('ok');
        $response->headers->set('X-Workspace-Context-Id', (string) (WorkspaceContext::current()?->id ?? ''));

        return $response;
    });
}

it('resolves a workspace from a valid signed URL with preview token without dropping a persistent cookie', function (): void {
    $workspace = Workspace::factory()->create();
    $link = PreviewLink::query()->create([
        'workspace_id' => $workspace->id,
        'token' => PreviewLink::generateToken(),
        'issued_at' => CarbonImmutable::now(),
        'expires_at' => CarbonImmutable::now()->addHour(),
    ]);

    $signedUrl = URL::temporarySignedRoute(
        'workspace-preview-test',
        now()->addHour(),
        [
            ResolveWorkspaceContext::QUERY_PARAM => $workspace->uuid,
            ResolveWorkspaceContext::TOKEN_PARAM => $link->token,
        ],
    );

    $response = invokeWorkspaceMiddleware(Request::create($signedUrl));

    $responseCookies = array_filter(
        $response->headers->getCookies(),
        fn (Cookie $cookie): bool => $cookie->getName() === ResolveWorkspaceContext::COOKIE_NAME,
    );

    expect($response->headers->get('X-Workspace-Context-Id'))->toBe((string) $workspace->id)
        ->and(WorkspaceContext::current())->toBeNull()
        ->and($responseCookies)->toBeEmpty();
});

it('rejects a signed URL that carries a workspace uuid without a preview token', function (): void {
    $workspace = Workspace::factory()->create();

    $signedUrl = URL::temporarySignedRoute(
        'workspace-preview-test',
        now()->addHour(),
        [ResolveWorkspaceContext::QUERY_PARAM => $workspace->uuid],
    );

    $response = invokeWorkspaceMiddleware(Request::create($signedUrl));

    $responseCookies = array_filter(
        $response->headers->getCookies(),
        fn (Cookie $cookie): bool => $cookie->getName() === ResolveWorkspaceContext::COOKIE_NAME,
    );

    expect(WorkspaceContext::current())->toBeNull()
        ->and($responseCookies)->toBeEmpty();
});

it('rejects a preview token whose workspace uuid does not match the signed URL', function (): void {
    $workspaceA = Workspace::factory()->create();
    $workspaceB = Workspace::factory()->create();
    $link = PreviewLink::query()->create([
        'workspace_id' => $workspaceA->id,
        'token' => PreviewLink::generateToken(),
        'issued_at' => CarbonImmutable::now(),
        'expires_at' => CarbonImmutable::now()->addHour(),
    ]);

    $signedUrl = URL::temporarySignedRoute(
        'workspace-preview-test',
        now()->addHour(),
        [
            ResolveWorkspaceContext::QUERY_PARAM => $workspaceB->uuid,
            ResolveWorkspaceContext::TOKEN_PARAM => $link->token,
        ],
    );

    invokeWorkspaceMiddleware(Request::create($signedUrl));

    expect(WorkspaceContext::current())->toBeNull();
});

it('ignores a raw workspace cookie when no signed URL is present', function (): void {
    $workspace = Workspace::factory()->create();

    $request = Request::create('/_workspace-preview-test');
    $request->cookies->set(ResolveWorkspaceContext::COOKIE_NAME, $workspace->uuid);

    $response = invokeWorkspaceMiddleware($request);

    expect($response->headers->get('X-Workspace-Context-Id'))->toBe('')
        ->and(WorkspaceContext::current())->toBeNull();
});

it('does not refresh an unsigned workspace cookie', function (): void {
    $workspace = Workspace::factory()->create();

    $request = Request::create('/_workspace-preview-test');
    $request->cookies->set(ResolveWorkspaceContext::COOKIE_NAME, $workspace->uuid);

    $response = invokeWorkspaceMiddleware($request);

    $responseCookies = array_filter(
        $response->headers->getCookies(),
        fn (Cookie $cookie): bool => $cookie->getName() === ResolveWorkspaceContext::COOKIE_NAME,
    );

    expect($responseCookies)->toBeEmpty();
});

it('resolves a workspace from the session when no signed URL or cookie is present', function (): void {
    $workspace = Workspace::factory()->create();

    $sessionStore = new Store('test', new ArraySessionHandler(30));
    $sessionStore->put(ResolveWorkspaceContext::SESSION_KEY, $workspace->id);

    $request = Request::create('/_workspace-preview-test');
    $request->setLaravelSession($sessionStore);

    $response = invokeWorkspaceMiddleware($request);

    expect($response->headers->get('X-Workspace-Context-Id'))->toBe((string) $workspace->id)
        ->and(WorkspaceContext::current())->toBeNull();
});

it('falls back to null context when no hint is supplied', function (): void {
    $response = invokeWorkspaceMiddleware(Request::create('/_workspace-preview-test'));

    $responseCookies = array_filter(
        $response->headers->getCookies(),
        fn (Cookie $cookie): bool => $cookie->getName() === ResolveWorkspaceContext::COOKIE_NAME,
    );

    expect(WorkspaceContext::current())->toBeNull()
        ->and($responseCookies)->toBeEmpty();
});

it('ignores a signed URL with a bogus signature', function (): void {
    $workspace = Workspace::factory()->create();

    $request = Request::create(
        '/_workspace-preview-test?' . http_build_query([
            ResolveWorkspaceContext::QUERY_PARAM => $workspace->uuid,
            'signature' => 'bogus',
            'expires' => now()->addHour()->timestamp,
        ]),
    );

    invokeWorkspaceMiddleware($request);

    expect(WorkspaceContext::current())->toBeNull();
});

it('resolves workspace via valid preview link token and increments access metadata', function (): void {
    $workspace = Workspace::factory()->create();
    $link = PreviewLink::query()->create([
        'workspace_id' => $workspace->id,
        'token' => PreviewLink::generateToken(),
        'issued_at' => CarbonImmutable::now(),
        'expires_at' => CarbonImmutable::now()->addHour(),
    ]);

    $signedUrl = URL::temporarySignedRoute(
        'workspace-preview-test',
        now()->addHour(),
        [
            ResolveWorkspaceContext::QUERY_PARAM => $workspace->uuid,
            ResolveWorkspaceContext::TOKEN_PARAM => $link->token,
        ],
    );

    $response = invokeWorkspaceMiddleware(Request::create($signedUrl));

    $link->refresh();

    expect($response->headers->get('X-Workspace-Context-Id'))->toBe((string) $workspace->id)
        ->and(WorkspaceContext::current())->toBeNull()
        ->and($link->access_count)->toBe(1)
        ->and($link->last_accessed_at)->not->toBeNull();
});

it('marks preview workspace responses private and bypasses frontend cache only during the request', function (): void {
    config(['capell-core.disable_cache' => false]);

    $workspace = Workspace::factory()->create();
    $link = PreviewLink::query()->create([
        'workspace_id' => $workspace->id,
        'token' => PreviewLink::generateToken(),
        'issued_at' => CarbonImmutable::now(),
        'expires_at' => CarbonImmutable::now()->addHour(),
    ]);

    $signedUrl = URL::temporarySignedRoute(
        'workspace-preview-test',
        now()->addHour(),
        [
            ResolveWorkspaceContext::QUERY_PARAM => $workspace->uuid,
            ResolveWorkspaceContext::TOKEN_PARAM => $link->token,
        ],
    );

    $middleware = new ResolveWorkspaceContext;
    $response = $middleware->handle(
        Request::create($signedUrl),
        function (): Response {
            expect(config('capell-core.disable_cache'))->toBeTrue();
            expect(WorkspaceContext::current())->not->toBeNull();

            return new Response('ok');
        },
    );

    expect($response->headers->get('Cache-Control'))->toContain('private')
        ->and($response->headers->get('Cache-Control'))->toContain('no-store')
        ->and($response->headers->get('Pragma'))->toBe('no-cache')
        ->and($response->headers->get('Expires'))->toBe('0')
        ->and(WorkspaceContext::current())->toBeNull()
        ->and(config('capell-core.disable_cache'))->toBeFalse();
});

it('restores an existing workspace context after handling a preview request', function (): void {
    $previousWorkspace = Workspace::factory()->create();
    $workspace = Workspace::factory()->create();
    $link = PreviewLink::query()->create([
        'workspace_id' => $workspace->id,
        'token' => PreviewLink::generateToken(),
        'issued_at' => CarbonImmutable::now(),
        'expires_at' => CarbonImmutable::now()->addHour(),
    ]);

    WorkspaceContext::set($previousWorkspace);

    $signedUrl = URL::temporarySignedRoute(
        'workspace-preview-test',
        now()->addHour(),
        [
            ResolveWorkspaceContext::QUERY_PARAM => $workspace->uuid,
            ResolveWorkspaceContext::TOKEN_PARAM => $link->token,
        ],
    );

    $response = invokeWorkspaceMiddleware(Request::create($signedUrl));

    expect($response->headers->get('X-Workspace-Context-Id'))->toBe((string) $workspace->id)
        ->and(WorkspaceContext::current()?->id)->toBe($previousWorkspace->id);
});

it('rejects a revoked preview link token', function (): void {
    $workspace = Workspace::factory()->create();
    $link = PreviewLink::query()->create([
        'workspace_id' => $workspace->id,
        'token' => PreviewLink::generateToken(),
        'issued_at' => CarbonImmutable::now()->subHour(),
        'expires_at' => CarbonImmutable::now()->addHour(),
        'revoked_at' => CarbonImmutable::now()->subMinute(),
    ]);

    $signedUrl = URL::temporarySignedRoute(
        'workspace-preview-test',
        now()->addHour(),
        [
            ResolveWorkspaceContext::QUERY_PARAM => $workspace->uuid,
            ResolveWorkspaceContext::TOKEN_PARAM => $link->token,
        ],
    );

    invokeWorkspaceMiddleware(Request::create($signedUrl));

    expect(WorkspaceContext::current())->toBeNull();
});

it('rejects an expired preview link token even if the URL signature is still valid', function (): void {
    $workspace = Workspace::factory()->create();
    $link = PreviewLink::query()->create([
        'workspace_id' => $workspace->id,
        'token' => PreviewLink::generateToken(),
        'issued_at' => CarbonImmutable::now()->subHours(2),
        'expires_at' => CarbonImmutable::now()->subMinute(),
    ]);

    $signedUrl = URL::temporarySignedRoute(
        'workspace-preview-test',
        now()->addHour(),
        [
            ResolveWorkspaceContext::QUERY_PARAM => $workspace->uuid,
            ResolveWorkspaceContext::TOKEN_PARAM => $link->token,
        ],
    );

    invokeWorkspaceMiddleware(Request::create($signedUrl));

    expect(WorkspaceContext::current())->toBeNull();
});

it('ignores a cookie-supplied workspace for an authenticated user who lacks view permission', function (): void {
    $workspace = Workspace::factory()->create();

    $user = User::factory()->create();

    $request = Request::create('/_workspace-preview-test');
    $request->cookies->set(ResolveWorkspaceContext::COOKIE_NAME, $workspace->uuid);
    $request->setUserResolver(fn () => $user);

    invokeWorkspaceMiddleware($request);

    expect(WorkspaceContext::current())->toBeNull();
});

it('ignores a session-supplied workspace for an authenticated user who lacks view permission', function (): void {
    $workspace = Workspace::factory()->create();

    $user = User::factory()->create();

    $sessionStore = new Store('test', new ArraySessionHandler(30));
    $sessionStore->put(ResolveWorkspaceContext::SESSION_KEY, $workspace->id);

    $request = Request::create('/_workspace-preview-test');
    $request->setLaravelSession($sessionStore);
    $request->setUserResolver(fn () => $user);

    invokeWorkspaceMiddleware($request);

    expect(WorkspaceContext::current())->toBeNull();
});
