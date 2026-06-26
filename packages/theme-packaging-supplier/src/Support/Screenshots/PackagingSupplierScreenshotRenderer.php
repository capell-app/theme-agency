<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PackagingSupplier\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class PackagingSupplierScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-packaging-supplier::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (PackagingSupplierScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-packaging-supplier::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#16a34a',
                accentColor: '#b45309',
                neutralColor: '#1c2a1f',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#f7f6f0',
                foregroundColor: '#1c2a1f',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'packaging-supplier',
        ])->render();

        return view('capell-theme-packaging-supplier::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, PackagingSupplierScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'packaging-supplier-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('product-range'),
                $this->section('materials'),
                $this->section('sustainability'),
                $this->section('features'),
                $this->section('industries'),
                $this->section('sample-request'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'packaging-supplier-directory' => [
                $this->navigation(),
                $this->section('product-range', [
                    'heading' => 'A product range built to be scanned',
                    'summary' => 'Structured product cards keep the catalogue legible and trustworthy without the theme owning inventory records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'packaging-supplier-detail' => [
                $this->navigation(),
                $this->section('product-range', [
                    'heading' => 'A product profile that reads with confidence',
                    'summary' => 'A single product view pairs materials and proof so buyers can specify with confidence.',
                ]),
                $this->section('materials'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'packaging-supplier-contact' => [
                $this->navigation(),
                $this->section('sample-request', [
                    'heading' => 'Reach the supplier through one confident path',
                    'summary' => 'A non-submitting sample-request CTA proves the contact journey feels like part of the supplier experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'packaging-supplier-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays premium and structured while the supplier prepares its catalogue.',
                ]),
                $this->footer(),
            ],
            'packaging-supplier-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the supplier credible and routes visitors back into the catalogue journey.',
                ]),
                $this->footer(),
            ],
            'packaging-supplier-cta' => [
                $this->navigation(),
                $this->section('sample-request'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a requested sample',
                    'summary' => 'A conversion-focused CTA stack keeps the path to specifying packaging direct and premium.',
                ]),
                $this->footer(),
            ],
            'packaging-supplier-products' => [
                $this->navigation(),
                $this->section('product-range', [
                    'heading' => 'A product range built to be scanned and trusted',
                    'summary' => 'Structured product groupings keep formats legible and credible without owning inventory records.',
                ]),
                $this->section('materials'),
                $this->section('industries'),
                $this->section('cta'),
                $this->footer(),
            ],
            'packaging-supplier-sustainability' => [
                $this->navigation(),
                $this->section('sustainability', [
                    'heading' => 'Sustainability claims built to be trusted',
                    'summary' => 'Structured recyclability and materials proof keeps environmental claims legible and credible.',
                ]),
                $this->section('materials'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'packaging-supplier-samples' => [
                $this->navigation(),
                $this->section('sample-request', [
                    'heading' => 'Request a sample in one confident path',
                    'summary' => 'A non-submitting sample-request CTA proves the request journey feels like part of the supplier experience.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): PackagingSupplierScreenshotSection
    {
        return new PackagingSupplierScreenshotSection($sectionKey, $data);
    }

    private function navigation(): PackagingSupplierScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Verdant Pack Co.',
            'items' => [
                ['label' => 'Product range', 'url' => '#product-range'],
                ['label' => 'Materials', 'url' => '#materials'],
                ['label' => 'Sustainability', 'url' => '#sustainability'],
                ['label' => 'Samples', 'url' => '#samples'],
            ],
            'consultationUrl' => '#samples',
        ]);
    }

    private function hero(): PackagingSupplierScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Packaging a brand can stand behind',
            'eyebrow' => 'Packaging Supplier',
            'summary' => 'A credible B2B packaging homepage for product ranges, materials, sustainability proof, industries served, and sample-led journeys.',
            'actions' => [
                ['label' => 'Request a sample', 'url' => '#samples'],
                ['label' => 'View product range', 'url' => '#product-range'],
            ],
        ]);
    }

    private function footer(): PackagingSupplierScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Verdant Pack Co.',
            'items' => [
                ['label' => 'Product range', 'url' => '#product-range'],
                ['label' => 'Materials', 'url' => '#materials'],
                ['label' => 'Sustainability', 'url' => '#sustainability'],
                ['label' => 'Samples', 'url' => '#samples'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'packaging-supplier-directory' => 'Theme Packaging Supplier directory',
            'packaging-supplier-detail' => 'Theme Packaging Supplier detail',
            'packaging-supplier-contact' => 'Theme Packaging Supplier contact',
            'packaging-supplier-empty' => 'Theme Packaging Supplier empty state',
            'packaging-supplier-not-found' => 'Theme Packaging Supplier 404 state',
            'packaging-supplier-cta' => 'Theme Packaging Supplier conversion CTA',
            'packaging-supplier-products' => 'Theme Packaging Supplier product range',
            'packaging-supplier-sustainability' => 'Theme Packaging Supplier sustainability',
            'packaging-supplier-samples' => 'Theme Packaging Supplier sample request',
            default => 'Theme Packaging Supplier homepage',
        };
    }
}
