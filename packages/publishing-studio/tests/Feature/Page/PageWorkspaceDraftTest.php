<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\Core\Models\SiteDomain;
use Capell\PublishingStudio\Actions\CopyOnWriteAction;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Http\Middleware\ResolveWorkspaceContext;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Publisher;
use Capell\PublishingStudio\WorkspaceContext;
use Capell\Tests\Support\Concerns\TestingFrontend;
use Carbon\CarbonImmutable;

use function Pest\Laravel\get;

uses(TestingFrontend::class)->group('page');

// ─── ExitWorkspacePreviewController ──────────────────────────────────────────

test('exit workspace preview redirects to root by default', function (): void {
    get(route('capell-frontend.preview.exit'))
        ->assertRedirect('/');
});

test('exit workspace preview redirects to specified path', function (): void {
    get(route('capell-frontend.preview.exit', ['redirect' => '/about']))
        ->assertRedirect('/about');
});

test('exit workspace preview refuses external redirects', function (): void {
    get(route('capell-frontend.preview.exit', ['redirect' => 'https://evil.example/phish']))
        ->assertRedirect('/');
});

test('exit workspace preview allows same host absolute redirects as local paths', function (): void {
    get(route('capell-frontend.preview.exit', ['redirect' => url('/about?tab=preview')]))
        ->assertRedirect('/about?tab=preview');
});

test('exit workspace preview clears the workspace cookie', function (): void {
    $workspace = Workspace::factory()->create();

    $response = $this
        ->withCookie(ResolveWorkspaceContext::COOKIE_NAME, $workspace->uuid)
        ->get(route('capell-frontend.preview.exit'));

    $response->assertRedirect('/');
    $response->assertCookieExpired(ResolveWorkspaceContext::COOKIE_NAME);
});

// ─── Workspace preview pill ───────────────────────────────────────────────────

test('workspace preview pill is not rendered for a raw workspace cookie', function (): void {
    $siteDomain = SiteDomain::factory()->default()->create();
    $page = Page::factory()->site($siteDomain->site)->withTranslations()->create();
    $workspace = Workspace::factory()->create(['name' => 'Sprint 2']);

    $this
        ->withCookie(ResolveWorkspaceContext::COOKIE_NAME, $workspace->uuid)
        ->get($page->pageUrl->full_url)
        ->assertOk()
        ->assertDontSee('Sprint 2')
        ->assertDontSee('workspace-preview-pill');
});

test('workspace preview pill is not rendered without workspace cookie', function (): void {
    $siteDomain = SiteDomain::factory()->default()->create();
    $page = Page::factory()->site($siteDomain->site)->withTranslations()->create();

    get($page->pageUrl->full_url)
        ->assertOk()
        ->assertDontSee('workspace-preview-pill');
});

// ─── Post-publish: live page accessible ──────────────────────────────────────

test('live page is accessible after workspace containing its draft is published', function (): void {
    $siteDomain = SiteDomain::factory()->default()->create();
    $page = Page::factory()->site($siteDomain->site)->withTranslations()->create();
    $url = $page->pageUrl->full_url;

    // Create a workspace draft of this page
    $workspace = Workspace::factory()->create(['status' => WorkspaceStatusEnum::Approved]);
    (new CopyOnWriteAction)->cloneForEdit(
        $page->fresh()->fill(['name' => 'Updated Name']),
        $workspace,
    );

    // Publish the workspace — publisher replaces live with the draft
    resolve(Publisher::class)->publish($workspace);

    // The URL should still resolve and return 200
    get($url)->assertOk();
});

test('workspace draft row is not rendered via its live url without workspace context', function (): void {
    $siteDomain = SiteDomain::factory()->default()->create();
    $page = Page::factory()->site($siteDomain->site)->withTranslations()->create();

    $workspace = Workspace::factory()->create(['status' => WorkspaceStatusEnum::Open]);
    (new CopyOnWriteAction)->cloneForEdit(
        $page->fresh()->fill(['name' => 'Draft Name']),
        $workspace,
    );

    // Without workspace context, the live URL still serves the live page
    // (the live row is still accessible even when shadowed — only in workspace
    // context does the shadow hide it). The key is no 404.
    get($page->pageUrl->full_url)->assertOk();
});

test('anonymous live page does not expose workspace markers or embargoed draft content', function (): void {
    $siteDomain = SiteDomain::factory()->default()->create();
    $page = Page::factory()
        ->site($siteDomain->site)
        ->withTranslations(data: [
            'title' => 'Live public page',
            'content' => '<p>Published public copy remains visible.</p>',
        ])
        ->create();

    $workspace = Workspace::factory()->approved()->create([
        'name' => 'Embargoed launch workspace',
        'embargo_until' => CarbonImmutable::now()->addDay(),
    ]);

    WorkspaceContext::runWith($workspace, function () use ($page): void {
        $draftTranslation = $page->translation()->firstOrFail();

        $draftTranslation->forceFill([
            'title' => 'Embargoed draft title',
            'content' => '<p>Embargoed draft copy must stay private.</p>',
        ])->save();
    });

    $response = get($page->pageUrl->full_url);
    $cacheControl = (string) $response->baseResponse->headers->get('Cache-Control');

    $response
        ->assertOk()
        ->assertSee('Published public copy remains visible.')
        ->assertDontSee('Embargoed launch workspace')
        ->assertDontSee('Embargoed draft title')
        ->assertDontSee('Embargoed draft copy must stay private.')
        ->assertDontSee('workspace-preview-pill')
        ->assertDontSee('data-workspace-preview')
        ->assertCookieMissing(ResolveWorkspaceContext::COOKIE_NAME);

    expect($response->baseResponse->headers->get('Pragma'))->toBeNull()
        ->and($response->baseResponse->headers->get('Expires'))->toBeNull()
        ->and($cacheControl)->not->toContain('private')
        ->and($cacheControl)->not->toContain('no-store');
});
