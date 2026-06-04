<?php

declare(strict_types=1);

use Capell\AgentDelivery\Tests\AgentDeliveryTestCase;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\getJson;

uses(AgentDeliveryTestCase::class);

it('lists agent-delivery pages for the resolved site and language', function (): void {
    [$firstUrl, $firstPage, $language, $site] = createIndexTestPage('/first-page', [
        'title' => 'First Page',
        'content' => '<p>First public body.</p>',
    ]);
    createIndexTestPage('/second-page', [
        'title' => 'Second Page',
        'content' => '<p>Second public body.</p>',
    ], site: $site, language: $language);
    createIndexTestPage('/excluded-page', [
        'title' => 'Excluded Page',
        'content' => '<p>Excluded body.</p>',
        'pageMeta' => ['agent_delivery' => ['enabled' => false]],
    ], site: $site, language: $language);

    $response = getJson(indexTestUrl('capell-agent-delivery.pages.index'))
        ->assertOk()
        ->assertHeader('Cache-Control', 'max-age=300, public')
        ->assertHeader('ETag');

    $data = $response->json('data');

    throw_unless(is_array($data), RuntimeException::class, 'Expected page index data to be an array.');

    $firstEntry = collect(array_values($data))
        ->firstWhere('canonicalUrl', 'http://example.com/first-page');

    throw_unless(is_array($firstEntry), RuntimeException::class, 'Expected first page entry to be present.');

    $canonicalUrls = collect(array_values($data))->pluck('canonicalUrl');

    expect($canonicalUrls)
        ->toContain('http://example.com/first-page')
        ->toContain('http://example.com/second-page')
        ->not->toContain('http://example.com/excluded-page')
        ->and($firstEntry['url'] ?? null)->toBe($firstUrl->url)
        ->and($firstEntry['language'] ?? null)->toBe('en')
        ->and($firstEntry['lastUpdatedAt'] ?? null)->toBe($firstPage->updated_at?->toIso8601String())
        ->and($firstEntry['manifestUrl'] ?? null)->toBe('http://example.com/api/capell/agent/v1/pages/manifest?url=%2Ffirst-page&locale=en')
        ->and($response->json('meta.count'))->toBe($canonicalUrls->count());
});

it('returns a 304 response when page index etag matches', function (): void {
    createIndexTestPage('/cache-index');

    $response = getJson(indexTestUrl('capell-agent-delivery.pages.index'))
        ->assertOk()
        ->assertHeader('ETag');

    getJson(indexTestUrl('capell-agent-delivery.pages.index'), [
        'If-None-Match' => indexResponseHeader($response, 'ETag'),
    ])
        ->assertStatus(304)
        ->assertHeader('ETag', indexResponseHeader($response, 'ETag'));
});

it('resolves manifests for a requested locale override', function (): void {
    $english = Language::factory()->english()->create();
    $french = Language::factory()->state(['code' => 'fr', 'locale' => 'fr'])->create();
    $site = Site::factory()->default()->create(['language_id' => $english->id]);
    SiteDomain::factory()->site($site)->language($english)->create([
        'domain' => 'example.com',
        'path' => null,
        'scheme' => null,
    ]);
    SiteDomain::factory()->site($site)->language($french)->create([
        'domain' => 'example.com',
        'path' => '/fr',
        'scheme' => null,
    ]);

    $page = Page::factory()
        ->site($site)
        ->published()
        ->withTranslations([$english, $french], [
            $english->id => [
                'title' => 'English Page',
                'content' => '<p>English body.</p>',
                'meta' => ['description' => 'English summary'],
            ],
            $french->id => [
                'title' => 'French Page',
                'content' => '<p>French body.</p>',
                'meta' => ['description' => 'French summary'],
            ],
        ])
        ->create();

    PageUrl::factory()->site($site)->language($english)->page($page)->create(['url' => '/guide']);
    PageUrl::factory()->site($site)->language($french)->page($page)->create(['url' => '/guide']);

    getJson(indexTestUrl('capell-agent-delivery.pages.manifest', ['url' => '/guide', 'locale' => 'fr']))
        ->assertOk()
        ->assertJsonPath('data.language', 'fr')
        ->assertJsonPath('data.title', 'French Page')
        ->assertJsonPath('data.summary', 'French summary')
        ->assertJsonPath('data.canonicalUrl', 'http://example.com/fr/guide');
});

it('keeps manifest query count under the frontend budget for a normal page', function (): void {
    [$pageUrl] = createIndexTestPage('/budget-page');

    DB::flushQueryLog();
    DB::enableQueryLog();

    getJson(indexTestUrl('capell-agent-delivery.pages.manifest', ['url' => $pageUrl->url]))
        ->assertOk();

    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBeLessThanOrEqual(20);
});

/**
 * @param  array<string, mixed>  $translation
 * @return array{0: PageUrl, 1: Page, 2: Language, 3: Site}
 */
function createIndexTestPage(string $url, array $translation = [], ?Site $site = null, ?Language $language = null): array
{
    $language ??= Language::factory()->english()->create();
    $site ??= Site::factory()->default()->create(['language_id' => $language->id]);

    if (! SiteDomain::query()->where('site_id', $site->id)->where('language_id', $language->id)->exists()) {
        SiteDomain::factory()
            ->site($site)
            ->language($language)
            ->create([
                'domain' => 'example.com',
                'path' => null,
                'scheme' => null,
            ]);
    }

    $page = Page::factory()
        ->site($site)
        ->meta($translation['pageMeta'] ?? [])
        ->published()
        ->withTranslations($language, [
            'title' => $translation['title'] ?? 'Published Page',
            'content' => $translation['content'] ?? '<p>Published content</p>',
            'meta' => $translation['meta'] ?? ['description' => 'Published summary'],
        ])
        ->create();

    $pageUrl = PageUrl::factory()
        ->site($site)
        ->language($language)
        ->page($page)
        ->create(['url' => $url]);

    return [$pageUrl, $page, $language, $site];
}

/**
 * @param  array<string, mixed>  $parameters
 */
function indexTestUrl(string $routeName, array $parameters = [], string $host = 'example.com'): string
{
    URL::useOrigin('http://' . $host);

    return route($routeName, $parameters);
}

function indexResponseHeader(TestResponse $response, string $header): string
{
    $value = $response->baseResponse->headers->get($header);

    return is_string($value) ? $value : '';
}
