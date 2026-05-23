<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\Frontend\Contracts\AdminAccessCheckerInterface;
use Capell\HtmlCache\Models\CachedModelUrl;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    Config::set('capell-frontend-authoring.enabled', true);
});

function fakeAdminAccessChecker(bool $isAdmin = true): void
{
    app()->instance(AdminAccessCheckerInterface::class, new readonly class($isAdmin) implements AdminAccessCheckerInterface
    {
        public function __construct(private bool $isAdmin) {}

        public function isAdmin(Authenticatable $user): bool
        {
            return $this->isAdmin;
        }
    });
}

it('returns 404 if no site domain', function (): void {
    $user = User::factory()->create(['name' => 'Test User']);
    actingAs($user);

    $response = postJson(route('capell-frontend.beacon'), [
        'url' => 'https://example.com/foo',
    ]);

    $response->assertStatus(404);
});

it('returns csrf token and user info for authenticated user', function (): void {
    $user = User::factory()->create(['name' => 'Test User']);
    actingAs($user);

    $site = Site::factory()->create();
    $language = Language::factory()->create();
    $siteDomain = SiteDomain::factory()->for($site)->for($language)->create();

    $response = postJson(route('capell-frontend.beacon'), [
        'url' => $siteDomain->full_url,
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'csrf_token',
        'user' => ['id', 'name'],
    ]);
    $response->assertJson(['user' => ['id' => $user->getKey(), 'name' => 'Test User']]);
});

it('returns beacon scripts for admin user with url', function (): void {
    $user = User::factory()->create(['name' => 'Test User']);
    actingAs($user);

    fakeAdminAccessChecker();

    $site = Site::factory()->create();
    $language = Language::factory()->create();
    SiteDomain::factory()->for($site)->for($language)->create();
    $page = Page::factory()->site($site)->create();
    PageUrl::factory()->for($site)->for($language)->page($page)->create();

    $response = postJson(route('capell-frontend.beacon'), [
        'url' => $page->pageUrl->full_url,
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'scripts',
    ]);

    expect($response->json('scripts.0'))
        ->toContain('CapellFrontendAuthoring')
        ->toContain('edit_url')
        ->toContain('.capell-authoring-region:hover > .capell-authoring-button')
        ->toContain('capell-authoring-toolbar')
        ->toContain('Admin editing')
        ->toContain('Not cached');
});

it('returns page and html cache context in admin authoring banner script', function (): void {
    $user = User::factory()->create(['name' => 'Test User']);
    actingAs($user);

    fakeAdminAccessChecker();

    $site = Site::factory()->create();
    $language = Language::factory()->create();
    SiteDomain::factory()->for($site)->for($language)->create();
    $page = Page::factory()->site($site)->create(['name' => 'Company page']);
    $page->forceFill([
        'updated_by' => $user->getKey(),
        'updated_at' => now()->subMinutes(12),
    ])->saveQuietly();
    $pageUrl = PageUrl::factory()->for($site)->for($language)->page($page)->create([
        'url' => '/company',
    ]);

    CachedModelUrl::query()->create([
        'url' => $pageUrl->full_url,
        'url_hash' => CachedModelUrl::hashUrl($pageUrl->full_url),
        'path' => $pageUrl->url,
        'site_id' => $site->getKey(),
        'site_domain_id' => $pageUrl->siteDomain?->getKey(),
        'language_id' => $language->getKey(),
        'cacheable_type' => $page->getMorphClass(),
        'cacheable_id' => $page->getKey(),
        'cached_at' => now()->subMinutes(5),
        'last_seen_at' => now()->subMinutes(5),
    ]);

    $response = postJson(route('capell-frontend.beacon'), [
        'url' => $pageUrl->full_url,
    ]);

    $response->assertOk();

    expect($response->json('scripts.0'))
        ->toContain('Company page')
        ->toContain('/company')
        ->toContain('HTML cached')
        ->toContain('Test User')
        ->toContain('--capell-authoring-bottom-offset');
});

