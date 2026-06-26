<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AutomotiveDealer\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class AutomotiveDealerScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-automotive-dealer::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (AutomotiveDealerScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-automotive-dealer::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#dc2626',
                accentColor: '#0ea5e9',
                neutralColor: '#18181b',
                headingFont: 'space-grotesk',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'elevated',
                navigationStyle: 'prominent',
                layoutPresentation: 'immersive',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#0d0d0f',
                foregroundColor: '#f4f4f5',
                headingScale: 'dramatic',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'automotive-dealer',
        ])->render();

        return view('capell-theme-automotive-dealer::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, AutomotiveDealerScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'automotive-dealer-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('inventory-grid'),
                $this->section('finance-options'),
                $this->section('part-exchange'),
                $this->section('test-drive-panel'),
                $this->section('features'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'automotive-dealer-directory' => [
                $this->navigation(),
                $this->section('inventory-grid', [
                    'heading' => 'A showroom directory built to be scanned',
                    'summary' => 'Structured vehicle cards keep the forecourt legible and premium without the theme owning stock records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'automotive-dealer-detail' => [
                $this->navigation(),
                $this->section('vehicle-detail', [
                    'heading' => 'A vehicle profile that reads with confidence',
                    'summary' => 'A single vehicle view pairs specification and proof so buyers can reserve with confidence.',
                ]),
                $this->section('finance-options'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'automotive-dealer-contact' => [
                $this->navigation(),
                $this->section('test-drive-panel', [
                    'heading' => 'Reach the dealership through one confident path',
                    'summary' => 'A non-submitting test-drive panel proves the contact journey feels like part of the showroom experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'automotive-dealer-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing in stock here yet',
                    'summary' => 'An empty listing state stays premium and structured while the dealership prepares its inventory.',
                ]),
                $this->footer(),
            ],
            'automotive-dealer-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the dealership confident and routes visitors back into the showroom journey.',
                ]),
                $this->footer(),
            ],
            'automotive-dealer-cta' => [
                $this->navigation(),
                $this->section('finance-options'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a reserved vehicle',
                    'summary' => 'A conversion-focused CTA stack keeps the path to reserving a car direct and premium.',
                ]),
                $this->footer(),
            ],
            'automotive-dealer-inventory' => [
                $this->navigation(),
                $this->section('inventory-grid', [
                    'heading' => 'Inventory built to be scanned and trusted',
                    'summary' => 'Structured stock groupings keep the forecourt legible and premium without owning vehicle records.',
                ]),
                $this->section('finance-options'),
                $this->section('cta'),
                $this->footer(),
            ],
            'automotive-dealer-vehicle-detail' => [
                $this->navigation(),
                $this->section('vehicle-detail', [
                    'heading' => 'Every detail of the vehicle in one view',
                    'summary' => 'An editorial vehicle profile keeps specification and proof legible without the theme owning stock records.',
                ]),
                $this->section('finance-options'),
                $this->section('test-drive-panel'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'automotive-dealer-finance' => [
                $this->navigation(),
                $this->section('finance-options', [
                    'heading' => 'Finance options laid out with confidence',
                    'summary' => 'Structured finance and part-exchange panels keep the funding journey premium without owning quote records.',
                ]),
                $this->section('part-exchange'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): AutomotiveDealerScreenshotSection
    {
        return new AutomotiveDealerScreenshotSection($sectionKey, $data);
    }

    private function navigation(): AutomotiveDealerScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Velston Motors',
            'items' => [
                ['label' => 'Inventory', 'url' => '#inventory'],
                ['label' => 'Finance', 'url' => '#finance'],
                ['label' => 'Part exchange', 'url' => '#part-exchange'],
                ['label' => 'Test drive', 'url' => '#test-drive'],
            ],
            'consultationUrl' => '#test-drive',
        ]);
    }

    private function hero(): AutomotiveDealerScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Prestige and performance cars, ready to drive away',
            'eyebrow' => 'Automotive Dealer',
            'summary' => 'A confident dealership homepage for inventory, vehicle detail, finance, part exchange, and test-drive-led journeys.',
            'actions' => [
                ['label' => 'Browse inventory', 'url' => '#inventory'],
                ['label' => 'Book a test drive', 'url' => '#test-drive'],
            ],
        ]);
    }

    private function footer(): AutomotiveDealerScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Velston Motors',
            'items' => [
                ['label' => 'Inventory', 'url' => '#inventory'],
                ['label' => 'Finance', 'url' => '#finance'],
                ['label' => 'Part exchange', 'url' => '#part-exchange'],
                ['label' => 'Test drive', 'url' => '#test-drive'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'automotive-dealer-directory' => 'Theme Automotive Dealer directory',
            'automotive-dealer-detail' => 'Theme Automotive Dealer detail',
            'automotive-dealer-contact' => 'Theme Automotive Dealer contact',
            'automotive-dealer-empty' => 'Theme Automotive Dealer empty state',
            'automotive-dealer-not-found' => 'Theme Automotive Dealer 404 state',
            'automotive-dealer-cta' => 'Theme Automotive Dealer conversion CTA',
            'automotive-dealer-inventory' => 'Theme Automotive Dealer inventory',
            'automotive-dealer-vehicle-detail' => 'Theme Automotive Dealer vehicle detail',
            'automotive-dealer-finance' => 'Theme Automotive Dealer finance',
            default => 'Theme Automotive Dealer homepage',
        };
    }
}
