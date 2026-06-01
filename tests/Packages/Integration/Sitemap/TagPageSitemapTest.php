<?php

declare(strict_types=1);

use Capell\Blog\Support\Creator\BlogCreator;
use Capell\Blog\Support\Sitemap\TagsSitemap;
use Capell\Core\Models\Language;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\SiteDiscovery\Data\SitemapPageData;
use Capell\Tags\Enums\TagTypeEnum;
use Capell\Tags\Models\Tag;
use Capell\Tests\Support\Concerns\TestingFrontend;
use Illuminate\Support\Collection;

uses(TestingFrontend::class);

it('builds recursive sitemap for tag results page with parent chain and tag children', function (): void {
    $blogCreator = resolve(BlogCreator::class);

    $language = Language::factory()->create();
    $site = Site::factory()->recycle($language)->withTranslations()->create();
    $domain = SiteDomain::factory()->for($site)->create(['language_id' => $language->id]);
    $blogPage = $blogCreator->createBlogPage($site);
    $tagsPage = $blogCreator->createTagsPage($site, $blogPage);
    $tagPage = $blogCreator->createTagPage($site, $tagsPage);
    $tagPageUrl = capell_test_instance($tagPage->pageUrl, PageUrl::class);
    $tagPageUrl->setRelation('siteDomain', $domain);

    // Create some tags
    /** @var Collection<int, Tag> $tags */
    $tags = Tag::factory()->count(3)->type(TagTypeEnum::Page)->translate($language)->create();

    $tagUrl = rtrim($tagPageUrl->full_url, '/*');
    $tagUrls = $tags->map(fn (Tag $tag): string => $tagUrl . '/' . $tag->getTranslation('slug', $language->code));

    // Under test
    $sitemap = new TagsSitemap(site: $site, domain: $domain, language: $language);
    $result = $sitemap->fetch();

    expect($result)
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1);

    $root = capell_test_instance($result->first(), SitemapPageData::class);
    $rootChildren = capell_test_instance($root->children, Collection::class);
    $tagsNode = capell_test_instance($rootChildren->first(), SitemapPageData::class);
    $tagChildren = capell_test_instance($tagsNode->children, Collection::class);

    expect($root)
        ->toBeInstanceOf(SitemapPageData::class)
        ->pageId->toBe($blogPage->id)
        ->children
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(1)
        ->and($tagsNode)
        ->pageId->toBe($tagsPage->id)
        ->children
        ->toBeInstanceOf(Collection::class)
        ->toHaveCount(3)
        ->and($tagChildren->pluck('url'))
        ->toContain($tagUrls->first())
        ->toContain($tagUrls->get(1))
        ->toContain($tagUrls->last());
});
