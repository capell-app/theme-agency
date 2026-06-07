<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Portfolio\PortfolioThemeServiceProvider;
use Illuminate\Support\Facades\Route;

uses(PackagesTestCase::class);

it('passes optional package availability into public section renderers', function (string $sectionKey, string $packageName, bool $installed, string $expected, string $missing): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled($packageName, $installed);

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('portfolio', $sectionKey);

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(new readonly class($sectionKey) implements ThemeSection
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
                'heading' => 'Package-aware section',
                'section' => (object) [
                    'heading' => 'Package-aware section',
                    'items' => [],
                ],
            ];
        }
    });

    expect($html)->toContain($expected)->not->toContain($missing);
})->with([
    'media library installed' => ['work-grid', 'capell-app/media-library', true, 'Connected media library', 'Static work grid'],
    'media library not installed' => ['work-grid', 'capell-app/media-library', false, 'Static work grid', 'Connected media library'],
    'newsletter installed' => ['newsletter', 'capell-app/newsletter', true, 'Connected newsletter signup is available.', 'Static newsletter CTA is available.'],
    'newsletter not installed' => ['newsletter', 'capell-app/newsletter', false, 'Static newsletter CTA is available.', 'Connected newsletter signup is available.'],
]);

it('renders the newsletter capture form against the installed newsletter route', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(PortfolioThemeServiceProvider::$packageName);
    CapellCore::forcePackageInstalled('capell-app/newsletter');

    Route::post('/newsletter/subscribe', static fn (): string => 'ok')->name('capell-newsletter.subscribe');

    $registry = new ThemeRegistry;
    (new PortfolioThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('portfolio', 'newsletter');

    expect($renderer)->not->toBeNull();
    assert($renderer instanceof SectionRenderer);

    $html = $renderer->render(new readonly class implements ThemeSection
    {
        public function key(): string
        {
            return 'newsletter';
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
                'heading' => 'Creator notes',
                'section' => (object) [
                    'heading' => 'Creator notes',
                ],
            ];
        }
    });

    expect($html)
        ->toContain('Connected newsletter signup is available.')
        ->toContain('action="http://localhost/newsletter/subscribe"')
        ->toContain('method="POST"')
        ->toContain('name="source"')
        ->toContain('theme_portfolio_newsletter')
        ->not->toContain('Static newsletter CTA is available.')
        ->not->toContain('Newsletter capture is ready once a form action is connected.');
});
