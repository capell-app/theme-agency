<?php

declare(strict_types=1);

use Capell\AiCreator\Actions\BuildCapellSiteFromSpecAction;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecColorsData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecLanguageData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecPageData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecSectionData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecSiteData;
use Capell\AiCreator\Data\SiteSpec\CapellSiteSpecThemeData;
use Capell\Core\Actions\CreateDefaultLanguagesAction;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\Theme;
use Capell\FoundationTheme\Actions\InstallFoundationThemeLayoutDefaultsAction;
use RuntimeException;

beforeEach(function (): void {
    // Seed the install defaults the builder assumes (home/default layouts +
    // page-content widget) and the primary language. A real cloud/local
    // install already has these; the empty test DB does not.
    InstallFoundationThemeLayoutDefaultsAction::run();
    CreateDefaultLanguagesAction::run(['en']);
});

function buildSpecFixture(): CapellSiteSpecData
{
    return new CapellSiteSpecData(
        site: new CapellSiteSpecSiteData(
            name: 'Bluefin Coffee',
            businessName: 'Bluefin Coffee Roasters',
            organisationType: 'cafe',
            description: 'Single-origin coffee roasted in Bristol.',
        ),
        theme: new CapellSiteSpecThemeData(
            key: 'bluefin',
            colors: new CapellSiteSpecColorsData(primary: '#0b3d4f', secondary: '#e8b04b', accent: '#c0392b'),
            fontFamily: 'Inter',
        ),
        pages: [
            new CapellSiteSpecPageData(
                name: 'Home',
                slug: 'home',
                title: 'Welcome to Bluefin',
                pageType: 'default',
                description: 'Bristol coffee roasters.',
                order: 0,
                sections: [
                    new CapellSiteSpecSectionData(type: 'content', content: '<p>Freshly roasted.</p>', title: 'Our Beans', order: 0),
                    new CapellSiteSpecSectionData(type: 'content', content: '<p>Visit our cafe.</p>', order: 1),
                ],
            ),
            new CapellSiteSpecPageData(
                name: 'About',
                slug: 'about',
                title: 'About Bluefin',
                pageType: 'default',
                description: 'Our story.',
                order: 1,
                sections: [
                    new CapellSiteSpecSectionData(type: 'content', content: '<p>Founded in 2018.</p>', title: 'History', order: 0),
                ],
            ),
        ],
        language: new CapellSiteSpecLanguageData,
    );
}

it('builds a complete site from a spec without any inference', function (): void {
    $site = BuildCapellSiteFromSpecAction::run(buildSpecFixture());

    $language = Language::query()->where('code', 'en')->firstOrFail();

    // Site identity + branding
    expect($site)->toBeInstanceOf(Site::class)
        ->and($site->name)->toBe('Bluefin Coffee')
        ->and($site->language_id)->toBe($language->id)
        ->and($site->meta['business_name'] ?? null)->toBe('Bluefin Coffee Roasters')
        ->and($site->meta['organization_type'] ?? null)->toBe('cafe');

    $theme = Theme::query()->where('key', 'bluefin')->firstOrFail();
    $themeMeta = is_array($theme->meta) ? $theme->meta : [];
    $themeColors = is_array($themeMeta['colors'] ?? null) ? $themeMeta['colors'] : [];
    expect($themeColors['primary'] ?? null)->toBe('#0b3d4f')
        ->and($themeColors['secondary'] ?? null)->toBe('#e8b04b')
        ->and($themeColors['accent'] ?? null)->toBe('#c0392b')
        ->and($themeMeta['font_family'] ?? null)->toBe('Inter');
});

it('creates one page per spec page with the right layout and a resolved url', function (): void {
    $site = BuildCapellSiteFromSpecAction::run(buildSpecFixture());

    $pages = Page::query()->where('site_id', $site->id)->orderBy('id')->get();
    expect($pages)->toHaveCount(2);

    $home = $pages->firstWhere('name', 'Home') ?? throw new RuntimeException('Home page not found');
    $about = $pages->firstWhere('name', 'About') ?? throw new RuntimeException('About page not found');

    // First (order 0) page uses the home layout, the rest use default.
    $homeLayout = $home->layout()->first() ?? throw new RuntimeException('Home layout not found');
    $aboutLayout = $about->layout()->first() ?? throw new RuntimeException('About layout not found');
    expect($homeLayout->key)->toBe('home')
        ->and($aboutLayout->key)->toBe('default');

    // SetupPageUrlsAction created a PageUrl per page at "/{slug}".
    expect(PageUrl::query()->where('url', '/home')->exists())->toBeTrue()
        ->and(PageUrl::query()->where('url', '/about')->exists())->toBeTrue();
});

it('concatenates ordered sections into the page translation content', function (): void {
    $site = BuildCapellSiteFromSpecAction::run(buildSpecFixture());

    $home = Page::query()->where('site_id', $site->id)->where('name', 'Home')->firstOrFail();
    $translation = $home->translations()->firstOrFail();

    // Section title becomes an <h2>, sections appear in order, raw HTML kept.
    expect($translation->content)
        ->toContain('<h2>Our Beans</h2>')
        ->toContain('<p>Freshly roasted.</p>')
        ->toContain('<p>Visit our cafe.</p>');

    $content = $translation->content;
    if (! is_string($content)) {
        throw new RuntimeException('Translation content is not a string');
    }
    $beansAt = strpos($content, 'Freshly roasted');
    $cafeAt = strpos($content, 'Visit our cafe');
    if (! is_int($beansAt) || ! is_int($cafeAt)) {
        throw new RuntimeException('Expected strings not found in translation content');
    }
    expect($beansAt)->toBeLessThan($cafeAt);
});
