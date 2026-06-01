<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Extenders\PageSchemaExtender;
use Capell\Admin\Enums\PageTranslationSchemaHookEnum;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Data\PageSeoReportData;
use Capell\SeoSuite\Data\RedirectOpportunityData;
use Capell\SeoSuite\Data\SeoIssueData;
use Capell\SeoSuite\Data\SeoPreviewData;
use Capell\SeoSuite\Enums\SeoCheckKeyEnum;
use Capell\SeoSuite\Enums\SeoIssueSeverityEnum;
use Capell\SeoSuite\Filament\Components\Forms\Page\PageSeoPanel;
use Capell\SeoSuite\Filament\Extenders\Page\PageSeoPanelSchemaExtender;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;

/**
 * @param  array<string, mixed>  $state
 */
function seoSuitePagePanelFakeGet(array $state): Get
{
    return new class($state) extends Get
    {
        /**
         * @param  array<string, mixed>  $state
         */
        public function __construct(private readonly array $state) {}

        public function __invoke(string|Component $path = '', bool $isAbsolute = false): mixed
        {
            if ($path instanceof Component) {
                return null;
            }

            return $this->state[$path] ?? data_get($this->state, $path);
        }
    };
}

it('registers the page SEO panel schema extender', function (): void {
    $extenders = collect(app()->tagged(PageSchemaExtender::TAG));

    expect($extenders->contains(
        fn (PageSchemaExtender $extender): bool => $extender instanceof PageSeoPanelSchemaExtender,
    ))->toBeTrue();
});

it('adds the page SEO panel after search meta', function (): void {
    $extender = resolve(PageSeoPanelSchemaExtender::class);
    $components = $extender->extendTranslationComponentsForHook(
        Schema::make(),
        PageTranslationSchemaHookEnum::AfterSearchMeta,
    );

    expect($components)->toHaveCount(1)
        ->and($components[0])->toBeInstanceOf(PageSeoPanel::class);
});

it('resolves the panel language id from nested translation state paths', function (): void {
    $panel = PageSeoPanel::make();

    expect($panel->resolveLanguageIdFromState(seoSuitePagePanelFakeGet([
        'language_id' => null,
        '../language_id' => 12,
    ])))->toBe(12)
        ->and($panel->resolveLanguageIdFromState(seoSuitePagePanelFakeGet([
            'language_id' => '',
            '../language_id' => null,
            '../../language_id' => '34',
        ])))->toBe('34');
});

it('groups SEO report issues and passed checks by key and severity', function (): void {
    $report = new PageSeoReportData(
        score: 72,
        searchPreview: new SeoPreviewData(
            title: 'Search title',
            description: 'Search description',
            url: 'https://example.test/search-page',
        ),
        socialPreview: new SeoPreviewData(
            title: 'Social title',
            description: 'Social description',
            url: 'https://example.test/social-page',
            imageUrl: 'https://example.test/social-image.jpg',
        ),
        issues: [
            new SeoIssueData(
                key: SeoCheckKeyEnum::MetaTitle,
                severity: SeoIssueSeverityEnum::Critical,
                message: 'Meta title needs attention.',
            ),
            new SeoIssueData(
                key: SeoCheckKeyEnum::Schema,
                severity: SeoIssueSeverityEnum::Warning,
                message: 'Schema needs attention.',
            ),
            new SeoIssueData(
                key: SeoCheckKeyEnum::InternalLinks,
                severity: SeoIssueSeverityEnum::Notice,
                message: 'Internal links need attention.',
            ),
        ],
        passedChecks: [
            SeoCheckKeyEnum::MetaDescription,
        ],
    );

    expect($report->issuesBySeverity(SeoIssueSeverityEnum::Critical))->toHaveCount(1)
        ->and($report->issuesForKey(SeoCheckKeyEnum::Schema))->toHaveCount(1)
        ->and($report->passedCheckValues())->toBe(['meta_description'])
        ->and($report->hasIssuesForKey(SeoCheckKeyEnum::InternalLinks))->toBeTrue();
});

it('exposes redirect opportunities to the page SEO panel view data', function (): void {
    $report = new PageSeoReportData(
        score: 72,
        searchPreview: new SeoPreviewData(
            title: 'Search title',
            description: 'Search description',
            url: 'https://example.test/search-page',
        ),
        socialPreview: new SeoPreviewData(
            title: 'Social title',
            description: 'Social description',
            url: 'https://example.test/social-page',
        ),
        redirectOpportunities: [
            new RedirectOpportunityData(
                sourceUrl: 'https://example.test/old-page',
                hits: 12,
                siteId: 1,
                languageId: 1,
                suggestedTargetUrl: 'https://example.test/new-page',
                pageName: 'New page',
            ),
        ],
    );

    $reflection = new ReflectionClass(PageSeoPanel::class);
    $method = $reflection->getMethod('viewDataForReport');

    $viewData = $method->invoke(PageSeoPanel::make(), $report);

    expect($viewData['redirectOpportunities'])->toHaveCount(1)
        ->and($viewData['redirectOpportunities'][0])->toBeInstanceOf(RedirectOpportunityData::class)
        ->and($viewData['redirectOpportunities'][0]->sourceUrl)->toBe('https://example.test/old-page');
});

