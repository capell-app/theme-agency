<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\PublishingStudio\Models\Workspace;
use Capell\SeoSuite\Actions\BuildPageSeoReportAction;
use Capell\SeoSuite\Data\PageSeoReportData;
use Capell\SeoSuite\Data\SeoIssueData;
use Capell\SeoSuite\Data\SeoPreviewData;
use Capell\SeoSuite\Enums\SeoCheckKeyEnum;
use Capell\SeoSuite\Enums\SeoIssueSeverityEnum;
use Capell\SeoSuite\Support\Publishing\SeoPublishReportProviderAdapter;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

it('resolves publish report languages from page translations before falling back to the site language', function (): void {
    $english = new Language;
    $english->forceFill(['id' => 10, 'name' => 'English']);

    $french = new Language;
    $french->forceFill(['id' => 20, 'name' => 'French']);

    $site = new Site;
    $site->setRelation('language', $english);

    $englishTranslation = new Translation;
    $englishTranslation->setRelation('language', $english);

    $duplicateEnglishTranslation = new Translation;
    $duplicateEnglishTranslation->setRelation('language', $english);

    $frenchTranslation = new Translation;
    $frenchTranslation->setRelation('language', $french);

    $page = new Page;
    $page->setRelation('translations', new EloquentCollection([
        $englishTranslation,
        $duplicateEnglishTranslation,
        $frenchTranslation,
    ]));

    $method = new ReflectionMethod(SeoPublishReportProviderAdapter::class, 'languagesForPage');
    $languages = $method->invoke(new SeoPublishReportProviderAdapter, $page, $site);

    expect($languages)->toHaveCount(2)
        ->and(array_map(
            fn (Language $language): int => (int) $language->getKey(),
            $languages,
        ))->toBe([10, 20]);
});

it('falls back to site language when a publish report page has no translation languages', function (): void {
    $english = new Language;
    $english->forceFill(['id' => 10, 'name' => 'English']);

    $site = new Site;
    $site->setRelation('language', $english);

    $page = new Page;
    $page->setRelation('translations', new EloquentCollection);

    $method = new ReflectionMethod(SeoPublishReportProviderAdapter::class, 'languagesForPage');
    $languages = $method->invoke(new SeoPublishReportProviderAdapter, $page, $site);

    expect($languages)->toHaveCount(1)
        ->and($languages[0])->toBe($english);
});

it('uses public urls before page names and uuids for publish report labels', function (): void {
    $pageUrl = new PageUrl;
    $pageUrl->forceFill(['url' => '/about']);

    $page = new Page;
    $page->forceFill([
        'id' => 123,
        'name' => 'About page',
        'uuid' => 'page-uuid',
    ]);
    $page->setRelation('pageUrls', new EloquentCollection([$pageUrl]));

    $method = new ReflectionMethod(SeoPublishReportProviderAdapter::class, 'pageLabel');

    expect($method->invoke(new SeoPublishReportProviderAdapter, $page))->toBe('/about');

    $page->setRelation('pageUrls', new EloquentCollection);

    expect($method->invoke(new SeoPublishReportProviderAdapter, $page))->toBe('About page');

    $page->forceFill(['name' => '   ']);

    expect($method->invoke(new SeoPublishReportProviderAdapter, $page))->toBe('page-uuid');
});

it('normalizes scalar publish report values and rejects empty or structured values', function (): void {
    $method = new ReflectionMethod(SeoPublishReportProviderAdapter::class, 'stringValue');
    $adapter = new SeoPublishReportProviderAdapter;

    expect($method->invoke($adapter, '  Search page  '))->toBe('Search page')
        ->and($method->invoke($adapter, 42))->toBe('42')
        ->and($method->invoke($adapter, '   '))->toBeNull()
        ->and($method->invoke($adapter, ['not' => 'scalar']))->toBeNull();
});

it('builds workspace seo publish reports from draft pages and skips failed page audits', function (): void {
    if (! Schema::hasColumn('pages', 'workspace_id')) {
        Schema::table('pages', function (Blueprint $table): void {
            $table->unsignedBigInteger('workspace_id')->default(0)->index();
        });
    }

    $language = Language::factory()->english()->create();
    $site = Site::factory()->create(['language_id' => $language->getKey()]);
    $workspace = new Workspace;
    $workspace->forceFill([
        'id' => 123,
        'uuid' => 'workspace-uuid',
        'name' => 'Draft workspace',
    ]);
    $workspace->exists = true;

    $draftPage = Page::factory()
        ->site($site)
        ->withTranslations($language)
        ->create([
            'name' => 'Draft SEO page',
            'workspace_id' => 123,
        ]);
    $draftPage->pageUrls()->create([
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'url' => '/draft-seo-page',
        'status' => true,
    ]);
    Page::factory()
        ->site($site)
        ->withTranslations($language)
        ->create([
            'name' => 'Other workspace page',
            'workspace_id' => 456,
        ]);
    Page::factory()
        ->site($site)
        ->withTranslations($language)
        ->create([
            'name' => 'Broken SEO page',
            'workspace_id' => 123,
        ]);

    app()->instance(BuildPageSeoReportAction::class, new class
    {
        public function handle(Page $page, Site $site, Language $language): PageSeoReportData
        {
            throw_if($page->name === 'Broken SEO page', RuntimeException::class, 'SEO audit failed');

            return new PageSeoReportData(
                score: 72,
                searchPreview: new SeoPreviewData('Title', 'Description', '/draft-seo-page'),
                socialPreview: new SeoPreviewData('Title', 'Description', '/draft-seo-page'),
                issues: [
                    new SeoIssueData(
                        key: SeoCheckKeyEnum::MetaTitle,
                        severity: SeoIssueSeverityEnum::Critical,
                        message: 'Missing meta title',
                    ),
                    'ignored issue payload',
                ],
            );
        }
    });

    $reports = (new SeoPublishReportProviderAdapter)->forWorkspace($workspace);

    expect($reports)->toHaveCount(1)
        ->and($reports[0]['page'])->toMatchArray([
            'id' => $draftPage->getKey(),
            'label' => '/draft-seo-page-en',
        ])
        ->and($reports[0]['issues'])->toBe([
            [
                'key' => SeoCheckKeyEnum::MetaTitle->value,
                'severity' => SeoIssueSeverityEnum::Critical->value,
                'message' => 'Missing meta title',
            ],
        ]);
});
