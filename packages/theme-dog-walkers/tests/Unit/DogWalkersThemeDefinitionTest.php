<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\DogWalkers\DogWalkersThemeServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;

uses(PackagesTestCase::class);

it('defines the Dog Walkers theme contract', function (): void {
    $definition = DogWalkersThemeServiceProvider::definition();

    expect($definition->key)->toBe('dog-walkers')
        ->and($definition->package)->toBe('capell-app/theme-dog-walkers')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('walk-options')
        ->and($definition->includedSections)->toContain('route-board')
        ->and($definition->includedSections)->toContain('safety-checklist')
        ->and($definition->includedSections)->toContain('service-areas')
        ->and($definition->includedSections)->toContain('meet-the-walkers')
        ->and($definition->includedSections)->toContain('reviews-testimonials')
        ->and($definition->includedSections)->toContain('faq')
        ->and($definition->includedSections)->toContain('structured-data')
        ->and($definition->includedSections)->toContain('enquiry-form')
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->values['bodyFont'])->toBe('ibm-plex-sans');
});

it('renders dog walking sections anonymously without database queries or authoring metadata', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(DogWalkersThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/form-builder', false);
    CapellCore::forcePackageInstalled('capell-app/bookings', false);
    CapellCore::forcePackageInstalled('capell-app/blog', false);

    $registry = new ThemeRegistry;
    (new DogWalkersThemeServiceProvider($this->app))->boot($registry);

    $queryCount = 0;

    DB::listen(static function (QueryExecuted $query) use (&$queryCount): void {
        if (str_starts_with(strtolower($query->sql), 'select')) {
            $queryCount++;
        }
    });

    foreach (dogWalkersOwnedSectionKeys() as $sectionKey) {
        $renderer = $registry->sectionRenderer('dog-walkers', $sectionKey);

        assert($renderer instanceof SectionRenderer);

        $html = $renderer->render(dogWalkersThemeSection($sectionKey, dogWalkersRenderPayload($sectionKey)));

        expect($html)
            ->not->toContain('capell-app/theme-dog-walkers')
            ->not->toContain('Filament')
            ->not->toContain('wire:')
            ->not->toContain('data-field')
            ->not->toContain('model_id');
    }

    expect($queryCount)->toBe(0);
});

it('renders a dog walking hero with enquiry and service area actions', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(DogWalkersThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new DogWalkersThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('dog-walkers', 'hero');

    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(new HeroSectionData(
        heading: 'Trusted daily walks for calmer dogs',
        summary: 'Hydrated dog walking hero summary.',
        mediaUrl: '/images/dog-walker.jpg',
        mediaAlt: 'Dog walker crossing a park with two dogs',
        actions: [
            ['label' => 'Enquire about walks', 'url' => '#enquiry'],
            ['label' => 'Check service areas', 'url' => '#areas'],
        ],
    ));

    expect($html)
        ->toContain('Trusted daily walks for calmer dogs')
        ->toContain('Hydrated dog walking hero summary.')
        ->toContain('src="/images/dog-walker.jpg"')
        ->toContain('alt="Dog walker crossing a park with two dogs"')
        ->toContain('Enquire about walks')
        ->toContain('Check service areas')
        ->not->toContain('capell-app/theme-dog-walkers');
});

it('renders structured data for local pet care without leaking package internals', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(DogWalkersThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new DogWalkersThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('dog-walkers', 'structured-data');

    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(dogWalkersThemeSection('structured-data', [
        'business' => [
            'name' => 'Neighbourhood Walk Co',
            'url' => 'https://walks.example.test',
            'phone' => '+44 20 0000 0000',
            'address' => '12 Park Lane, Cardiff CF10 1AA',
        ],
        'serviceName' => 'Dog walking',
        'areaServed' => 'Cardiff',
        'services' => [
            ['title' => 'Solo dog walks', 'summary' => 'One-to-one dog walking.'],
        ],
        'faqs' => [
            ['question' => 'Do you offer meet-and-greets?', 'answer' => 'Yes, every new dog starts with a calm introduction.'],
        ],
        'openingHours' => [
            ['dayOfWeek' => 'Monday', 'opens' => '08:00', 'closes' => '17:00'],
        ],
    ]));

    expect($html)
        ->toContain('application/ld+json')
        ->toContain('"@context":"https://schema.org"')
        ->toContain('"@type":"LocalBusiness"')
        ->toContain('"@type":"Service"')
        ->toContain('"@type":"FAQPage"')
        ->toContain('Neighbourhood Walk Co')
        ->toContain('Dog walking')
        ->not->toContain('capell-app/theme-dog-walkers')
        ->not->toContain('Filament');
});

