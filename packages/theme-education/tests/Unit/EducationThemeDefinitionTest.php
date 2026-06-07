<?php

declare(strict_types=1);

use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Education\EducationThemeServiceProvider;
use Illuminate\Support\ServiceProvider;

uses(PackagesTestCase::class);

it('defines the Education theme contract', function (): void {
    $definition = EducationThemeServiceProvider::definition();

    expect($definition->key)->toBe('education')
        ->and($definition->package)->toBe('capell-app/theme-education')
        ->and($definition->extends)->toBe('default')
        ->and($definition->previewImage)->toBe(EducationThemeServiceProvider::PUBLIC_PREVIEW_IMAGE)
        ->and($definition->assets)->toBe(['css' => EducationThemeServiceProvider::GENERATED_FRONTEND_CSS])
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('features')
        ->and($definition->includedSections)->toContain('content-listing')
        ->and($definition->includedSections)->toContain('cta')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});

it('publishes the declared preview image and registers css through the tailwind source contract', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EducationThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    $provider = new EducationThemeServiceProvider($this->app);
    $provider->boot($registry);

    $publishPaths = ServiceProvider::pathsToPublish(EducationThemeServiceProvider::class, 'capell-theme-education-assets');
    $publishedSourcePath = array_key_first($publishPaths);

    expect($publishPaths)->toHaveCount(1)
        ->and(realpath((string) $publishedSourcePath))->toBe(realpath(__DIR__ . '/../../docs/assets/marketplace/extension-card.jpg'))
        ->and($publishPaths[$publishedSourcePath])->toBe(public_path(ltrim(EducationThemeServiceProvider::PUBLIC_PREVIEW_IMAGE, '/')));

    $packageImports = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindImport)
        ->filter(static fn (mixed $asset): bool => $asset->packageName === EducationThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    $packageSources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(static fn (mixed $asset): bool => $asset->packageName === EducationThemeServiceProvider::$packageName)
        ->pluck('value')
        ->all();

    expect($packageImports)->toContain(EducationThemeServiceProvider::TAILWIND_IMPORT)
        ->and($packageSources)->toContain(EducationThemeServiceProvider::TAILWIND_SOURCE)
        ->and($packageImports)->not->toContain('vendor/capell/themes/education.css')
        ->and(file_exists(__DIR__ . '/../../' . EducationThemeServiceProvider::TAILWIND_IMPORT))->toBeTrue();
});

it('has renderable views for every non foundation included section', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EducationThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new EducationThemeServiceProvider($this->app))->boot($registry);

    $foundationSections = ['navigation', 'footer'];

    foreach (EducationThemeServiceProvider::definition()->includedSections as $sectionKey) {
        if (in_array($sectionKey, $foundationSections, true)) {
            continue;
        }

        expect(view()->exists('capell-theme-education::sections.' . $sectionKey))
            ->toBeTrue('Missing Education section view for [' . $sectionKey . '].');

        expect($registry->sectionRenderer('education', $sectionKey))
            ->toBeInstanceOf(SectionRenderer::class);
    }
});

