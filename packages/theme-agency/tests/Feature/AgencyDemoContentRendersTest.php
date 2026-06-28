<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Capell\Core\ThemeStudio\Data\FooterData;
use Capell\Core\ThemeStudio\Data\GenericSectionData;
use Capell\Core\ThemeStudio\Data\NavigationData;
use Capell\Core\ThemeStudio\Data\ThemePageData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Agency\AgencyThemeServiceProvider;
use Capell\ThemeStudio\Agency\Support\Demo\AgencyDemoContent;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

beforeEach(function (): void {
    View::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-agency', __DIR__ . '/../../resources/lang');
});

/**
 * End-to-end proof that the demo content this theme seeds renders through the
 * theme's real signature Blade views into complete HTML — the same view layer a
 * live `/theme-agency` install drives. Guards against a seeded payload whose keys
 * the view cannot read (which would render an empty section or throw).
 *
 * @return array<string, array<string, mixed>>
 */
function agencyHomepageSectionsByType(): array
{
    $homepage = (new AgencyDemoContent)->definitions('agency', 'Agency', 'https://agency.test')[0];
    $sections = $homepage->renderData['sections'] ?? [];

    $byType = [];

    foreach ($sections as $section) {
        $byType[(string) $section['type']] = $section;
    }

    return $byType;
}

/**
 * Render one seeded section entry through its real agency Blade view.
 *
 * @param  array<string, mixed>  $entry
 */
function renderAgencySection(array $entry): string
{
    $type = (string) $entry['type'];
    unset($entry['type']);

    $section = new GenericSectionData($type, $entry);

    return app(ViewFactory::class)
        ->make('capell-theme-agency::sections.' . $type, $section->toViewData())
        ->render();
}

it('renders the project-showcase view with seeded project cards', function (): void {
    $html = renderAgencySection(agencyHomepageSectionsByType()['project-showcase']);

    expect($html)->toContain('Selected work')
        ->and($html)->toContain('Meridian')
        ->and($html)->toContain('Harbour &amp; Co.')
        ->and($html)->not->toContain('project_empty_title');
});

it('renders the case-study view with the full challenge/approach/result story and metrics', function (): void {
    $html = renderAgencySection(agencyHomepageSectionsByType()['case-study']);

    expect($html)->toContain('Rebuilding Meridian')
        ->and($html)->toContain('12 wks')
        ->and($html)->toContain('$24m')
        ->and($html)->not->toContain('case_study_empty_title');
});

it('renders the services view with disciplines and deliverables', function (): void {
    $html = renderAgencySection(agencyHomepageSectionsByType()['services']);

    expect($html)->toContain('Strategy &amp; identity')
        ->and($html)->toContain('Websites &amp; product')
        ->and($html)->toContain('Brand strategy')
        ->and($html)->not->toContain('services_empty_title');
});

it('renders the team view with named people', function (): void {
    $html = renderAgencySection(agencyHomepageSectionsByType()['team']);

    expect($html)->toContain('Priya Nadkarni')
        ->and($html)->toContain('Founder &amp; ECD')
        ->and($html)->not->toContain('team_empty_title');
});

it('renders the client-logos view with wordmarks', function (): void {
    $html = renderAgencySection(agencyHomepageSectionsByType()['client-logos']);

    expect($html)->toContain('Trusted by teams who sweat the details')
        ->and($html)->toContain('Northwind')
        ->and($html)->not->toContain('client_logos_empty_title');
});

it('renders the full homepage through the real agency renderer with chrome and complete body', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(AgencyThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new AgencyThemeServiceProvider($this->app))->boot($registry);
    app()->instance(ThemeRegistry::class, $registry);

    $homepage = (new AgencyDemoContent)->definitions('agency', 'Agency', 'https://agency.test')[0];
    $renderData = $homepage->renderData;

    $sections = [];

    foreach ($renderData['sections'] as $entry) {
        $type = (string) $entry['type'];
        unset($entry['type']);
        $sections[] = new GenericSectionData($type, $entry);
    }

    $page = new ThemePageData(
        title: 'Fieldwork',
        brand: new BrandProfileData(primaryColor: '#be123c', surfaceColor: '#09090b', foregroundColor: '#f8fafc'),
        sections: $sections,
        navigation: NavigationData::from($renderData['navigation']),
        footer: FooterData::from($renderData['footer']),
    );

    $html = $registry->renderer('agency')->render($page);

    // Chrome: the seeded navigation + footer brand both render.
    expect(substr_count($html, 'Fieldwork'))->toBeGreaterThanOrEqual(2)
        ->and($html)->toContain('agency-shell')
        // Signature body across the surface, in order.
        ->and($html)->toContain('What we do')
        ->and($html)->toContain('Selected work')
        ->and($html)->toContain('Rebuilding Meridian')
        ->and($html)->toContain('Priya Nadkarni')
        ->and($html)->toContain('Trusted by teams who sweat the details')
        // No skeleton: no section degraded to its empty state.
        ->and($html)->not->toContain('_empty_title');

    // Order check: hero/services precede the case study, which precedes the footer.
    $servicesAt = strpos($html, 'What we do');
    $caseAt = strpos($html, 'Rebuilding Meridian');
    $ctaAt = strpos($html, 'Have a project in mind?');

    expect($servicesAt)->toBeLessThan($caseAt)
        ->and($caseAt)->toBeLessThan($ctaAt);
});

it('renders every homepage signature section without an empty-state fallback', function (): void {
    foreach (agencyHomepageSectionsByType() as $type => $entry) {
        if (in_array($type, ['hero', 'cta', 'proof'], true)) {
            continue;
        }

        $html = renderAgencySection($entry);

        expect(trim($html))->not->toBe('', "section {$type} rendered empty")
            ->and($html)->not->toContain('_empty_title', "section {$type} fell back to its empty state");
    }
});
