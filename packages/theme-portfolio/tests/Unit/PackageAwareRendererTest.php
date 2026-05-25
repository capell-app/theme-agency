<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Portfolio\PortfolioThemeServiceProvider;

uses(PackagesTestCase::class);

it('passes optional package availability into public section renderers', function (bool $installed, string $expected, string $missing): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/media-library', $installed);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('portfolio', 'work-grid');

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(new readonly class('work-grid') implements ThemeSection
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

    expect($html)->toContain($expected)->not->toContain($missing);
})->with([
    'installed' => [true, 'Connected media library', 'Static work grid'],
    'not installed' => [false, 'Static work grid', 'Connected media library'],
]);
