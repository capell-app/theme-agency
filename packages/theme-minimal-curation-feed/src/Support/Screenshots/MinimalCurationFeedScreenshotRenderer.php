<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MinimalCurationFeed\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class MinimalCurationFeedScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-minimal-curation-feed::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (MinimalCurationFeedScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-minimal-curation-feed::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#2563eb',
                neutralColor: '#6b7280',
                headingFont: 'system',
                bodyFont: 'system',
                spacing: 'balanced',
                cardStyle: 'hairline',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'curation-feed',
                radius: 'sm',
                surfaceColor: '#ffffff',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'dense',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'minimal-curation-feed',
        ])->render();

        return view('capell-theme-minimal-curation-feed::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, MinimalCurationFeedScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'minimal-curation-feed-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('category-tabs'),
                $this->section('curation-feed'),
                $this->section('app-website-icons'),
                $this->section('source-metadata'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'minimal-curation-feed-landing-page' => [
                $this->navigation(),
                $this->section('best-of-views', [
                    'heading' => 'Best-of collections built to be browsed',
                    'summary' => 'A feature page for best-of lists, apps, websites, icons, and product references that stays calm and scannable.',
                ]),
                $this->section('app-website-icons'),
                $this->section('source-metadata'),
                $this->section('cta'),
                $this->footer(),
            ],
            'minimal-curation-feed-list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'The latest feed, archived and legible',
                    'summary' => 'An archive view for latest, best of, apps, websites, icons, makers, sources, and topic tags.',
                ]),
                $this->section('curation-feed'),
                $this->section('cta'),
                $this->footer(),
            ],
            'minimal-curation-feed-search-results' => [
                $this->navigation(),
                $this->section('category-tabs', [
                    'heading' => 'Search across the whole curated feed',
                    'summary' => 'Results for screenshots, apps, websites, icons, makers, sources, ratings, platforms, and topics.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'minimal-curation-feed-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Stay close to the feed in one calm step',
                    'summary' => 'A non-submitting newsletter, link submission, and source suggestion prompt that feels native to the feed.',
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
    private function section(string $sectionKey, array $data = []): MinimalCurationFeedScreenshotSection
    {
        return new MinimalCurationFeedScreenshotSection($sectionKey, $data);
    }

    private function navigation(): MinimalCurationFeedScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Curation Daily',
            'items' => [
                ['label' => 'Latest', 'url' => '#curation-feed'],
                ['label' => 'Best of', 'url' => '#best-of-views'],
                ['label' => 'Apps & icons', 'url' => '#app-website-icons'],
                ['label' => 'Newsletter', 'url' => '#newsletter'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): MinimalCurationFeedScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A daily feed of curated screenshots and references',
            'eyebrow' => 'Minimal Curation Feed',
            'summary' => 'A calm white feed for daily curated links, screenshots, apps, websites, icons, and product references with category tabs, search, and newsletter signup.',
            'actions' => [
                ['label' => 'Browse the feed', 'url' => '#curation-feed'],
                ['label' => 'Join the newsletter', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function footer(): MinimalCurationFeedScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Curation Daily',
            'items' => [
                ['label' => 'Latest', 'url' => '#curation-feed'],
                ['label' => 'Best of', 'url' => '#best-of-views'],
                ['label' => 'Apps & icons', 'url' => '#app-website-icons'],
                ['label' => 'Newsletter', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'minimal-curation-feed-landing-page' => 'Theme Minimal Curation Feed landing page',
            'minimal-curation-feed-list-page' => 'Theme Minimal Curation Feed latest feed',
            'minimal-curation-feed-search-results' => 'Theme Minimal Curation Feed search results',
            'minimal-curation-feed-contact-form' => 'Theme Minimal Curation Feed contact form',
            default => 'Theme Minimal Curation Feed homepage',
        };
    }
}
