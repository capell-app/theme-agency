<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Knowledge\KnowledgeThemeServiceProvider;

uses(PackagesTestCase::class);

it('passes optional package availability into public section renderers', function (bool $installed, string $expected, string $missing): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(KnowledgeThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/newsletter', $installed);

    $registry = new ThemeRegistry;
    (new KnowledgeThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('knowledge', 'newsletter');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(new readonly class('newsletter') implements ThemeSection
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
    'installed' => [true, 'Connected newsletter signup', 'Static newsletter CTA'],
    'not installed' => [false, 'Static newsletter CTA', 'Connected newsletter signup'],
]);

it('passes optional search availability into the search listing renderer', function (bool $installed, string $expected, string $missing): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(KnowledgeThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/search', $installed);

    $registry = new ThemeRegistry;
    (new KnowledgeThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('knowledge', 'search-listing');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(new readonly class('search-listing') implements ThemeSection
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
            return ['heading' => 'Search package-aware section'];
        }
    });

    expect($html)
        ->toContain($expected)
        ->toContain('role="search"')
        ->toContain('type="search"')
        ->toContain('name="q"')
        ->not->toContain($missing);
})->with([
    'installed' => [true, 'Connected search index', 'Static search guide'],
    'not installed' => [false, 'Static search guide', 'Connected search index'],
]);
