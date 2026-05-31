<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Education\EducationThemeServiceProvider;

uses(PackagesTestCase::class);

it('passes optional package availability into public section renderers', function (bool $installed, string $expected, string $missing): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(EducationThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/events', $installed);

    $registry = new ThemeRegistry;
    (new EducationThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('education', 'events');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(new readonly class('events') implements ThemeSection
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
    'installed' => [true, 'Connected events calendar', 'Static events list'],
    'not installed' => [false, 'Static events list', 'Connected events calendar'],
]);
