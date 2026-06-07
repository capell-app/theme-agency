<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\LocalServices\LocalServicesThemeServiceProvider;

uses(PackagesTestCase::class);

it('passes optional package availability into public section renderers', function (bool $installed, string $expected, string $missing): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/form-builder', $installed);
    CapellCore::forcePackageInstalled('capell-app/bookings', false);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('local-services', 'quote-form');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(new readonly class('quote-form') implements ThemeSection
    {
        public function __construct(private string $sectionKey) {}

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
            return ['heading' => 'Package-aware section'];
        }
    });

    expect($html)
        ->toContain($expected)
        ->toContain('<form')
        ->toContain('name="phone"')
        ->not->toContain($missing)
        ->not->toContain('capell-app/theme-local-services');
})->with([
    'installed' => [true, 'Connected enquiry workflow', 'Send a quote request with your contact details'],
    'not installed' => [false, 'Send a quote request with your contact details', 'Connected enquiry workflow'],
]);

it('renders a booking handoff when Bookings is installed and a booking URL is provided', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/form-builder', false);
    CapellCore::forcePackageInstalled('capell-app/bookings');

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('local-services', 'quote-form');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(new readonly class('quote-form') implements ThemeSection
    {
        public function __construct(private string $sectionKey) {}

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
            return [
                'section' => (object) [
                    'heading' => 'Book a quote slot',
                    'bookingUrl' => '/bookings',
                ],
            ];
        }
    });

    expect($html)
        ->toContain('Book a quote slot')
        ->toContain('Booking slots can be connected')
        ->toContain('href="/bookings"')
        ->toContain('View booking slots')
        ->toContain('<form')
        ->not->toContain('capell-app/theme-local-services')
        ->not->toContain('Filament')
        ->not->toContain('wire:');
});

it('keeps the Form Builder quote embed handle-gated in public Blade', function (): void {
    $blade = file_get_contents(__DIR__ . '/../../resources/views/sections/quote-form.blade.php') ?: '';

    expect($blade)
        ->toContain('$formBuilderAvailable')
        ->toContain('$formHandle')
        ->toContain("@livewire('capell-form-builder::form'")
        ->toContain("'instanceId' => 'theme-local-services-quote'");
});