/**
 * @param  array<string, mixed>  $viewData
 */
function dogWalkersThemeSection(string $key, array $viewData): ThemeSection
{
    return new class($key, $viewData) implements ThemeSection
    {
        /**
         * @param  array<string, mixed>  $viewData
         */
        public function __construct(private readonly string $key, private readonly array $viewData) {}

        public function key(): string
        {
            return $this->key;
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
            return ['section' => (object) $this->viewData, ...$this->viewData];
        }
    };
}

/**
 * @return list<string>
 */
function dogWalkersOwnedSectionKeys(): array
{
    return collect(DogWalkersThemeServiceProvider::definition()->includedSections)
        ->reject(static fn (string $sectionKey): bool => in_array($sectionKey, ['navigation', 'footer'], true))
        ->values()
        ->all();
}

/**
 * @return array<string, mixed>
 */
function dogWalkersRenderPayload(string $sectionKey): array
{
    return match ($sectionKey) {
        'hero' => [
            'heading' => 'Dog walking built on calm handoffs',
            'summary' => 'Trusted neighbourhood walks with owner updates.',
        ],
        'walk-options' => [
            'heading' => 'Choose the right walk',
            'items' => [['title' => 'Solo walks', 'summary' => 'One-to-one routes.', 'category' => 'Solo']],
        ],
        'route-board' => [
            'heading' => 'Live route board',
            'items' => [['title' => 'West park loop', 'summary' => 'Morning pickup window.', 'type' => 'Morning']],
        ],
        'safety-checklist' => [
            'heading' => 'Safety checklist',
            'items' => [['title' => 'Insured walker', 'summary' => 'Current care cover.', 'issuer' => 'Care proof']],
        ],
        'service-areas' => [
            'heading' => 'Neighbourhood coverage',
            'items' => [['label' => 'Central route', 'url' => '#contact', 'postcode' => 'Morning']],
        ],
        'meet-the-walkers' => [
            'heading' => 'Meet the walkers',
            'items' => [['title' => 'Lead walker', 'summary' => 'Calm, consistent routes.', 'metric' => 'Lead']],
        ],
        'proof' => [
            'heading' => 'Local proof',
            'items' => [['metric' => '4.9', 'label' => 'Rating', 'summary' => 'Pet-parent review average.']],
        ],
        'reviews-testimonials' => [
            'heading' => 'Pet-parent reviews',
            'items' => [['quote' => 'Reliable updates after every walk.', 'name' => 'Local pet parent', 'rating' => 5]],
        ],
        'opening-hours' => [
            'heading' => 'Walk windows',
            'openNow' => true,
            'items' => [['day' => 'Monday to Friday', 'opens' => '08:00', 'closes' => '17:00']],
        ],
        'faq' => [
            'heading' => 'Care questions',
            'items' => [['title' => 'Do you offer meet-and-greets?', 'summary' => 'Yes, every new dog starts with an intro.']],
        ],
        'structured-data' => [
            'business' => ['name' => 'Anonymous Dog Walker'],
            'services' => [['title' => 'Dog walking']],
        ],
        'content-listing' => [
            'heading' => 'Walking guides',
            'items' => [['title' => 'Preparing for a first walk', 'summary' => 'What to share before handoff.']],
        ],
        'resources' => [
            'heading' => 'Pet care resources',
            'items' => [['title' => 'First walk notes', 'summary' => 'Useful owner notes.', 'url' => '/guides/first-walk']],
        ],
        'enquiry-form' => [
            'heading' => 'Enquire about walks',
            'formAction' => '/contact',
            'formMethod' => 'POST',
        ],
        'contact' => [
            'heading' => 'Contact the walking team',
            'phone' => '+44 20 0000 0000',
            'email' => 'walks@example.test',
        ],
        'cta' => [
            'heading' => 'Ready to plan regular walks?',
            'actions' => [['label' => 'Send walk enquiry', 'url' => '#enquiry']],
        ],
        default => [
            'heading' => 'Dog walking section',
            'items' => [['title' => 'Dog walking item', 'summary' => 'Pet care summary.']],
        ],
    };
}
