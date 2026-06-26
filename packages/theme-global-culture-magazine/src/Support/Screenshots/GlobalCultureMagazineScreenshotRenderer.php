<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\GlobalCultureMagazine\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class GlobalCultureMagazineScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-global-culture-magazine::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (GlobalCultureMagazineScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-global-culture-magazine::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#b88a2e',
                neutralColor: '#111111',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'photographic',
                radius: 'md',
                surfaceColor: '#f6f1e7',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'global-culture-magazine',
        ])->render();

        return view('capell-theme-global-culture-magazine::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, GlobalCultureMagazineScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'global-culture-magazine-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('lead-dispatch'),
                $this->section('radio-audio'),
                $this->section('city-guides'),
                $this->section('travel-culture'),
                $this->section('shop-books'),
                $this->section('columnists'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'global-culture-magazine-landing-page' => [
                $this->navigation(),
                $this->section('lead-dispatch', [
                    'heading' => 'A lead dispatch built to hold the page',
                    'summary' => 'A feature landing layout pairs the dispatch with travel and culture context so a single story reads as a destination.',
                ]),
                $this->section('travel-culture'),
                $this->section('radio-audio'),
                $this->section('cta'),
                $this->footer(),
            ],
            'global-culture-magazine-list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'An archive built to be scanned',
                    'summary' => 'Structured listing cards keep affairs, travel, culture, and radio legible without the theme owning content records.',
                ]),
                $this->section('city-guides'),
                $this->section('cta'),
                $this->footer(),
            ],
            'global-culture-magazine-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search across the whole magazine',
                    'summary' => 'A results layout surfaces places, episodes, columnists, books, and products in one premium, scannable grid.',
                ]),
                $this->section('columnists'),
                $this->footer(),
            ],
            'global-culture-magazine-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'One confident path into the magazine',
                    'summary' => 'A non-submitting newsletter and subscriber CTA proves the conversion journey feels like part of the editorial experience.',
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
    private function section(string $sectionKey, array $data = []): GlobalCultureMagazineScreenshotSection
    {
        return new GlobalCultureMagazineScreenshotSection($sectionKey, $data);
    }

    private function navigation(): GlobalCultureMagazineScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'The Global Review',
            'items' => [
                ['label' => 'Affairs', 'url' => '#lead-dispatch'],
                ['label' => 'Travel & culture', 'url' => '#travel-culture'],
                ['label' => 'Radio', 'url' => '#radio-audio'],
                ['label' => 'City guides', 'url' => '#city-guides'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): GlobalCultureMagazineScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A global magazine for the stories that matter',
            'eyebrow' => 'Global Culture Magazine',
            'summary' => 'An editorial homepage for lead dispatches, radio and audio, city guides, travel, culture, books, shop modules, columnists, and newsletter journeys.',
            'actions' => [
                ['label' => 'Read the dispatch', 'url' => '#lead-dispatch'],
                ['label' => 'Browse city guides', 'url' => '#city-guides'],
            ],
        ]);
    }

    private function footer(): GlobalCultureMagazineScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'The Global Review',
            'items' => [
                ['label' => 'Affairs', 'url' => '#lead-dispatch'],
                ['label' => 'Travel & culture', 'url' => '#travel-culture'],
                ['label' => 'Radio', 'url' => '#radio-audio'],
                ['label' => 'City guides', 'url' => '#city-guides'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'global-culture-magazine-landing-page' => 'Theme Global Culture Magazine landing page',
            'global-culture-magazine-list-page' => 'Theme Global Culture Magazine listing',
            'global-culture-magazine-search-results' => 'Theme Global Culture Magazine search results',
            'global-culture-magazine-contact-form' => 'Theme Global Culture Magazine contact form',
            default => 'Theme Global Culture Magazine homepage',
        };
    }
}
