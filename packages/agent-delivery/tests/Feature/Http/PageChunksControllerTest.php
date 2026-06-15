<?php

declare(strict_types=1);

use Capell\AgentDelivery\Contracts\AgentDeliveryChunkContributor;
use Capell\AgentDelivery\Data\AgentDeliveryChunkData;
use Capell\AgentDelivery\Providers\AgentDeliveryServiceProvider;
use Capell\AgentDelivery\Support\AgentDeliveryRegistry;
use Capell\AgentDelivery\Tests\AgentDeliveryTestCase;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\getJson;

uses(AgentDeliveryTestCase::class);

it('returns chunks with a meta envelope for a published page', function (): void {
    [$pageUrl] = createChunksTestPage('/guides/chunked', [
        'title' => 'Chunked Page',
        'content' => '<h2>Section</h2><p>Body text for chunking.</p>',
    ]);

    getJson(chunksTestUrl('capell-agent-delivery.pages.chunks', ['url' => $pageUrl->url]))
        ->assertOk()
        ->assertHeader('X-Capell-Agent-Delivery-Version', 'v1')
        ->assertHeader('X-Capell-Cache-Tags')
        ->assertHeader('X-Capell-Agent-Delivery-Variation', 'site,language,url,page')
        ->assertHeader('Cache-Control', 'max-age=300, public')
        ->assertHeader('Vary', 'Host')
        ->assertHeader('ETag')
        ->assertJsonPath('data.0.id', 'section')
        ->assertJsonPath('data.0.heading', 'Section')
        ->assertJsonPath('data.0.sourceUrl', 'http://example.com/guides/chunked#section')
        ->assertJsonPath('data.0.body', 'Section Body text for chunking.')
        ->assertJsonPath('meta.count', 1)
        ->assertJsonPath('meta.budget.chunkCount', 1)
        ->assertJsonPath('meta.budget.targetWords', 160)
        ->assertJsonPath('meta.budget.maxRecommendedChunks', 40)
        ->assertJsonPath('meta.budget.overTargetChunks', 0)
        ->assertJsonPath('meta.budget.isWithinBudget', true)
        ->assertJsonPath('meta.budget.warnings', [])
        ->assertJsonPath('meta.canonicalUrl', 'http://example.com/guides/chunked')
        ->assertJsonStructure(['meta' => ['count', 'budget', 'canonicalUrl', 'generatedAt']]);
});

it('returns 404 without cache-tag headers when page is missing', function (): void {
    createChunksTestPage('/published');

    getJson(chunksTestUrl('capell-agent-delivery.pages.chunks', ['url' => '/nonexistent']))
        ->assertNotFound()
        ->assertExactJson(['message' => 'Page not found'])
        ->assertHeader('X-Capell-Agent-Delivery-Version', 'v1')
        ->assertHeaderMissing('X-Capell-Cache-Tags');
});

it('returns 404 without cache-tag headers when package is not installed', function (): void {
    createChunksTestPage('/published');
    CapellCore::forcePackageInstalled(AgentDeliveryServiceProvider::$packageName, false);

    getJson(chunksTestUrl('capell-agent-delivery.pages.chunks', ['url' => '/published']))
        ->assertNotFound()
        ->assertExactJson(['message' => 'Page not found'])
        ->assertHeader('X-Capell-Agent-Delivery-Version', 'v1')
        ->assertHeaderMissing('X-Capell-Cache-Tags');
});

it('returns empty chunks array for a page with no body content', function (): void {
    [$pageUrl] = createChunksTestPage('/empty-body', [
        'title' => 'Empty Body Page',
        'content' => '',
    ]);

    getJson(chunksTestUrl('capell-agent-delivery.pages.chunks', ['url' => $pageUrl->url]))
        ->assertOk()
        ->assertJsonPath('data', [])
        ->assertJsonPath('meta.count', 0);
});

it('reports chunk budget warnings when generated chunks exceed configured limits', function (): void {
    config()->set('capell-agent-delivery.public_pages.chunk_target_words', 3);
    config()->set('capell-agent-delivery.public_pages.chunk_overlap_words', 0);
    config()->set('capell-agent-delivery.public_pages.chunk_max_recommended_chunks', 1);

    [$pageUrl] = createChunksTestPage('/budget-warning', [
        'title' => 'Budget Warning Page',
        'content' => '<p>One two three four five six seven eight nine ten eleven twelve.</p>',
    ]);

    getJson(chunksTestUrl('capell-agent-delivery.pages.chunks', ['url' => $pageUrl->url]))
        ->assertOk()
        ->assertJsonPath('meta.budget.targetWords', 3)
        ->assertJsonPath('meta.budget.maxRecommendedChunks', 1)
        ->assertJsonPath('meta.budget.maxChunkWords', 3)
        ->assertJsonPath('meta.budget.overTargetChunks', 0)
        ->assertJsonPath('meta.budget.isWithinBudget', false)
        ->assertJsonPath('meta.budget.warnings', ['chunk_count_exceeds_recommended_max']);
});