it('renders standard sections through Education views', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EducationThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new EducationThemeServiceProvider($this->app))->boot($registry);

    $featureRenderer = $registry->sectionRenderer('education', 'features');
    $listingRenderer = $registry->sectionRenderer('education', 'content-listing');
    $ctaRenderer = $registry->sectionRenderer('education', 'cta');
    $proofRenderer = $registry->sectionRenderer('education', 'proof');

    assert($featureRenderer instanceof SectionRenderer);
    assert($listingRenderer instanceof SectionRenderer);
    assert($ctaRenderer instanceof SectionRenderer);
    assert($proofRenderer instanceof SectionRenderer);

    $featureHtml = $featureRenderer->render(new FeatureSectionData(
        heading: 'Programme pathways',
        summary: 'Course cards should feel specific to education.',
        features: [
            ['title' => 'Course discovery', 'description' => 'Find the right programme.', 'type' => 'Courses'],
        ],
    ));

    $listingHtml = $listingRenderer->render(new ContentListingSectionData(
        heading: 'Learning resources',
        summary: 'Cards should support courses and resources.',
        items: [
            ['title' => 'Open day guide', 'summary' => 'Prepare for the next cohort.', 'type' => 'Guide'],
        ],
    ));

    $ctaHtml = $ctaRenderer->render(new CtaSectionData(
        heading: 'Open the next cohort',
        summary: 'Move learners into enrolment.',
        actions: [['label' => 'Apply now', 'url' => '#apply', 'style' => 'primary']],
    ));

    $proofHtml = $proofRenderer->render(new ProofSectionData(
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
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(HeroSectionData::from([
        'heading' => 'Launch a cohort pathway',
        'summary' => 'Hydrated education hero summary.',
        'mediaUrl' => '/images/education-hero.jpg',
        'mediaAlt' => 'Learners reviewing a pathway board',
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
        ->toContain('src="/images/education-hero.jpg"')
        ->toContain('alt="Learners reviewing a pathway board"')
        ->toContain('width="1200"')
        ->toContain('height="750"')
        ->toContain('loading="eager"')
        ->toContain('fetchpriority="high"')
        ->toContain('sizes="(min-width: 1024px) 52vw, 100vw"')
        ->toContain('Interview-ready path')
        ->toContain('View courses')
        ->toContain('Talk to admissions')
        ->not->toContain('capell-app/theme-education');
});

it('renders new premium education layouts through the registry', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EducationThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new EducationThemeServiceProvider($this->app))->boot($registry);

    $pathwayRenderer = $registry->sectionRenderer('education', 'pathway-comparison');
    $outcomesRenderer = $registry->sectionRenderer('education', 'outcomes');
    $admissionsRenderer = $registry->sectionRenderer('education', 'admissions-checklist');

    assert($pathwayRenderer instanceof SectionRenderer);
    assert($outcomesRenderer instanceof SectionRenderer);
    assert($admissionsRenderer instanceof SectionRenderer);

    $pathwayHtml = $pathwayRenderer->render(educationThemeSection('pathway-comparison', [
        'heading' => 'Compare learning pathways',
        'items' => [
            ['title' => 'Evening cohort', 'summary' => 'Flexible study route for working learners.'],
        ],
    ]));

    $outcomesHtml = $outcomesRenderer->render(educationThemeSection('outcomes', [
        'heading' => 'Learner outcomes',
        'items' => [
            ['title' => 'Completion proof', 'summary' => 'Evidence that learners can finish and progress.'],
        ],
    ]));

    $admissionsHtml = $admissionsRenderer->render(educationThemeSection('admissions-checklist', [
        'heading' => 'Prepare your application',
        'items' => [
            ['title' => 'Portfolio review', 'summary' => 'Application guidance for practical programme fit.'],
        ],
    ]));

    expect($pathwayHtml)
        ->toContain('Compare learning pathways')
        ->toContain('Evening cohort')
        ->not->toContain('capell-app/theme-education');

    expect($outcomesHtml)
        ->toContain('Learner outcomes')
        ->toContain('Completion proof')
        ->not->toContain('capell-app/theme-education');

    expect($admissionsHtml)
        ->toContain('Prepare your application')
        ->toContain('Portfolio review')
        ->not->toContain('capell-app/theme-education');
});

it('renders translated education catalogue, event, and instructor defaults', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EducationThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/events', false);

    $registry = new ThemeRegistry;
    (new EducationThemeServiceProvider($this->app))->boot($registry);

    $catalogueRenderer = $registry->sectionRenderer('education', 'course-catalog');
    $eventsRenderer = $registry->sectionRenderer('education', 'events');
    $instructorsRenderer = $registry->sectionRenderer('education', 'instructors');

    assert($catalogueRenderer instanceof SectionRenderer);
    assert($eventsRenderer instanceof SectionRenderer);
    assert($instructorsRenderer instanceof SectionRenderer);

    $catalogueHtml = $catalogueRenderer->render(educationThemeSection('course-catalog', [
        'heading' => 'Find your course',
    ]));

    $eventsHtml = $eventsRenderer->render(educationThemeSection('events', [
        'heading' => 'Open days',
    ]));

    $instructorsHtml = $instructorsRenderer->render(educationThemeSection('instructors', [
        'heading' => 'Meet the team',
    ]));

    expect($catalogueHtml)
        ->toContain('Starter Path')
        ->toContain('Cohort Tracks')
        ->toContain('Advanced Badge')
        ->toContain('data-carousel="course-catalog"')
        ->not->toContain('data-carousel="education-course-catalog"')
        ->not->toContain('data-carousel-prev')
        ->not->toContain('data-carousel-next');

    expect($eventsHtml)
        ->toContain('Static events list is available.')
        ->toContain('Live Workshops')
        ->toContain('Masterclasses')
        ->toContain('Mentor Access');

    expect($instructorsHtml)
        ->toContain('Programme lead')
        ->toContain('Cohort mentor')
        ->toContain('Assessment coach')
        ->toContain('Named educator profile');
});

