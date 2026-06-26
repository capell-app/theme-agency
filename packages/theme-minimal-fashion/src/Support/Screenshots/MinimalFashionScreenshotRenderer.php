<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MinimalFashion\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class MinimalFashionScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-minimal-fashion::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (MinimalFashionScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-minimal-fashion::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#76716a',
                neutralColor: '#1d1d1b',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'framed',
                radius: 'sm',
                surfaceColor: '#f8f7f3',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'minimal-fashion',
        ])->render();

        return view('capell-theme-minimal-fashion::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, MinimalFashionScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'minimal-fashion-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('seasonal-collections'),
                $this->section('category-paths'),
                $this->section('lookbook-feature'),
                $this->section('material-notes'),
                $this->section('product-care'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'minimal-fashion-landing-page' => [
                $this->navigation(),
                $this->hero(),
                $this->section('seasonal-collections', [
                    'heading' => 'A seasonal collection that unfolds quietly',
                    'summary' => 'A restrained landing layout lets a single collection lead while the theme keeps editorial pacing intact.',
                ]),
                $this->section('lookbook-feature'),
                $this->section('newsletter'),
                $this->footer(),
            ],
            'minimal-fashion-list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'A product archive built to be browsed calmly',
                    'summary' => 'Structured listing cards keep products, collections, and lookbooks legible without the theme owning catalogue records.',
                ]),
                $this->section('category-paths'),
                $this->section('cta'),
                $this->footer(),
            ],
            'minimal-fashion-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results that stay quiet and scannable',
                    'summary' => 'A sparse results layout keeps products, lookbooks, and care guides discoverable while preserving the calm retail feel.',
                ]),
                $this->section('category-paths'),
                $this->footer(),
            ],
            'minimal-fashion-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'One quiet path to stay in touch',
                    'summary' => 'A non-submitting newsletter and assistance CTA proves the conversion journey feels like part of the retail experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): MinimalFashionScreenshotSection
    {
        return new MinimalFashionScreenshotSection($sectionKey, $data);
    }

    private function navigation(): MinimalFashionScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Atelier Nord',
            'items' => [
                ['label' => 'Collections', 'url' => '#seasonal-collections'],
                ['label' => 'Lookbook', 'url' => '#lookbook'],
                ['label' => 'Product care', 'url' => '#product-care'],
                ['label' => 'Newsletter', 'url' => '#newsletter'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): MinimalFashionScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A wardrobe edited down to what lasts',
            'eyebrow' => 'Minimal Fashion',
            'summary' => 'A restrained retail homepage for seasonal collections, lookbooks, product care, and quiet conversion paths.',
            'actions' => [
                ['label' => 'Shop the collection', 'url' => '#seasonal-collections'],
                ['label' => 'View the lookbook', 'url' => '#lookbook'],
            ],
        ]);
    }

    private function footer(): MinimalFashionScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Atelier Nord',
            'items' => [
                ['label' => 'Collections', 'url' => '#seasonal-collections'],
                ['label' => 'Lookbook', 'url' => '#lookbook'],
                ['label' => 'Product care', 'url' => '#product-care'],
                ['label' => 'Newsletter', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'minimal-fashion-landing-page' => 'Theme Minimal Fashion landing page',
            'minimal-fashion-list-page' => 'Theme Minimal Fashion listing',
            'minimal-fashion-search-results' => 'Theme Minimal Fashion search results',
            'minimal-fashion-contact-form' => 'Theme Minimal Fashion contact form',
            default => 'Theme Minimal Fashion homepage',
        };
    }
}