it('reports over-target contributor chunks in the budget diagnostics', function (): void {
    config()->set('capell-agent-delivery.public_pages.chunk_target_words', 3);

    [$pageUrl] = createChunksTestPage('/contributor-budget-warning', [
        'title' => 'Contributor Budget Warning Page',
        'content' => '<p>Some content</p>',
    ]);

    /** @var AgentDeliveryRegistry $registry */
    $registry = resolve(AgentDeliveryRegistry::class);
    $registry->registerChunkContributor(new class implements AgentDeliveryChunkContributor
    {
        /**
         * @param  Pageable<Model>  $page
         * @return list<AgentDeliveryChunkData>
         */
        public function chunks(Pageable $page, Site $site, Language $language): array
        {
            return [
                new AgentDeliveryChunkData('oversized', 'Oversized', 'https://example.com#oversized', null, 'one two three four', 1),
            ];
        }
    });

    getJson(chunksTestUrl('capell-agent-delivery.pages.chunks', ['url' => $pageUrl->url]))
        ->assertOk()
        ->assertJsonPath('meta.budget.maxChunkWords', 4)
        ->assertJsonPath('meta.budget.overTargetChunks', 1)
        ->assertJsonPath('meta.budget.isWithinBudget', false)
        ->assertJsonPath('meta.budget.warnings', ['chunk_body_exceeds_target_words']);
});

it('returns contributor chunks in stable sort order', function (): void {
    [$pageUrl, $page, $language, $site] = createChunksTestPage('/ordered', [
        'title' => 'Ordered Page',
        'content' => '<p>Some content</p>',
    ]);

    /** @var AgentDeliveryRegistry $registry */
    $registry = resolve(AgentDeliveryRegistry::class);
    $registry->registerChunkContributor(new class implements AgentDeliveryChunkContributor
    {
        /**
         * @param  Pageable<Model>  $page
         * @return list<AgentDeliveryChunkData>
         */
        public function chunks(Pageable $page, Site $site, Language $language): array
        {
            return [
                new AgentDeliveryChunkData('third', 'Third', 'https://example.com#3', null, 'Third body', 3),
                new AgentDeliveryChunkData('first', 'First', 'https://example.com#1', null, 'First body', 1),
                new AgentDeliveryChunkData('second', 'Second', 'https://example.com#2', null, 'Second body', 2),
            ];
        }
    });
    $registry->registerChunkContributor(new class implements AgentDeliveryChunkContributor
    {
        /**
         * @param  Pageable<Model>  $page
         * @return list<AgentDeliveryChunkData>
         */
        public function chunks(Pageable $page, Site $site, Language $language): array
        {
            return [
                new AgentDeliveryChunkData('fourth', 'Fourth', 'https://example.com#4', null, 'Fourth body', 4),
            ];
        }
    });

    getJson(chunksTestUrl('capell-agent-delivery.pages.chunks', ['url' => $pageUrl->url]))
        ->assertOk()
        ->assertJsonPath('data.0.id', 'first')
        ->assertJsonPath('data.1.id', 'second')
        ->assertJsonPath('data.2.id', 'third')
        ->assertJsonPath('data.3.id', 'fourth')
        ->assertJsonPath('meta.count', 4);
});

it('returns a 304 response when chunk etag matches', function (): void {
    [$pageUrl] = createChunksTestPage('/etag-chunks', [
        'title' => 'ETag Chunk Page',
        'content' => '<h2>Cacheable</h2><p>Cacheable body.</p>',
    ]);

    $response = getJson(chunksTestUrl('capell-agent-delivery.pages.chunks', ['url' => $pageUrl->url]))
        ->assertOk()
        ->assertHeader('ETag');

    getJson(chunksTestUrl('capell-agent-delivery.pages.chunks', ['url' => $pageUrl->url]), [
        'If-None-Match' => chunksResponseHeader($response, 'ETag'),
    ])
        ->assertStatus(304)
        ->assertHeader('ETag', chunksResponseHeader($response, 'ETag'))
        ->assertHeader('X-Capell-Agent-Delivery-Variation', 'site,language,url,page')
        ->assertHeader('Vary', 'Host');
});

/**
 * @param  array<string, mixed>  $translation
 * @return array{0: PageUrl, 1: Page, 2: Language, 3: Site}
 */
function createChunksTestPage(string $url, array $translation = [], ?string $domain = 'example.com'): array
{
    $language = Language::factory()->english()->create();
    $site = Site::factory()->default()->create(['language_id' => $language->id]);
    SiteDomain::factory()
        ->site($site)
        ->language($language)
        ->create([
            'domain' => $domain,
            'path' => null,
            'scheme' => null,
        ]);

    $page = Page::factory()
        ->site($site)
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
function chunksTestUrl(string $routeName, array $parameters = [], string $host = 'example.com'): string
{
    URL::useOrigin('http://' . $host);

    return route($routeName, $parameters);
}

function chunksResponseHeader(TestResponse $response, string $header): string
{
    $value = $response->baseResponse->headers->get($header);

    return is_string($value) ? $value : '';
}