it('renders a compact inline SEO panel without deep diagnostics', function (): void {
    $report = new PageSeoReportData(
        score: 72,
        searchPreview: new SeoPreviewData(
            title: 'Search title',
            description: 'Search description',
            url: 'https://example.test/search-page',
        ),
        socialPreview: new SeoPreviewData(
            title: 'Social title',
            description: 'Social description',
            url: 'https://example.test/social-page',
            imageUrl: 'https://example.test/social-image.jpg',
        ),
        issues: [
            new SeoIssueData(
                key: SeoCheckKeyEnum::MetaTitle,
                severity: SeoIssueSeverityEnum::Critical,
                message: 'Meta title needs attention.',
            ),
            new SeoIssueData(
                key: SeoCheckKeyEnum::Schema,
                severity: SeoIssueSeverityEnum::Warning,
                message: 'Schema needs attention.',
            ),
            new SeoIssueData(
                key: SeoCheckKeyEnum::InternalLinks,
                severity: SeoIssueSeverityEnum::Notice,
                message: 'Internal links need attention.',
            ),
        ],
    );

    $reflection = new ReflectionClass(PageSeoPanel::class);
    $method = $reflection->getMethod('viewDataForReport');
    $viewData = $method->invoke(PageSeoPanel::make(), $report);
    $schemaComponent = new class
    {
        public function getAction(string $name): HtmlString
        {
            return new HtmlString($name);
        }
    };

    $html = View::make('capell-seo-suite::filament.components.page-seo-panel', [
        ...$viewData,
        'schemaComponent' => $schemaComponent,
    ])->render();

    expect($html)
        ->toContain('Search title')
        ->toContain('Social title')
        ->toContain('ai_content_brief')
        ->toContain('Meta title needs attention.')
        ->toContain('Schema needs attention.')
        ->toContain('Internal links need attention.')
        ->not->toContain('Links')
        ->not->toContain('Schema</div>')
        ->not->toContain('Redirects')
        ->not->toContain('Search Console')
        ->not->toContain('Target keywords')
        ->not->toContain('Robots and canonical')
        ->not->toContain('Passed checks');
});

it('resolves AI brief context and empty report view state from the current page workflow', function (): void {
    $english = Language::factory()->create(['name' => 'English', 'code' => 'en']);
    $french = Language::factory()->create(['name' => 'French', 'code' => 'fr']);
    $site = Site::factory()
        ->language($english)
        ->withTranslations([$english, $french])
        ->create();
    $page = Page::factory()
        ->site($site)
        ->withTranslations([$english, $french])
        ->create(['name' => 'SEO panel context page']);

    $panel = PageSeoPanel::make()->model($page);
    $missingRecordPanel = PageSeoPanel::make()->model(new Page);
    $viewDataForReport = new ReflectionMethod(PageSeoPanel::class, 'viewDataForReport');
    $firstTranslationLanguageId = new ReflectionMethod(PageSeoPanel::class, 'firstTranslationLanguageId');

    $frenchContext = $panel->resolveAiContentBriefContext($french->getKey());
    $fallbackContext = $panel->resolveAiContentBriefContext();
    $emptyViewData = $viewDataForReport->invoke($panel, null);

    expect($frenchContext)->toBeArray()
        ->and($frenchContext['page']->is($page))->toBeTrue()
        ->and($frenchContext['site']->is($site))->toBeTrue()
        ->and($frenchContext['language']->is($french))->toBeTrue()
        ->and($fallbackContext)->toBeArray()
        ->and($fallbackContext['language'])->toBeInstanceOf(Language::class)
        ->and($missingRecordPanel->resolveAiContentBriefContext())->toBeNull()
        ->and($panel->resolveLanguageIdFromState(seoSuitePagePanelFakeGet([
            'language_id' => '',
            '../language_id' => '',
            '../../language_id' => null,
        ])))->toBeNull()
        ->and($firstTranslationLanguageId->invoke($panel, [
            ['language_id' => null],
            ['language_id' => $french->getKey()],
        ]))->toBe($french->getKey())
        ->and($firstTranslationLanguageId->invoke($panel, ['not-an-array']))->toBeNull()
        ->and($emptyViewData)->toMatchArray([
            'report' => null,
            'hasReport' => false,
            'linkIssues' => [],
            'schemaIssues' => [],
            'searchConsoleIssues' => [],
            'redirectOpportunities' => [],
            'passedCheckValues' => [],
        ]);
});
