<?php

declare(strict_types=1);

use Capell\Core\ThemeStudio\Data\GenericSectionData;
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
