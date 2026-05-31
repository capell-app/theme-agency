<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Enums\AiDiscoveryStatusEnum;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Capell\SeoSuite\Models\AiDiscoverySiteProfile;
use Capell\SeoSuite\Support\SiteDiscovery\AiDiscoveryGeneratedOutputCoverageSource;

it('reports ai discovery coverage from any enabled url-bearing ai discovery output', function (): void {
    $language = Language::query()->create([
        'name' => 'English',
        'locale' => 'en',
        'code' => 'en',
        'flag' => 'gb-eng',
        'status' => true,
        'default' => true,
        'order' => 1,
    ]);

    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, siteDomainData: [
            'scheme' => 'https',
            'domain' => 'example.test',
            'path' => null,
        ])
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, ['title' => 'AI Discovery Covered Page'], slug: 'covered')
        ->create();

    AiDiscoverySiteProfile::query()->create([
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'llms_txt_enabled' => false,
        'llms_full_txt_enabled' => true,
        'markdown_pages_enabled' => false,
        'status' => AiDiscoveryStatusEnum::Enabled,
    ]);

    AiDiscoveryPageProfile::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'include_in_ai_index' => true,
        'priority' => 500,
    ]);

    $urls = (new AiDiscoveryGeneratedOutputCoverageSource)->coveredUrls(collect());

    expect($urls->all())->toBe(['https://example.test/covered']);
});

it('does not report ai discovery coverage for disabled output profiles or excluded pages', function (): void {
    $language = Language::query()->create([
        'name' => 'English',
        'locale' => 'en',
        'code' => 'en',
        'flag' => 'gb-eng',
        'status' => true,
        'default' => true,
        'order' => 1,
    ]);

    $site = Site::factory()
        ->language($language)
        ->withTranslations($language)
        ->create();

    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, ['title' => 'Excluded Page'], slug: 'excluded')
        ->create();

    AiDiscoverySiteProfile::query()->create([
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'llms_txt_enabled' => true,
        'llms_full_txt_enabled' => true,
        'markdown_pages_enabled' => true,
        'status' => AiDiscoveryStatusEnum::Disabled,
    ]);

    AiDiscoveryPageProfile::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'include_in_ai_index' => false,
        'priority' => 500,
    ]);

    expect((new AiDiscoveryGeneratedOutputCoverageSource)->coveredUrls(collect())->all())->toBe([]);
});
