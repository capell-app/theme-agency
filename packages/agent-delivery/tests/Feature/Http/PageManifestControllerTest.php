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
                ['title' => 'Unsafe source', 'url' => 'javascript:alert(1)'],
            ],
        ],
    ]);

    URL::forceRootUrl('http://example.com');

    getJson(route('capell-agent-delivery.pages.manifest', ['url' => $pageUrl->url]))
        ->assertOk()
        ->assertHeader('X-Capell-Agent-Delivery-Version', 'v1')
        ->assertHeader('X-Capell-Cache-Tags', sprintf('agent-delivery,site:%s,language:%s,page:%s', $site->getKey(), $language->getKey(), $page->getKey()))
        ->assertJsonPath('data.canonicalUrl', 'http://example.com/guides/ai-ready')
        ->assertJsonPath('data.url', '/guides/ai-ready')
        ->assertJsonPath('data.language', 'en')
        ->assertJsonPath('data.title', 'AI Ready Websites')
        ->assertJsonPath('data.headings.0', 'AI Ready Websites')
        ->assertJsonPath('data.headings.1', 'Discovery')
        ->assertJsonPath('data.summary', 'A concise discovery summary.')
        ->assertJsonPath('data.body', 'Discovery Readable public copy')
        ->assertJsonPath('data.metadata.description', 'A concise discovery summary.')
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
        ->assertJsonPath('data.0.id', 'ai-ready-websites')
        ->assertJsonPath('data.0.heading', 'AI Ready Websites')
        ->assertJsonPath('data.0.sourceUrl', 'http://example.com/chunks/guides/ai-ready')
        ->assertJsonPath('data.0.body', 'Discovery Readable public copy')
        ->assertJsonPath('data.0.dependsOn.0', 'url:http://example.com/chunks/guides/ai-ready');
});

it('does not serve unpublished or missing pages', function (): void {
    createAgentDeliveryPage('/published');

    getJson(agentDeliveryUrl('capell-agent-delivery.pages.manifest', ['url' => '/missing']))
        ->assertNotFound()
        ->assertExactJson(['message' => 'Page not found']);
});

it('does not serve agent delivery when the package is not installed', function (): void {
    createAgentDeliveryPage('/published');
    CapellCore::forcePackageInstalled(AgentDeliveryServiceProvider::$packageName, false);

    getJson(agentDeliveryUrl('capell-agent-delivery.pages.manifest', ['url' => '/published']))
        ->assertNotFound()
        ->assertExactJson(['message' => 'Page not found']);
});

/**
 * @param  array<string, mixed>  $translation
 * @return array{0: PageUrl, 1: Page, 2: Language, 3: Site}
 */
function createAgentDeliveryPage(string $url, array $translation = [], string $domain = 'example.com', ?string $siteDomainPath = null): array
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
    URL::forceRootUrl('http://' . $host);

    return route($routeName, $parameters);
}
