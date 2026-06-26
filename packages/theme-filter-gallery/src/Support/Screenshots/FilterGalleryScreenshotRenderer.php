<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FilterGallery\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class FilterGalleryScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-filter-gallery::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (FilterGalleryScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-filter-gallery::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111827',
                accentColor: '#0ea5e9',
                neutralColor: '#475569',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'thumbnail-grid',
                radius: 'md',
                surfaceColor: '#ffffff',
                foregroundColor: '#111827',
                headingScale: 'balanced',
                cardDensity: 'dense',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'filter-gallery',
        ])->render();

        return view('capell-theme-filter-gallery::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, FilterGalleryScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'filter-gallery-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('filter-hero'),
                $this->section('taxonomy-navigation'),
                $this->section('editor-picks'),
                $this->section('latest-designs'),
                $this->section('blog-mission'),
                $this->section('faq-archives'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'filter-gallery-landing-page' => [
                $this->navigation(),
                $this->section('filter-hero', [
                    'heading' => 'A feature landing built to spotlight a taxonomy',
                    'summary' => 'Editor picks and latest designs frame a single industry, style, color, platform, or technology archive without the theme owning the records.',
                ]),
                $this->section('editor-picks'),
                $this->section('latest-designs'),
                $this->section('cta'),
                $this->footer(),
            ],
            'filter-gallery-list-page' => [
                $this->navigation(),
                $this->section('taxonomy-navigation', [
                    'heading' => 'A filter archive built to be scanned',
                    'summary' => 'Dense taxonomy navigation and a consistent thumbnail grid keep large libraries legible without the theme owning entry records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'filter-gallery-search-results' => [
                $this->navigation(),
                $this->section('filter-hero', [
                    'heading' => 'Search results with selected-filter clarity',
                    'summary' => 'Prominent search, selected-filter chips, and saved views prove the discovery journey feels like part of the gallery experience.',
                ]),
                $this->section('taxonomy-navigation'),
                $this->section('content-listing'),
                $this->footer(),
            ],
            'filter-gallery-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Convert interest through one confident path',
                    'summary' => 'A non-submitting newsletter and submission CTA proves the conversion journey stays premium without the theme owning form records.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): FilterGalleryScreenshotSection
    {
        return new FilterGalleryScreenshotSection($sectionKey, $data);
    }

    private function navigation(): FilterGalleryScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Galleria Index',
            'items' => [
                ['label' => 'Industries', 'url' => '#industries'],
                ['label' => 'Styles', 'url' => '#styles'],
                ['label' => 'Editor picks', 'url' => '#editor-picks'],
                ['label' => 'Latest designs', 'url' => '#latest-designs'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): FilterGalleryScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'An inspiration library built to be filtered',
            'eyebrow' => 'Filter Gallery',
            'summary' => 'A dense, scalable gallery homepage for taxonomy filters, selected-filter chips, saved views, editor picks, latest designs, and discovery-led journeys.',
            'actions' => [
                ['label' => 'Browse the gallery', 'url' => '#latest-designs'],
                ['label' => 'Explore filters', 'url' => '#industries'],
            ],
        ]);
    }

    private function footer(): FilterGalleryScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Galleria Index',
            'items' => [
                ['label' => 'Industries', 'url' => '#industries'],
                ['label' => 'Styles', 'url' => '#styles'],
                ['label' => 'Editor picks', 'url' => '#editor-picks'],
                ['label' => 'Latest designs', 'url' => '#latest-designs'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'filter-gallery-landing-page' => 'Theme Filter Gallery landing page',
            'filter-gallery-list-page' => 'Theme Filter Gallery filter archive',
            'filter-gallery-search-results' => 'Theme Filter Gallery search results',
            'filter-gallery-contact-form' => 'Theme Filter Gallery contact form',
            default => 'Theme Filter Gallery homepage',
        };
    }
}
