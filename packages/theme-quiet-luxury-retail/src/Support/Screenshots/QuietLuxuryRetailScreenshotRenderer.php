<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuietLuxuryRetail\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class QuietLuxuryRetailScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-quiet-luxury-retail::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (QuietLuxuryRetailScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-quiet-luxury-retail::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#33281f',
                accentColor: '#8a765f',
                neutralColor: '#2a2520',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'framed',
                radius: 'sm',
                surfaceColor: '#f3eee6',
                foregroundColor: '#2a2520',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'quiet-luxury-retail',
        ])->render();

        return view('capell-theme-quiet-luxury-retail::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, QuietLuxuryRetailScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'quiet-luxury-retail-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('ritual-guide'),
                $this->section('product-families'),
                $this->section('ingredient-notes'),
                $this->section('store-consultation'),
                $this->section('usage-guidance'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quiet-luxury-retail-landing-page' => [
                $this->navigation(),
                $this->section('ritual-guide', [
                    'heading' => 'A guided ritual landing built to feel considered',
                    'summary' => 'A consultation-led landing pairs rituals and store assistance so the brand reads as restrained and attentive.',
                ]),
                $this->section('store-consultation'),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quiet-luxury-retail-list-page' => [
                $this->navigation(),
                $this->section('product-families', [
                    'heading' => 'A product family listing built to be browsed',
                    'summary' => 'Structured product families keep the catalogue editorial and legible without the theme owning product records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quiet-luxury-retail-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search across products, rituals, and ingredient notes',
                    'summary' => 'A calm search results view keeps discovery precise and premium across the full catalogue of guidance.',
                ]),
                $this->section('ingredient-notes'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quiet-luxury-retail-contact-form' => [
                $this->navigation(),
                $this->section('store-consultation', [
                    'heading' => 'Reach the store through one considered path',
                    'summary' => 'A non-submitting consultation CTA proves the assistance journey feels like part of the retail experience.',
                ]),
                $this->section('newsletter'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): QuietLuxuryRetailScreenshotSection
    {
        return new QuietLuxuryRetailScreenshotSection($sectionKey, $data);
    }

    private function navigation(): QuietLuxuryRetailScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Maison Lessard',
            'items' => [
                ['label' => 'Rituals', 'url' => '#ritual-guide'],
                ['label' => 'Products', 'url' => '#product-families'],
                ['label' => 'Ingredients', 'url' => '#ingredient-notes'],
                ['label' => 'Consultation', 'url' => '#store-consultation'],
            ],
            'consultationUrl' => '#store-consultation',
        ]);
    }

    private function hero(): QuietLuxuryRetailScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Retail a house can stand behind',
            'eyebrow' => 'Quiet Luxury Retail',
            'summary' => 'A restrained luxury homepage for guided rituals, product families, ingredient notes, store consultation, and considered conversion paths.',
            'actions' => [
                ['label' => 'Book a consultation', 'url' => '#store-consultation'],
                ['label' => 'Explore products', 'url' => '#product-families'],
            ],
        ]);
    }

    private function footer(): QuietLuxuryRetailScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Maison Lessard',
            'items' => [
                ['label' => 'Rituals', 'url' => '#ritual-guide'],
                ['label' => 'Products', 'url' => '#product-families'],
                ['label' => 'Ingredients', 'url' => '#ingredient-notes'],
                ['label' => 'Consultation', 'url' => '#store-consultation'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'quiet-luxury-retail-landing-page' => 'Theme Quiet Luxury Retail landing page',
            'quiet-luxury-retail-list-page' => 'Theme Quiet Luxury Retail listing',
            'quiet-luxury-retail-search-results' => 'Theme Quiet Luxury Retail search results',
            'quiet-luxury-retail-contact-form' => 'Theme Quiet Luxury Retail contact form',
            default => 'Theme Quiet Luxury Retail homepage',
        };
    }
}
