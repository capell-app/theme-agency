<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\ThemeStudio\Restaurant\RestaurantThemeServiceProvider;

it('defines the Restaurant premium theme contract', function (): void {
    $definition = RestaurantThemeServiceProvider::definition();

    expect($definition->key)->toBe('restaurant')
        ->and($definition->package)->toBe('capell-app/theme-restaurant')
        ->and($definition->extends)->toBe('default')
        ->and($definition->runtime->value)->toBe('blade')
        ->and($definition->assets)->toBe(['css' => 'vendor/capell/themes/restaurant.css'])
        ->and($definition->tags)->toContain('Hospitality', 'Reservations', 'Menus')
        ->and($definition->bestFit)->toContain('Restaurants')
        ->and($definition->includedSections)->toBe([
            'navigation',
            'hero',
            'menu-highlights',
            'reservation-panel',
            'private-dining',
            'events-calendar',
            'opening-hours',
            'location-guide',
            'chef-story',
            'features',
            'proof',
            'content-listing',
            'cta',
            'footer',
        ])
        ->and($definition->presets)->toHaveCount(1)
        ->and($definition->presets[0]->key)->toBe('restaurant')
        ->and($definition->presets[0]->values)->toHaveKey('mediaTreatment', 'full-bleed');
});

it('renders restaurant-owned hospitality sections through the registry', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(RestaurantThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new RestaurantThemeServiceProvider($this->app))->boot($registry);

    $heroRenderer = $registry->sectionRenderer('restaurant', 'hero');
    $menuRenderer = $registry->sectionRenderer('restaurant', 'menu-highlights');
    $reservationRenderer = $registry->sectionRenderer('restaurant', 'reservation-panel');
    $privateDiningRenderer = $registry->sectionRenderer('restaurant', 'private-dining');

    assert($heroRenderer instanceof SectionRenderer);
    assert($menuRenderer instanceof SectionRenderer);
    assert($reservationRenderer instanceof SectionRenderer);
    assert($privateDiningRenderer instanceof SectionRenderer);

    $heroHtml = $heroRenderer->render(HeroSectionData::from([
        'heading' => 'Neighbourhood dining with a late table rhythm',
        'summary' => 'Hydrated restaurant hero copy should render in the hospitality layout.',
        'mediaUrl' => '/images/restaurant-room.jpg',
        'mediaAlt' => 'Dining room service',
        'actions' => [
            ['label' => 'Reserve tonight', 'url' => '#reservations'],
            ['label' => 'Read the menu', 'url' => '#menu'],
        ],
    ]));

    $menuHtml = $menuRenderer->render(restaurantThemeSection('menu-highlights', [
        'heading' => 'A seasonal menu built for scanning',
        'items' => [
            ['title' => 'Roast cod with green sauce', 'summary' => 'Short dish note.', 'price' => '28'],
        ],
    ]));

    $reservationHtml = $reservationRenderer->render(restaurantThemeSection('reservation-panel', [
        'heading' => 'Reserve the table',
        'summary' => 'Reservation copy should stay public and editor-free.',
    ]));

    $privateDiningHtml = $privateDiningRenderer->render(restaurantThemeSection('private-dining', [
        'heading' => 'Book a private room',
        'items' => [
            ['title' => 'Cellar room', 'summary' => 'Private tasting menu.', 'metric' => '18'],
        ],
    ]));

    expect($heroHtml)
        ->toContain('Neighbourhood dining with a late table rhythm')
        ->toContain('src="/images/restaurant-room.jpg"')
        ->toContain('fetchpriority="high"')
        ->toContain('Reserve tonight')
        ->not->toContain('capell-app/theme-restaurant');

    expect($menuHtml)
        ->toContain('A seasonal menu built for scanning')
        ->toContain('Roast cod with green sauce')
        ->toContain('28')
        ->not->toContain('capell-app/theme-restaurant');

    expect($reservationHtml)
        ->toContain('Reserve the table')
        ->toContain('Request-led fallback')
        ->toContain('Static enquiry route')
        ->not->toContain('capell-app/theme-restaurant');

    expect($privateDiningHtml)
        ->toContain('Book a private room')
        ->toContain('Cellar room')
        ->toContain('18')
        ->not->toContain('capell-app/theme-restaurant');
});

it('passes optional package availability into restaurant sections', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(RestaurantThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/bookings');
    CapellCore::forcePackageInstalled('capell-app/form-builder');
    CapellCore::forcePackageInstalled('capell-app/events');
    CapellCore::forcePackageInstalled('capell-app/blog');

    $registry = new ThemeRegistry;
    (new RestaurantThemeServiceProvider($this->app))->boot($registry);

    $reservationRenderer = $registry->sectionRenderer('restaurant', 'reservation-panel');
    $eventsRenderer = $registry->sectionRenderer('restaurant', 'events-calendar');
    $listingRenderer = $registry->sectionRenderer('restaurant', 'content-listing');

    assert($reservationRenderer instanceof SectionRenderer);
    assert($eventsRenderer instanceof SectionRenderer);
    assert($listingRenderer instanceof SectionRenderer);

    expect($reservationRenderer->render(restaurantThemeSection('reservation-panel', [
        'heading' => 'Integrated reservations',
    ])))
        ->toContain('Live booking flow')
        ->toContain('Live enquiry form')
        ->not->toContain('capell-app/theme-restaurant');

    expect($eventsRenderer->render(restaurantThemeSection('events-calendar', [
        'heading' => 'Connected dining events',
    ])))
        ->toContain('Events connected')
        ->toContain('Connected dining events')
        ->not->toContain('capell-app/theme-restaurant');

    expect($listingRenderer->render(restaurantThemeSection('content-listing', [
        'heading' => 'Dining notes',
    ])))
        ->toContain('Dining notes connected')
        ->toContain('Dining notes')
        ->not->toContain('capell-app/theme-restaurant');
});

/**
 * @param  array<string, mixed>  $viewData
 */
function restaurantThemeSection(string $sectionKey, array $viewData): ThemeSection
{
    return new readonly class($sectionKey, $viewData) implements ThemeSection
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
            return $this->viewData;
        }
    };
}
