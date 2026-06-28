<?php

declare(strict_types=1);

use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;
use Capell\ThemeStudio\Agency\Support\Demo\AgencyDemoContent;

/**
 * @return array<string, list<string>>
 */
function agencySurfaceSectionTypes(): array
{
    $definitions = (new AgencyDemoContent)->definitions('agency', 'Agency', 'https://agency.test');

    $map = [];

    foreach ($definitions as $definition) {
        $sections = $definition->renderData['sections'] ?? [];
        $map[$definition->surface] = array_map(
            static fn (array $section): string => (string) ($section['type'] ?? ''),
            $sections,
        );
    }

    return $map;
}

it('implements the demo content provider contract', function (): void {
    expect(new AgencyDemoContent)->toBeInstanceOf(ProvidesThemeDemoContent::class);
});

it('provides a complete definition for all seven foundation surfaces', function (): void {
    $definitions = (new AgencyDemoContent)->definitions('agency', 'Agency', 'https://agency.test');

    expect($definitions)->toHaveCount(7)
        ->and($definitions)->each->toBeInstanceOf(ThemeDemoPageDefinition::class)
        ->and(array_map(fn (ThemeDemoPageDefinition $d): string => $d->surface, $definitions))->toBe([
            'homepage',
            'directory',
            'detail',
            'contact',
            'empty',
            'not-found',
            'cta',
        ]);
});

it('seeds an ordered, non-empty section list on every surface', function (): void {
    foreach (agencySurfaceSectionTypes() as $surface => $types) {
        expect($types)->not->toBeEmpty("surface {$surface} has no sections")
            ->and(count($types))->toBeGreaterThan(1, "surface {$surface} is hero-only")
            ->and($types[0])->toBe('hero', "surface {$surface} does not open with a hero");
    }
});

it('emits the agency signature sections on the homepage in order', function (): void {
    expect(agencySurfaceSectionTypes()['homepage'])->toBe([
        'hero',
        'services',
        'project-showcase',
        'case-study',
        'team',
        'client-logos',
        'proof',
        'cta',
    ]);
});

it('routes each secondary surface through its own signature sections', function (): void {
    $map = agencySurfaceSectionTypes();

    expect($map['directory'])->toContain('project-showcase')->toContain('content-listing')
        ->and($map['detail'])->toContain('case-study')->toContain('team')
        ->and($map['contact'])->toContain('services')->toContain('proof')->toContain('cta')
        ->and($map['empty'])->toContain('project-showcase')->toContain('services')
        ->and($map['not-found'])->toContain('cta')
        ->and($map['cta'])->toContain('proof')->toContain('cta');
});

it('uses the expected route slugs for each surface', function (): void {
    $definitions = (new AgencyDemoContent)->definitions('agency', 'Agency', 'https://agency.test');
    $slugs = [];

    foreach ($definitions as $definition) {
        $slugs[$definition->surface] = $definition->slug;
    }

    expect($slugs)->toBe([
        'homepage' => 'theme-agency',
        'directory' => 'theme-agency-directory',
        'detail' => 'theme-agency-detail',
        'contact' => 'theme-agency-contact',
        'empty' => 'theme-agency-empty',
        'not-found' => 'theme-agency-404',
        'cta' => 'theme-agency-cta',
    ]);
});

it('carries navigation and footer chrome on every surface', function (): void {
    $definitions = (new AgencyDemoContent)->definitions('agency', 'Agency', 'https://agency.test');

    foreach ($definitions as $definition) {
        expect($definition->renderData['navigation']['brandName'] ?? null)->toBe('Fieldwork')
            ->and($definition->renderData['footer']['columns'] ?? [])->not->toBeEmpty();
    }
});
