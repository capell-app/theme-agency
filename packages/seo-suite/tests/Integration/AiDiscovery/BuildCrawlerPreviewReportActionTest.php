<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\LanguageFactory;
use Capell\Core\Database\Factories\SiteFactory;
use Capell\Core\Models\Page;
use Capell\SeoSuite\Actions\BuildCrawlerPreviewReportAction;
use Capell\SeoSuite\Actions\ResolveAiDiscoveryProfileAction;
use Capell\SeoSuite\Enums\MetaSchemaEnum;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Cache::flush();
    Date::setTestNow();
    Storage::fake((string) config('capell.sitemap.disk', 'local'));
});

afterEach(function (): void {
    Date::setTestNow();
});

it('builds crawler previews for robots ai discovery markdown and schema outputs', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()
        ->recycle($language)
        ->language($language)
        ->withTranslations(
            $language,
            [],
            siteDomainData: ['scheme' => 'https', 'domain' => 'example.test', 'path' => null],
        )
        ->create([
            'meta' => [
                'meta_schema' => [
                    MetaSchemaEnum::Website->getComponent(),
                ],
                'business_name' => 'Crawler Preview Co',
            ],
        ]);
    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Crawler Preview Page',
            'content' => '<p>Public crawler body.</p>',
            'meta' => [
                'description' => 'Public crawler summary.',
            ],
        ])
        ->create();

    ResolveAiDiscoveryProfileAction::run($site, $language)->update([
        'llms_full_txt_enabled' => true,
    ]);
    ResolveAiDiscoveryProfileAction::run($site, $language, $page);
    AiDiscoveryPageProfile::query()
        ->where('page_id', $page->getKey())
        ->update([
            'summary' => 'Public AI summary.',
        ]);

    $previews = collect(BuildCrawlerPreviewReportAction::run($site, $language, $page, 'GPTBot'));

    expect($previews->pluck('key')->all())->toBe([
        'robots_txt',
        'sitemap_xml',
        'llms_txt',
        'llms_full_txt',
        'page_markdown',
        'schema_json',
    ])
        ->and($previews->firstWhere('key', 'robots_txt')?->contentType)->toBe('text/plain')
        ->and($previews->firstWhere('key', 'sitemap_xml')?->contentType)->toBe('application/xml')
        ->and($previews->firstWhere('key', 'llms_txt')?->preview)->toContain('Crawler Preview Page')
        ->and($previews->firstWhere('key', 'page_markdown')?->path)->toEndWith('.md')
        ->and($previews->firstWhere('key', 'page_markdown')?->preview)->toContain('Public AI summary.')
        ->and($previews->firstWhere('key', 'schema_json')?->contentType)->toBe('application/ld+json')
        ->and($previews->firstWhere('key', 'schema_json')?->preview)->toContain('Crawler Preview Co')
        ->and($previews->pluck('crawlerUserAgent')->unique()->values()->all())->toBe(['GPTBot'])
        ->and($previews->pluck('status')->unique()->values()->all())->toBe(['ok']);
});

it('flags empty crawler outputs and public leak markers', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();
    $page = Page::factory()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Leaky Markdown',
        ])
        ->create();

    ResolveAiDiscoveryProfileAction::run($site, $language)->update([
        'llms_txt_enabled' => false,
        'markdown_pages_enabled' => true,
    ]);
    ResolveAiDiscoveryProfileAction::run($site, $language, $page);
    AiDiscoveryPageProfile::query()
        ->where('page_id', $page->getKey())
        ->update([
            'markdown_override' => "[Admin](/admin)\n",
        ]);

    $previews = collect(BuildCrawlerPreviewReportAction::run($site, $language, $page));

    expect($previews->firstWhere('key', 'llms_txt')?->status)->toBe('warn')
        ->and($previews->firstWhere('key', 'llms_txt')?->warnings)->toContain(__('capell-seo-suite::generic.crawler_preview_empty_output'))
        ->and($previews->firstWhere('key', 'page_markdown')?->status)->toBe('warn')
        ->and($previews->firstWhere('key', 'page_markdown')?->warnings[0])->toContain('/admin');
});
