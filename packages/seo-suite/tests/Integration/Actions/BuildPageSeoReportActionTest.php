<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\LanguageFactory;
use Capell\Core\Database\Factories\PageFactory;
use Capell\Core\Database\Factories\SiteFactory;
use Capell\Core\Models\PageUrl;
use Capell\SeoSuite\Actions\BuildPageSeoReportAction;
use Capell\SeoSuite\Data\SeoIssueData;
use Capell\SeoSuite\Enums\SeoCheckKeyEnum;
use Capell\SeoSuite\Enums\SeoIssueSeverityEnum;
use Capell\SeoSuite\Models\BrokenLink;
use Illuminate\Contracts\Database\Eloquent\Builder;

it('dashboard-dashboard_reports critical issues for missing title and description', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();
    $page = PageFactory::new()->site($site)->withTranslations($language, ['meta' => []])->create();

    $report = BuildPageSeoReportAction::run($page, $site, $language);

    expect($report->score)->toBeLessThan(100)
        ->and(collect($report->issues)->pluck('key'))->toContain(SeoCheckKeyEnum::MetaTitle)
        ->and(collect($report->issues)->pluck('key'))->toContain(SeoCheckKeyEnum::MetaDescription);
});

it('accounts for every SEO check key in report issues or passed checks', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($language, [
            'title' => 'Complete SEO Page',
            'content' => '<p>Useful public content about package readiness and search visibility.</p>',
            'meta' => [
                'title' => 'A complete search title for this SEO page',
                'description' => 'A complete search description that gives editors enough useful context.',
            ],
        ])
        ->create();

    PageUrl::factory()->page($page)->site($site)->language($language)->state(['url' => '/complete-seo-page'])->create();

    $report = BuildPageSeoReportAction::run($page, $site, $language);
    $coveredKeys = collect($report->issues)
        ->merge($report->passedChecks)
        ->map(fn (mixed $check): ?SeoCheckKeyEnum => $check instanceof SeoIssueData ? $check->key : null)
        ->filter()
        ->map(fn (SeoCheckKeyEnum $key): string => $key->value)
        ->unique()
        ->values()
        ->all();

    expect($coveredKeys)->toEqualCanonicalizing(array_map(
        fn (SeoCheckKeyEnum $key): string => $key->value,
        SeoCheckKeyEnum::cases(),
    ));
});

it('builds search and social previews from translation meta', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($language, [
            'meta' => [
                'title' => 'Search Title',
                'description' => 'Search description for the page.',
                'social_title' => 'Social Title',
                'social_description' => 'Social description for the page.',
            ],
        ])
        ->create();
    $page->translations()->where('language_id', $language->id)->update(['title' => 'Fallback Page Title']);

    $report = BuildPageSeoReportAction::run($page, $site, $language);

    expect($report->searchPreview->title)->toBe('Search Title')
        ->and($report->searchPreview->description)->toBe('Search description for the page.')
        ->and($report->socialPreview->title)->toBe('Social Title')
        ->and($report->socialPreview->description)->toBe('Social description for the page.');
});

it('flags duplicate meta titles in the same site and language', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();

    PageFactory::new()->site($site)->withTranslations($language, ['meta' => ['title' => 'Duplicate Title', 'description' => 'First description.']])->create();
    $page = PageFactory::new()->site($site)->withTranslations($language, ['meta' => ['title' => 'Duplicate Title', 'description' => 'Second description.']])->create();

    $report = BuildPageSeoReportAction::run($page, $site, $language);

    expect(collect($report->issues)->pluck('key'))->toContain(SeoCheckKeyEnum::DuplicateTitle);
});

it('warns when robots directives noindex a page', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();
    $page = PageFactory::new()->site($site)->withTranslations($language, ['meta' => ['title' => 'Search Title', 'description' => 'Search description.']])->create([
        'meta' => ['robots' => ['noindex']],
    ]);

    $report = BuildPageSeoReportAction::run($page, $site, $language);

    $robotsIssue = collect($report->issues)->firstWhere('key', SeoCheckKeyEnum::Robots);

    expect($robotsIssue?->severity)->toBe(SeoIssueSeverityEnum::Warning);
});

it('uses translation robots directives when building page reports', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($language, [
            'meta' => [
                'title' => 'Search Title',
                'description' => 'Search description.',
                'robots' => ['noindex'],
            ],
        ])
        ->create();

    $report = BuildPageSeoReportAction::run($page, $site, $language);

    expect($report->robotsDirectives)->toBe(['noindex'])
        ->and(collect($report->issues)->pluck('key'))->toContain(SeoCheckKeyEnum::Robots);
});

