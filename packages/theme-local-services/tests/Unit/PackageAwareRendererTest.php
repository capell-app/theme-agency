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
