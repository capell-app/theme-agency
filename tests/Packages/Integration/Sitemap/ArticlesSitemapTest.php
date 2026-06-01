<?php

declare(strict_types=1);
use Capell\Blog\Models\Article;
use Capell\Blog\Support\Creator\BlogCreator;
use Capell\Blog\Support\Sitemap\ArticlesSitemap;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\SiteDiscovery\Data\SitemapPageData;
use Capell\Tests\Support\Concerns\TestingFrontend;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

uses(TestingFrontend::class);

it('returns blog page with all Article children recursively', function (): void {
    $blogCreator = resolve(BlogCreator::class);

    $language = Language::factory()->create();
    $site = Site::factory()->recycle($language)->withTranslations()->create();
    $domain = SiteDomain::factory()->for($site)->create(['language_id' => $language->id]);

    // Create a blog page and a tree of children
    $blogPage = $blogCreator->createBlogPage($site);

    // Children: Article group
    $articleA = Article::factory()
        ->for($site)
        ->withTranslations()
        ->published(CarbonImmutable::now()->subDay())
        ->create();
    $articleB = Article::factory()
        ->for($site)
        ->withTranslations()
        ->published(CarbonImmutable::now()->subDays(2))
        ->create();
    Page::factory()->for($site)->withTranslations()->create();

    $sitemap = new ArticlesSitemap(site: $site, domain: $domain, language: $language);
    $result = $sitemap->fetch();

    expect($result)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1);

    $root = capell_test_instance($result->first(), SitemapPageData::class);

    $rootChildren = capell_test_instance($root->children, Collection::class);
    $firstChild = capell_test_instance($rootChildren->first(), SitemapPageData::class);

    expect($root)
        ->toBeInstanceOf(SitemapPageData::class)
        ->pageId->toBe($blogPage->id)
        ->lastModified->toBeInstanceOf(CarbonImmutable::class)
        ->and($rootChildren)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(2)
        ->pluck('pageId')
        ->toMatchArray([$articleA->id, $articleB->id])
        ->and($firstChild)
        ->toBeInstanceOf(SitemapPageData::class)
        ->lastModified->toBeInstanceOf(CarbonImmutable::class)
        ->and($root->toArray()['lastModified'])
        ->toBe($root->lastModified?->toAtomString())
        ->and($firstChild->toArray()['lastModified'])
        ->toBe($firstChild->lastModified?->toAtomString());
});