it('renders editor-provided education catalogue and event items', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EducationThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/events');

    $registry = new ThemeRegistry;
    (new EducationThemeServiceProvider($this->app))->boot($registry);

    $catalogueRenderer = $registry->sectionRenderer('education', 'course-catalog');
    $eventsRenderer = $registry->sectionRenderer('education', 'events');

    assert($catalogueRenderer instanceof SectionRenderer);
    assert($eventsRenderer instanceof SectionRenderer);

    $catalogueHtml = $catalogueRenderer->render(educationThemeSection('course-catalog', [
        'heading' => 'Find your course',
        'items' => [
            ['format' => 'Evening', 'title' => 'Laravel Academy', 'summary' => 'A practical cohort for working developers.', 'url' => '/courses/laravel-academy'],
        ],
    ]));

    $eventsHtml = $eventsRenderer->render(educationThemeSection('events', [
        'heading' => 'Open days',
        'items' => [
            ['signal' => 'Open day', 'title' => 'Campus preview', 'summary' => 'Meet mentors before applications close.', 'date' => '12 Sep', 'url' => '/events/campus-preview'],
        ],
    ]));

    expect($catalogueHtml)
        ->toContain('Laravel Academy')
        ->toContain('/courses/laravel-academy')
        ->not->toContain('Starter Path');

    expect($eventsHtml)
        ->toContain('Connected events calendar is live and ready.')
        ->toContain('Campus preview')
        ->toContain('12 Sep')
        ->toContain('/events/campus-preview')
        ->not->toContain('Live Workshops');
});

it('keeps education default card copy in translations instead of Blade literals', function (): void {
    $catalogueBlade = file_get_contents(__DIR__ . '/../../resources/views/sections/course-catalog.blade.php');
    $eventsBlade = file_get_contents(__DIR__ . '/../../resources/views/sections/events.blade.php');
    $instructorsBlade = file_get_contents(__DIR__ . '/../../resources/views/sections/instructors.blade.php');

    expect($catalogueBlade)->not->toBeFalse()
        ->and($eventsBlade)->not->toBeFalse()
        ->and($instructorsBlade)->not->toBeFalse();

    $blade = $catalogueBlade . "\n" . $eventsBlade . "\n" . $instructorsBlade;

    expect($blade)
        ->not->toContain('Starter Path')
        ->not->toContain('Cohort Tracks')
        ->not->toContain('Advanced Badge')
        ->not->toContain('Live Workshops')
        ->not->toContain('Masterclasses')
        ->not->toContain('Office Hours')
        ->not->toContain('Programme lead')
        ->not->toContain('Cohort mentor')
        ->not->toContain('Assessment coach')
        ->not->toContain('data-carousel-prev')
        ->not->toContain('data-carousel-next');
});

/**
 * @param  array<string, mixed>  $viewData
 */
function educationThemeSection(string $key, array $viewData): ThemeSection
{
    return new readonly class($key, $viewData) implements ThemeSection
    {
        /**
         * @param  array<string, mixed>  $viewData
         */
        public function __construct(
            private string $sectionKey,
            private array $viewData,
        ) {}

        public function key(): string
        {
            return $this->sectionKey;
        }

        public function fallbackKey(): ?string
        {
            return null;
        }

        /**
         * @return array<string, mixed>
         */
        public function toViewData(): array
        {
            return ['section' => (object) $this->viewData];
        }
    };
}
