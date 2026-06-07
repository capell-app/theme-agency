<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Contracts\SectionRenderer;
use Capell\Core\ThemeStudio\Contracts\ThemeSection;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Nonprofit\NonprofitThemeServiceProvider;

uses(PackagesTestCase::class);

it('passes optional package availability into public section renderers', function (string $sectionKey, array $packages, string $expected, string $missing): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);

    foreach ($packages as $packageName => $installed) {
        CapellCore::forcePackageInstalled($packageName, $installed);
    }

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('nonprofit', $sectionKey);

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
            return ['heading' => 'Package-aware section'];
        }
    });

    expect($html)->toContain($expected)->not->toContain($missing);
})->with([
    'campaigns connected' => ['campaigns', ['capell-app/campaign-studio' => true], 'Connected campaign workflow', 'Static campaigns'],
    'campaigns static' => ['campaigns', ['capell-app/campaign-studio' => false], 'Static campaigns', 'Connected campaign workflow'],
    'donation impact payments' => ['donation-impact', ['capell-app/payments' => true, 'capell-app/campaign-studio' => true], 'Payments-powered giving routes', 'Connected campaign appeals'],
    'donation impact connected' => ['donation-impact', ['capell-app/campaign-studio' => true], 'Connected campaign appeals', 'Static donation impact'],
    'donation impact static' => ['donation-impact', ['capell-app/campaign-studio' => false], 'Static donation impact', 'Connected campaign appeals'],
    'volunteer donate payments and form' => ['volunteer-donate', ['capell-app/payments' => true, 'capell-app/form-builder' => true], 'Connected giving and supporter forms', 'Static supporter CTA'],
    'volunteer donate connected' => ['volunteer-donate', ['capell-app/form-builder' => true], 'Connected supporter form', 'Static supporter CTA'],
    'volunteer donate static' => ['volunteer-donate', ['capell-app/form-builder' => false], 'Static supporter CTA', 'Connected supporter form'],
    'volunteer shifts connected by form' => ['volunteer-shifts', ['capell-app/form-builder' => true, 'capell-app/events' => false], 'Connected supporter forms', 'Static volunteer opportunities'],
    'volunteer shifts connected by events' => ['volunteer-shifts', ['capell-app/form-builder' => false, 'capell-app/events' => true], 'Connected supporter forms', 'Static volunteer opportunities'],
    'volunteer shifts static' => ['volunteer-shifts', ['capell-app/form-builder' => false, 'capell-app/events' => false], 'Static volunteer opportunities', 'Connected supporter forms'],
    'events connected' => ['events', ['capell-app/events' => true], 'Connected events calendar', 'Static events list'],
    'events static' => ['events', ['capell-app/events' => false], 'Static events list', 'Connected events calendar'],
    'stories connected' => ['stories', ['capell-app/blog' => true], 'Connected story feed', 'Static stories'],
    'stories static' => ['stories', ['capell-app/blog' => false], 'Static stories', 'Connected story feed'],
]);
