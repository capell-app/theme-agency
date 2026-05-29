<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Education\EducationThemeServiceProvider;

uses(PackagesTestCase::class);

it('defines the Education theme contract', function (): void {
    $definition = EducationThemeServiceProvider::definition();

    expect($definition->key)->toBe('education')
        ->and($definition->package)->toBe('capell-app/theme-education')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('features')
        ->and($definition->includedSections)->toContain('content-listing')
        ->and($definition->includedSections)->toContain('cta')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});

it('renders standard sections through Education views', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EducationThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new EducationThemeServiceProvider($this->app))->boot($registry);

    $featureHtml = $registry
        ->sectionRenderer('education', 'features')
        ->render(new FeatureSectionData(
            heading: 'Programme pathways',
            summary: 'Course cards should feel specific to education.',
            features: [
                ['title' => 'Course discovery', 'summary' => 'Find the right programme.', 'type' => 'Courses'],
            ],
        ));

    $listingHtml = $registry
        ->sectionRenderer('education', 'content-listing')
        ->render(new ContentListingSectionData(
            heading: 'Learning resources',
            summary: 'Cards should support courses and resources.',
            items: [
                ['title' => 'Open day guide', 'summary' => 'Prepare for the next cohort.', 'type' => 'Guide'],
            ],
        ));

    $ctaHtml = $registry
        ->sectionRenderer('education', 'cta')
        ->render(new CtaSectionData(
            heading: 'Open the next cohort',
            summary: 'Move learners into enrolment.',
            actions: [['label' => 'Apply now', 'url' => '#apply', 'style' => 'primary']],
        ));

    $proofHtml = $registry
        ->sectionRenderer('education', 'proof')
        ->render(new ProofSectionData(
            heading: 'Cohort outcomes',
            summary: 'Proof should use education-specific cohort evidence.',
            items: [
                ['metric' => '92%', 'name' => 'Completion', 'summary' => 'Learners complete the pathway with mentor review.'],
            ],
        ));

    expect($featureHtml)
        ->toContain('Programme pathways')
        ->toContain('Learning pathways')
        ->toContain('Pathway step')
        ->toContain('education-pathway-card')
        ->not->toContain('capell-app/theme-education');

    expect($listingHtml)
        ->toContain('Learning resources')
        ->toContain('Open day guide')
        ->toContain('Learner ready')
        ->toContain('education-resource-card')
        ->not->toContain('capell-app/theme-education');

    expect($ctaHtml)
        ->toContain('Open the next cohort')
        ->toContain('Enrolment')
        ->toContain('Choose track')
        ->toContain('education-enrolment-panel')
        ->toContain('Apply now')
        ->not->toContain('capell-app/theme-education');

    expect($proofHtml)
        ->toContain('Cohort evidence')
        ->toContain('92%')
        ->toContain('Completion')
        ->not->toContain('capell-app/theme-education');
});

it('renders hydrated hero data through the Education hero view', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EducationThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new EducationThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('education', 'hero');

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(HeroSectionData::from([
        'heading' => 'Launch a cohort pathway',
        'summary' => 'Hydrated education hero summary.',
        'actions' => [
            ['label' => 'View courses', 'url' => '#courses'],
            ['label' => 'Talk to admissions', 'url' => '#admissions'],
        ],
    ]));

    expect($html)
        ->toContain('Learning pathway')
        ->toContain('Launch a cohort pathway')
        ->toContain('Hydrated education hero summary.')
        ->toContain('education-learning-board')
        ->toContain('Interview-ready path')
        ->toContain('View courses')
        ->toContain('Talk to admissions')
        ->not->toContain('capell-app/theme-education');
});
