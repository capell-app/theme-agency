<?php

declare(strict_types=1);

use Capell\Core\Models\Media;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Enums\MetaSchemaEnum;
use Capell\Tests\Support\Concerns\TestingFrontend;

use function Pest\Laravel\get;

use Sinnbeck\DomAssertions\Asserts\AssertElement;
use Sinnbeck\DomAssertions\Asserts\BaseAssert;

uses(TestingFrontend::class);

test('can see all meta schema', function (): void {
    $site = Site::factory()
        ->withTranslations()
        ->meta([
            'meta_schema' => array_values(MetaSchemaEnum::getComponents()),
            'business_name' => 'Test Business',
        ])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations()
        ->has(Media::factory()->count(2)->video())
        ->has(Media::factory()->image())
        ->create();

    get($page->pageUrl->full_url)
        ->assertOk()
        ->assertElementExists(
            'script[type="application/ld+json"]',
            fn (AssertElement $elm): BaseAssert => $elm->containsText('Test Business'),
        );
});

test('can see website meta schema', function (): void {
    $site = Site::factory()
        ->withTranslations()
        ->meta([
            'meta_schema' => [
                MetaSchemaEnum::Website->getComponent(),
            ],
            'business_name' => 'Test Business',
        ])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations()
        ->create();

    get($page->pageUrl->full_url)
        ->assertOk()
        ->assertElementExists(
            'script[type="application/ld+json"]',
            fn (AssertElement $elm): BaseAssert => $elm->containsText('Test Business'),
        );
});

test('keeps website schema data loading out of public blade', function (): void {
    $view = file_get_contents(dirname(__DIR__, 3) . '/resources/views/components/schema/website.blade.php');

    expect($view)
        ->not->toContain('Page::')
        ->not->toContain('::query(')
        ->not->toContain('DB::')
        ->not->toContain('Frontend::')
        ->not->toContain('->pageUrl');
});

test('can see webpage meta schema', function (): void {
    $site = Site::factory()
        ->withTranslations()
        ->meta([
            'meta_schema' => [
                MetaSchemaEnum::Webpage->getComponent(),
            ],
            'business_name' => 'Test Business',
        ])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations(data: [
            'meta' => [
                'keywords' => 'test, keywords',
                'description' => 'test description',
            ],
        ])
        ->create();

    get($page->pageUrl->full_url)
        ->assertOk()
        ->assertElementExists(
            'script[type="application/ld+json"]',
            function (AssertElement $elm) use ($page): BaseAssert {
                $json = (string) $elm->getParser()->getText();
                $data = json_decode($json, true);

                expect($data)->toBeArray()
                    ->and($data['@context'] ?? null)->toBe('https://schema.org')
                    ->and($data['@type'] ?? null)->toBe('WebPage')
                    ->and($data['dateCreated'] ?? null)->toBe($page->created_at?->toDateString())
                    ->and($data['dateModified'] ?? null)->toBe($page->updated_at?->toDateString())
                    ->and($data['url'] ?? null)->toBe($page->pageUrl->full_url)
                    ->and($data['name'] ?? null)->toBe($page->translation->label)
                    ->and($data['headline'] ?? null)->toBe($page->translation->title)
                    ->and($data['availableLanguage'] ?? null)->not()->toBeNull()
                    ->and($data['keywords'] ?? null)->toBe($page->translation->meta_keywords)
                    ->and($data['description'] ?? null)->toBe($page->translation->meta_description);

                return $elm;
            },
        );
});

test('renders stored graph schema settings through the seo head hook', function (): void {
    $site = Site::factory()
        ->withTranslations(siteDomainData: [
            'scheme' => 'https',
            'domain' => 'graph.test',
            'path' => null,
        ])
        ->meta([
            'meta_schema' => [
                MetaSchemaEnum::Graph->getComponent(),
            ],
            'business_name' => 'Graph Business',
        ])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations()
        ->create();

    get($page->pageUrl->full_url)
        ->assertOk()
        ->assertSee('"@graph"', false)
        ->assertSee('Graph Business');
});

test('renders complete seo head output from shared public resolvers', function (): void {
    $site = Site::factory()
        ->withTranslations(siteDomainData: [
            'scheme' => 'https',
            'domain' => 'example.test',
            'path' => null,
        ])
        ->meta([
            'meta_schema' => [
                MetaSchemaEnum::Webpage->getComponent(),
            ],
            'business_name' => 'Test Business',
        ])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->meta([
            'canonical_url' => 'https://example.test/canonical',
            'robots' => ['noindex', 'nofollow'],
        ])
        ->withTranslations(data: [
            'title' => 'Public SEO Page',
            'meta' => [
                'title' => 'Public SEO Search Title',
                'description' => 'Public SEO description for rendered head output.',
                'social_title' => 'Public SEO Social Title',
                'social_description' => 'Public SEO social description.',
            ],
        ])
        ->create();
    $page->translations()->update([
        'meta' => [
            'title' => 'Public SEO Search Title',
            'description' => 'Public SEO description for rendered head output.',
            'social_title' => 'Public SEO Social Title',
            'social_description' => 'Public SEO social description.',
        ],
    ]);
    $page->refresh();

    $html = get($page->pageUrl->full_url)
        ->assertOk()
        ->getContent();

    $defaultAlternateUrl = $page->pageUrl->full_url;

    expect($html)->toContain('<title>')
        ->and($html)->toContain('Public SEO Search Title')
        ->and($html)->toContain('name="description"')
        ->and($html)->toContain('Public SEO description for rendered head output.')
        ->and($html)->toContain('property="og:title"')
        ->and($html)->toContain('Public SEO Social Title')
        ->and($html)->toContain('name="twitter:title"')
        ->and($html)->toContain('script type="application/ld+json"');

    expect(preg_match_all('/<link\s+href="https:\/\/example\.test\/canonical"\s+rel="canonical"\s*\/>/m', $html))->toBe(1)
        ->and(preg_match_all('/<meta\s+name="robots"\s+content="noindex, nofollow"\s*\/>/m', $html))->toBe(1)
        ->and(preg_match_all('/<link\s+href="' . preg_quote($defaultAlternateUrl, '/') . '"\s+hreflang="x-default"\s+rel="alternate"\s*\/>/m', $html))->toBe(1);
});
