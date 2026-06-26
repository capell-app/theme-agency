<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DenseNewsAnalysis\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class DenseNewsAnalysisScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-dense-news-analysis::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (DenseNewsAnalysisScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-dense-news-analysis::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#b21f2d',
                neutralColor: '#111111',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'photographic',
                radius: 'md',
                surfaceColor: '#ffffff',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'dense-news-analysis',
        ])->render();

        return view('capell-theme-dense-news-analysis::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, DenseNewsAnalysisScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'dense-news-analysis-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('top-stories'),
                $this->section('live-brief'),
                $this->section('topic-navigation'),
                $this->section('opinion-analysis'),
                $this->section('video-row'),
                $this->section('missed-it'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'dense-news-analysis-landing-page' => [
                $this->navigation(),
                $this->hero(),
                $this->section('top-stories', [
                    'heading' => 'A lead story built to carry the front page',
                    'summary' => 'A focused landing composition keeps the headline, live coverage, and analysis legible without the theme owning story records.',
                ]),
                $this->section('live-brief'),
                $this->section('opinion-analysis'),
                $this->section('video-row'),
                $this->section('cta'),
                $this->footer(),
            ],
            'dense-news-analysis-list-page' => [
                $this->navigation(),
                $this->section('topic-navigation', [
                    'heading' => 'A topic archive built to be scanned',
                    'summary' => 'Structured topic navigation keeps news, analysis, and opinion legible without the theme owning archive records.',
                ]),
                $this->section('content-listing'),
                $this->section('missed-it'),
                $this->section('cta'),
                $this->footer(),
            ],
            'dense-news-analysis-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results across the newsroom',
                    'summary' => 'A structured results view keeps stories, topics, authors, and videos discoverable without the theme owning index records.',
                ]),
                $this->section('topic-navigation'),
                $this->footer(),
            ],
            'dense-news-analysis-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Subscribe through one confident path',
                    'summary' => 'A non-submitting newsletter and conversion block proves the membership journey feels like part of the newsroom experience.',
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
    private function section(string $sectionKey, array $data = []): DenseNewsAnalysisScreenshotSection
    {
        return new DenseNewsAnalysisScreenshotSection($sectionKey, $data);
    }

    private function navigation(): DenseNewsAnalysisScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'The Meridian Review',
            'items' => [
                ['label' => 'Top stories', 'url' => '#top-stories'],
                ['label' => 'Live brief', 'url' => '#live-brief'],
                ['label' => 'Opinion', 'url' => '#opinion-analysis'],
                ['label' => 'Video', 'url' => '#video-row'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): DenseNewsAnalysisScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'News and analysis a newsroom can stand behind',
            'eyebrow' => 'Dense News Analysis',
            'summary' => 'A serious editorial homepage for top stories, live coverage, opinion, video, topic navigation, and reader briefings.',
            'actions' => [
                ['label' => 'Read top stories', 'url' => '#top-stories'],
                ['label' => 'Follow live coverage', 'url' => '#live-brief'],
            ],
        ]);
    }

    private function footer(): DenseNewsAnalysisScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'The Meridian Review',
            'items' => [
                ['label' => 'Top stories', 'url' => '#top-stories'],
                ['label' => 'Live brief', 'url' => '#live-brief'],
                ['label' => 'Opinion', 'url' => '#opinion-analysis'],
                ['label' => 'Video', 'url' => '#video-row'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'dense-news-analysis-landing-page' => 'Theme Dense News Analysis landing page',
            'dense-news-analysis-list-page' => 'Theme Dense News Analysis list page',
            'dense-news-analysis-search-results' => 'Theme Dense News Analysis search results',
            'dense-news-analysis-contact-form' => 'Theme Dense News Analysis contact form',
            default => 'Theme Dense News Analysis homepage',
        };
    }
}
