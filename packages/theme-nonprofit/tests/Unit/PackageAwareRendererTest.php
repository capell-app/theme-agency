<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Nonprofit\NonprofitThemeServiceProvider;

uses(PackagesTestCase::class);

it('passes optional package availability into public section renderers', function (bool $installed, string $expected, string $missing): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/campaign-studio', $installed);

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('nonprofit', 'campaigns');

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(new class('campaigns') implements ThemeSection
    {
        public function __construct(private readonly string $sectionKey) {}

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
    'installed' => [true, 'Connected campaign workflow', 'Static campaigns'],
    'not installed' => [false, 'Static campaigns', 'Connected campaign workflow'],
]);
