<?php

declare(strict_types=1);

use Capell\AgentDelivery\Providers\AgentDeliveryServiceProvider;
use Capell\AgentDelivery\Tests\AgentDeliveryTestCase;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Illuminate\Support\Facades\URL;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\getJson;

uses(AgentDeliveryTestCase::class);

it('returns a public-safe page manifest for an already public page', function (): void {
    [$pageUrl, $page, $language, $site] = createAgentDeliveryPage('/guides/ai-ready', [
        'title' => 'AI Ready Websites',
        'content' => '<h2>Discovery</h2><p data-editor-id="secret-page-id">Readable public copy</p><script>bad()</script>',
        'meta' => [
            'description' => 'A concise discovery summary.',
            'admin_prompt' => 'Rewrite with internal prompt',
            'references' => [
                ['title' => 'External source', 'url' => 'https://source.example/reference'],
                ['title' => 'Invalid source', 'url' => 'http-not-a-url'],
                ['title' => 'Unsafe source', 'url' => 'javascript:alert(1)'],
            ],
        ],
    ]);

    URL::useOrigin('http://example.com');

    getJson(route('capell-agent-delivery.pages.manifest', ['url' => $pageUrl->url]))
        ->assertOk()
        ->assertHeader('X-Capell-Agent-Delivery-Version', 'v1')
        ->assertHeader('X-Capell-Cache-Tags', sprintf('agent-delivery,site:%s,language:%s,page:%s', $site->getKey(), $language->getKey(), $page->getKey()))
        ->assertHeader('Cache-Control', 'max-age=300, public')
        ->assertHeader('ETag')
        ->assertJsonPath('data.canonicalUrl', 'http://example.com/guides/ai-ready')
        ->assertJsonPath('data.url', '/guides/ai-ready')
        ->assertJsonPath('data.language', 'en')
        ->assertJsonPath('data.title', 'AI Ready Websites')
        ->assertJsonPath('data.headings.0', 'AI Ready Websites')
        ->assertJsonPath('data.headings.1', 'Discovery')
        ->assertJsonPath('data.summary', 'A concise discovery summary.')
        ->assertJsonPath('data.body', 'Discovery Readable public copy')
        ->assertJsonPath('data.metadata.description', 'A concise discovery summary.')
        ->assertJsonPath('data.schema.@context', 'https://schema.org')
        ->assertJsonPath('data.schema.@type', 'WebPage')
        ->assertJsonPath('data.schema.name', 'AI Ready Websites')
        ->assertJsonPath('data.references.0.url', 'https://source.example/reference')
        ->assertJsonMissingPath('data.metadata.admin_prompt')
        ->assertJsonMissingPath('data.references.1');
});

it('returns stable semantic chunks for a public page', function (): void {
    [$pageUrl] = createAgentDeliveryPage('/guides/ai-ready', [
        'title' => 'AI Ready Websites',
        'content' => '<h2>Discovery</h2><p>Readable public copy</p>',
    ], siteDomainPath: '/chunks');

    getJson(agentDeliveryUrl('capell-agent-delivery.pages.chunks', ['url' => '/chunks/guides/ai-ready']))
        ->assertOk()
        ->assertJsonPath('data.0.id', 'discovery')
        ->assertJsonPath('data.0.heading', 'Discovery')
        ->assertJsonPath('data.0.sourceUrl', 'http://example.com/chunks/guides/ai-ready#discovery')
        ->assertJsonPath('data.0.body', 'Discovery Readable public copy')
        ->assertJsonPath('data.0.dependsOn.0', 'url:http://example.com/chunks/guides/ai-ready');
});

it('returns a 304 response when manifest etag matches', function (): void {
    [$pageUrl] = createAgentDeliveryPage('/etag-page');

    $response = getJson(agentDeliveryUrl('capell-agent-delivery.pages.manifest', ['url' => $pageUrl->url]))
        ->assertOk()
        ->assertHeader('ETag');

    getJson(agentDeliveryUrl('capell-agent-delivery.pages.manifest', ['url' => $pageUrl->url]), [
        'If-None-Match' => manifestResponseHeader($response, 'ETag'),
    ])
        ->assertStatus(304)
        ->assertHeader('ETag', manifestResponseHeader($response, 'ETag'));
});

it('does not serve pages opted out of agent delivery', function (): void {
    [$pageUrl] = createAgentDeliveryPage('/private-agent-page', [
        'meta' => [
            'description' => 'Private to agents.',
            'agent_delivery' => ['enabled' => false],
        ],
    ]);

    getJson(agentDeliveryUrl('capell-agent-delivery.pages.manifest', ['url' => $pageUrl->url]))
        ->assertNotFound()
        ->assertExactJson(['message' => 'Page not found']);
});

it('resolves path-only site domains when an exact host site also exists', function (): void {
    createAgentDeliveryPage('/host-root', domain: 'example.com');

    [$pageUrl, $page, $language, $site] = createAgentDeliveryPage('/article', [
        'title' => 'Path Only Article',
        'content' => '<p>Scoped copy</p>',
    ], domain: null, siteDomainPath: '/docs');

    getJson(agentDeliveryUrl('capell-agent-delivery.pages.manifest', ['url' => '/docs/article']))
        ->assertOk()
        ->assertHeader('X-Capell-Cache-Tags', sprintf('agent-delivery,site:%s,language:%s,page:%s', $site->getKey(), $language->getKey(), $page->getKey()))
        ->assertJsonPath('data.canonicalUrl', 'http://example.com/docs/article')
        ->assertJsonPath('data.url', $pageUrl->url)
        ->assertJsonPath('data.alternates.en', 'http://example.com/docs/article')
        ->assertJsonPath('data.title', 'Path Only Article');
});

it('does not serve unpublished or missing pages', function (): void {
    createAgentDeliveryPage('/published');

    getJson(agentDeliveryUrl('capell-agent-delivery.pages.manifest', ['url' => '/missing']))
        ->assertNotFound()
        ->assertExactJson(['message' => 'Page not found'])
        ->assertHeader('X-Capell-Agent-Delivery-Version', 'v1')
        ->assertHeaderMissing('X-Capell-Cache-Tags');
});

it('does not serve agent delivery when the package is not installed', function (): void {
    createAgentDeliveryPage('/published');
    CapellCore::forcePackageInstalled(AgentDeliveryServiceProvider::$packageName, false);

    getJson(agentDeliveryUrl('capell-agent-delivery.pages.manifest', ['url' => '/published']))
        ->assertNotFound()
        ->assertExactJson(['message' => 'Page not found'])
        ->assertHeader('X-Capell-Agent-Delivery-Version', 'v1')
        ->assertHeaderMissing('X-Capell-Cache-Tags');
});

/**
 * @param  array<string, mixed>  $translation
 * @return array{0: PageUrl, 1: Page, 2: Language, 3: Site}
 */
function createAgentDeliveryPage(string $url, array $translation = [], ?string $domain = 'example.com', ?string $siteDomainPath = null): array
{
    $language = Language::factory()->english()->create();
    $site = Site::factory()->default()->create(['language_id' => $language->id]);
    SiteDomain::factory()
        ->site($site)
        ->language($language)
        ->create([
            'domain' => $domain,
            'path' => $siteDomainPath,
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
function agentDeliveryUrl(string $routeName, array $parameters = [], string $host = 'example.com'): string
{
    URL::useOrigin('http://' . $host);

    return route($routeName, $parameters);
}

function manifestResponseHeader(TestResponse $response, string $header): string
{
    $value = $response->baseResponse->headers->get($header);

    return is_string($value) ? $value : '';
}