it('escapes hostile page and editor names in admin authoring banner script', function (): void {
    $user = User::factory()->create([
        'name' => 'Editor <script>alert("editor")</script>',
    ]);
    actingAs($user);

    fakeAdminAccessChecker();

    $site = Site::factory()->create();
    $language = Language::factory()->create();
    SiteDomain::factory()->for($site)->for($language)->create();
    $page = Page::factory()->site($site)->create([
        'name' => '<img src=x onerror=alert("page")>',
    ]);
    $page->forceFill([
        'updated_by' => $user->getKey(),
        'updated_at' => now()->subMinutes(12),
    ])->saveQuietly();
    $pageUrl = PageUrl::factory()->for($site)->for($language)->page($page)->create([
        'url' => '/hostile',
    ]);

    $response = postJson(route('capell-frontend.beacon'), [
        'url' => $pageUrl->full_url,
    ]);

    $response->assertOk();

    $script = $response->json('scripts.0');

    expect($script)
        ->not->toContain('<img src=x')
        ->not->toContain('<script>alert')
        ->toContain('textContent = String(text)')
        ->toContain("appendToolbarText(content, 'capell-authoring-toolbar__page', bannerContext.page")
        ->toContain('\u003Cimg src=x')
        ->toContain('\u003Cscript\u003Ealert');
});

it('does not return authoring scripts for cross-origin admin beacons', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    fakeAdminAccessChecker();

    $site = Site::factory()->create();
    $language = Language::factory()->create();
    SiteDomain::factory()->for($site)->for($language)->create();
    $page = Page::factory()->site($site)->create();
    PageUrl::factory()->for($site)->for($language)->page($page)->create();

    $response = postJson(route('capell-frontend.beacon'), [
        'url' => $page->pageUrl->full_url,
    ], [
        'Origin' => 'https://attacker.example',
        'Sec-Fetch-Site' => 'cross-site',
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['csrf_token']);
    $response->assertJsonMissingPath('scripts');
    $response->assertJsonMissingPath('user');

    expect($response->getContent())
        ->not->toContain('CapellFrontendAuthoring')
        ->not->toContain('capell-authoring')
        ->not->toContain('edit_url')
        ->not->toContain('recordKey');
});

it('does not return authoring scripts or metadata for non-admin authenticated user', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    fakeAdminAccessChecker(false);

    $site = Site::factory()->create();
    $language = Language::factory()->create();
    SiteDomain::factory()->for($site)->for($language)->create();
    $page = Page::factory()->site($site)->create();
    PageUrl::factory()->for($site)->for($language)->page($page)->create();

    $response = postJson(route('capell-frontend.beacon'), [
        'url' => $page->pageUrl->full_url,
    ]);

    $response->assertOk();
    $response->assertJsonMissingPath('scripts');
    $response->assertJsonMissingPath('editable_regions');
    $response->assertJsonMissingPath('editor_html');

    expect($response->getContent())
        ->not->toContain('CapellFrontendAuthoring')
        ->not->toContain('capell-authoring')
        ->not->toContain('edit_url')
        ->not->toContain('recordKey')
        ->not->toContain('meta.description');
});

it('returns only csrf token for guest', function (): void {
    $site = Site::factory()->create();
    $language = Language::factory()->create();
    $siteDomain = SiteDomain::factory()->for($site)->for($language)->create();

    $response = postJson(route('capell-frontend.beacon'), [
        'url' => $siteDomain->full_url,
    ]);

    $response->assertOk();
    $response->assertJsonStructure(['csrf_token']);
    $response->assertJsonMissing(['user']);

    expect($response->getContent())
        ->not->toContain('CapellFrontendAuthoring')
        ->not->toContain('capell-authoring')
        ->not->toContain('edit_url')
        ->not->toContain('recordKey')
        ->not->toContain('meta.description');
});

it('rejects oversized beacon urls', function (): void {
    $response = postJson(route('capell-frontend.beacon'), [
        'url' => 'https://example.com/' . str_repeat('a', 2049),
    ]);

    $response->assertUnprocessable();
    $response->assertJsonValidationErrors(['url']);
});

it('throttles beacon requests', function (): void {
    foreach (range(1, 60) as $requestNumber) {
        $response = postJson(route('capell-frontend.beacon'), [
            'url' => 'https://example.com/foo',
        ]);

        $response->assertOk();
    }

    $response = postJson(route('capell-frontend.beacon'), [
        'url' => 'https://example.com/foo',
    ]);

    $response->assertTooManyRequests();
});

it('renders page data without throwing when configured beacon route is missing', function (): void {
    Config::set('capell-page.frontend.route_name', 'capell-frontend.missing-beacon');

    $rendered = view('capell::components.page-data')->render();

    expect($rendered)->toContain('"url":null');
});

it('page data does not render authoring metadata into cached html', function (): void {
    $rendered = view('capell::components.page-data')->render();

    expect($rendered)
        ->not->toContain('editable_regions')
        ->not->toContain('edit_url')
        ->not->toContain('capell-authoring')
        ->not->toContain('CapellFrontendAuthoring')
        ->not->toContain('recordKey')
        ->not->toContain('meta.description');
});