it('detects noindex inside comma separated scalar robots directives', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($language, [
            'meta' => [
                'title' => 'Search Title',
                'description' => 'Search description.',
                'robots' => 'noindex, nofollow',
            ],
        ])
        ->create();

    $report = BuildPageSeoReportAction::run($page, $site, $language);

    expect($report->robotsDirectives)->toBe(['noindex', 'nofollow'])
        ->and(collect($report->issues)->pluck('key'))->toContain(SeoCheckKeyEnum::Robots);
});

it('uses robots directives from the requested translation only', function (): void {
    $english = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $french = LanguageFactory::new()->create(['name' => 'French', 'code' => 'fr']);
    $site = SiteFactory::new()->recycle($english)->language($english)->withTranslations([$english, $french])->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($english, [
            'meta' => [
                'title' => 'English Search Title',
                'description' => 'English search description.',
                'robots' => ['noindex'],
            ],
        ])
        ->create();
    $page->translations()->create([
        'language_id' => $french->id,
        'title' => 'Titre de recherche français',
        'meta' => [
            'title' => 'Titre de recherche français complet',
            'description' => 'Description française complète pour le rapport de référencement.',
            'robots' => ['index'],
        ],
    ]);
    PageUrl::factory()->page($page)->site($site)->language($french)->state(['url' => '/fr/page'])->create();

    $report = BuildPageSeoReportAction::run($page, $site, $french);

    expect($report->robotsDirectives)->toBe(['index'])
        ->and(collect($report->issues)->pluck('key'))->not->toContain(SeoCheckKeyEnum::Robots);
});

it('reloads language scoped relations for the requested language', function (): void {
    $english = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $french = LanguageFactory::new()->create(['name' => 'French', 'code' => 'fr']);
    $site = SiteFactory::new()
        ->recycle($english)
        ->language($english)
        ->withTranslations([$english, $french])
        ->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations([
            $english,
            $french,
        ], [
            $english->id => [
                'meta' => [
                    'title' => 'English Search Title',
                    'description' => 'English search description for the page.',
                ],
            ],
            $french->id => [
                'meta' => [
                    'title' => 'French Search Title',
                    'description' => 'French search description for the page.',
                ],
            ],
        ])
        ->create();

    $page->load([
        'translation' => fn (Builder $query): Builder => $query->where('language_id', $english->id),
        'pageUrl' => fn (Builder $query): Builder => $query->where('language_id', $english->id),
    ]);
    $site->load([
        'translation' => fn (Builder $query): Builder => $query->where('language_id', $english->id),
    ]);

    $report = BuildPageSeoReportAction::run($page, $site, $french);

    expect($report->searchPreview->title)->toBe('French Search Title')
        ->and($report->searchPreview->description)->toBe('French search description for the page.');
});

it('includes passed checks, canonical, robots, and page redirect opportunities', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($language, [
            'meta' => [
                'title' => 'A complete search title for this landing page',
                'description' => 'A complete search description that gives editors enough useful context.',
            ],
        ])
        ->create([
            'meta' => [
                'canonical_url' => 'https://example.com/canonical-page',
                'robots' => ['index', 'follow'],
            ],
        ]);

    PageUrl::factory()->page($page)->site($site)->language($language)->state(['url' => '/canonical-page'])->create();

    BrokenLink::query()->create([
        'page_id' => $page->id,
        'target_url' => '/old-page',
        'http_status' => 404,
        'last_checked_at' => now(),
    ]);

    $report = BuildPageSeoReportAction::run($page, $site, $language);

    expect($report->passedChecks)->not->toBeEmpty()
        ->and($report->canonicalUrl)->toBe('https://example.com/canonical-page')
        ->and($report->robotsDirectives)->toBe(['index', 'follow'])
        ->and($report->redirectOpportunities)->toHaveCount(1)
        ->and($report->redirectOpportunities[0]->sourceUrl)->toBe('/old-page');
});

it('grades on-page content and focus keyword workflow in page reports', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($language, [
            'content' => '<h2>Overview</h2><h4>Details</h4><p>Short introduction without the target phrase.</p>',
            'meta' => [
                'title' => 'A complete search title for this services page',
                'description' => 'A complete search description that gives editors enough useful context.',
                'keywords' => 'laravel cms',
            ],
        ])
        ->create();

    PageUrl::factory()->page($page)->site($site)->language($language)->state(['url' => '/services'])->create();

    $report = BuildPageSeoReportAction::run($page, $site, $language);

    expect($report->contentAnalysis?->focusKeyword)->toBe('laravel cms')
        ->and($report->contentAnalysis?->h1Count)->toBe(0)
        ->and($report->contentAnalysis?->headingOrderValid)->toBeFalse()
        ->and($report->scoreBreakdown?->category('on_page')?->issueCount)->toBeGreaterThan(0)
        ->and(collect($report->issues)->pluck('key'))->toContain(SeoCheckKeyEnum::OnPageContent)
        ->and(collect($report->issues)->pluck('key'))->toContain(SeoCheckKeyEnum::FocusKeyword);
});
