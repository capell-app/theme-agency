<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuietWebGallery\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class QuietWebGalleryScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-quiet-web-gallery::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (QuietWebGalleryScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-quiet-web-gallery::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#252525',
                accentColor: '#6f7d6a',
                neutralColor: '#6b6b66',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'understated',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'quiet-image-grid',
                radius: 'md',
                surfaceColor: '#f3f3f0',
                foregroundColor: '#252525',
                headingScale: 'balanced',
                cardDensity: 'balanced',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'quiet-web-gallery',
        ])->render();

        return view('capell-theme-quiet-web-gallery::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, QuietWebGalleryScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'quiet-web-gallery-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('browse-panels'),
                $this->section('latest-showcase'),
                $this->section('style-type-categories'),
                $this->section('sponsor-space'),
                $this->section('random-best-of'),
                $this->section('editorial-posts'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quiet-web-gallery-landing-page' => [
                $this->navigation(),
                $this->section('latest-showcase', [
                    'heading' => 'A feature page that lets the work stay quiet',
                    'summary' => 'Latest showcase entries, sponsor space, and best-of lists sit in a calm grid so the gallery reads without shouting.',
                ]),
                $this->section('sponsor-space'),
                $this->section('random-best-of'),
                $this->section('editorial-posts'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quiet-web-gallery-list-page' => [
                $this->navigation(),
                $this->section('style-type-categories', [
                    'heading' => 'Category archives built to be browsed calmly',
                    'summary' => 'Style, type, and category archives keep the listing legible without the theme owning entry records.',
                ]),
                $this->section('content-listing'),
                $this->section('random-best-of'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quiet-web-gallery-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results that stay understated',
                    'summary' => 'A quiet results grid surfaces entries, styles, and categories so discovery feels like part of the gallery.',
                ]),
                $this->section('browse-panels'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quiet-web-gallery-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'One quiet path to follow or submit',
                    'summary' => 'A non-submitting conversion form proves newsletter, submission, and sponsor journeys feel like part of the gallery.',
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
    private function section(string $sectionKey, array $data = []): QuietWebGalleryScreenshotSection
    {
        return new QuietWebGalleryScreenshotSection($sectionKey, $data);
    }

    private function navigation(): QuietWebGalleryScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Quiet Gallery',
            'items' => [
                ['label' => 'Browse', 'url' => '#browse-panels'],
                ['label' => 'Latest', 'url' => '#latest-showcase'],
                ['label' => 'Categories', 'url' => '#style-type-categories'],
                ['label' => 'Best of', 'url' => '#random-best-of'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): QuietWebGalleryScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A calm gallery for the quiet web',
            'eyebrow' => 'Web Gallery',
            'summary' => 'An understated homepage for browse panels, latest showcase entries, sponsor space, category archives, random picks, best-of lists, and simple editorial posts.',
            'actions' => [
                ['label' => 'Browse the gallery', 'url' => '#browse-panels'],
                ['label' => 'See latest entries', 'url' => '#latest-showcase'],
            ],
        ]);
    }

    private function footer(): QuietWebGalleryScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Quiet Gallery',
            'items' => [
                ['label' => 'Browse', 'url' => '#browse-panels'],
                ['label' => 'Latest', 'url' => '#latest-showcase'],
                ['label' => 'Categories', 'url' => '#style-type-categories'],
                ['label' => 'Best of', 'url' => '#random-best-of'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'quiet-web-gallery-landing-page' => 'Theme Quiet Web Gallery landing page',
            'quiet-web-gallery-list-page' => 'Theme Quiet Web Gallery category archive',
            'quiet-web-gallery-search-results' => 'Theme Quiet Web Gallery search results',
            'quiet-web-gallery-contact-form' => 'Theme Quiet Web Gallery contact form',
            default => 'Theme Quiet Web Gallery homepage',
        };
    }
}
