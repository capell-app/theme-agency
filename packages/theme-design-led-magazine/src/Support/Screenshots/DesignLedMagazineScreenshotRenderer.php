<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DesignLedMagazine\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class DesignLedMagazineScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-design-led-magazine::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (DesignLedMagazineScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-design-led-magazine::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#9b2f2f',
                neutralColor: '#1d1b18',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'photographic',
                radius: 'md',
                surfaceColor: '#f7f4ee',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'design-led-magazine',
        ])->render();

        return view('capell-theme-design-led-magazine::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, DesignLedMagazineScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'design-led-magazine-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('lead-story'),
                $this->section('editor-picks'),
                $this->section('vertical-categories'),
                $this->section('gallery-feature'),
                $this->section('product-credits'),
                $this->section('trend-list'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'design-led-magazine-landing-page' => [
                $this->navigation(),
                $this->section('lead-story', [
                    'heading' => 'A feature story built to be seen, not skimmed',
                    'summary' => 'A photography-led landing page pairs an architecture or interiors feature with editorial pacing so the story carries the theme.',
                ]),
                $this->section('gallery-feature'),
                $this->section('product-credits'),
                $this->section('cta'),
                $this->footer(),
            ],
            'design-led-magazine-list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'An archive built to be scanned and explored',
                    'summary' => 'Structured listing cards keep architecture, interiors, fashion, and art legible without the theme owning content records.',
                ]),
                $this->section('vertical-categories'),
                $this->section('cta'),
                $this->footer(),
            ],
            'design-led-magazine-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search across stories, galleries, and credits',
                    'summary' => 'A results view stays editorial and legible across categories, designers, makers, and trends without bespoke search records.',
                ]),
                $this->section('trend-list'),
                $this->footer(),
            ],
            'design-led-magazine-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Reach the desk through one calm conversion path',
                    'summary' => 'A non-submitting newsletter and inquiry CTA proves the contact journey feels like part of the editorial experience.',
                ]),
                $this->section('proof'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): DesignLedMagazineScreenshotSection
    {
        return new DesignLedMagazineScreenshotSection($sectionKey, $data);
    }

    private function navigation(): DesignLedMagazineScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Atrium & Field',
            'items' => [
                ['label' => 'Architecture', 'url' => '#lead-story'],
                ['label' => 'Galleries', 'url' => '#gallery-feature'],
                ['label' => 'Product credits', 'url' => '#product-credits'],
                ['label' => 'Trends', 'url' => '#trend-list'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): DesignLedMagazineScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A design magazine that reads like an exhibition',
            'eyebrow' => 'Design Led Magazine',
            'summary' => 'A photography-led editorial homepage for lead stories, editor picks, vertical categories, gallery features, product credits, and newsletter conversion.',
            'actions' => [
                ['label' => 'Read the lead story', 'url' => '#lead-story'],
                ['label' => 'Browse galleries', 'url' => '#gallery-feature'],
            ],
        ]);
    }

    private function footer(): DesignLedMagazineScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Atrium & Field',
            'items' => [
                ['label' => 'Architecture', 'url' => '#lead-story'],
                ['label' => 'Galleries', 'url' => '#gallery-feature'],
                ['label' => 'Product credits', 'url' => '#product-credits'],
                ['label' => 'Trends', 'url' => '#trend-list'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'design-led-magazine-landing-page' => 'Theme Design Led Magazine landing page',
            'design-led-magazine-list-page' => 'Theme Design Led Magazine list page',
            'design-led-magazine-search-results' => 'Theme Design Led Magazine search results',
            'design-led-magazine-contact-form' => 'Theme Design Led Magazine contact form',
            default => 'Theme Design Led Magazine homepage',
        };
    }
}
