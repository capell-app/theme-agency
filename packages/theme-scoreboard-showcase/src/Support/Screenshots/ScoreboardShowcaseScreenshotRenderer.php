<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ScoreboardShowcase\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class ScoreboardShowcaseScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-scoreboard-showcase::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (ScoreboardShowcaseScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-scoreboard-showcase::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#241f1b',
                accentColor: '#c2410c',
                neutralColor: '#5f5147',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'scoreboard',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'score-media',
                radius: 'md',
                surfaceColor: '#fbf3e7',
                foregroundColor: '#241f1b',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'scoreboard-showcase',
        ])->render();

        return view('capell-theme-scoreboard-showcase::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, ScoreboardShowcaseScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'scoreboard-showcase-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('winner-hero'),
                $this->section('score-criteria'),
                $this->section('newest-nominees'),
                $this->section('previous-winners'),
                $this->section('voting-status'),
                $this->section('creator-credits'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'scoreboard-showcase-landing-page' => [
                $this->navigation(),
                $this->section('winner-hero', [
                    'heading' => 'A winner page that leads with the score',
                    'summary' => 'A featured nominee view pairs the numeric score breakdown with creator credits so visitors can read the verdict at a glance.',
                ]),
                $this->section('score-criteria'),
                $this->section('creator-credits'),
                $this->section('cta'),
                $this->footer(),
            ],
            'scoreboard-showcase-list-page' => [
                $this->navigation(),
                $this->section('newest-nominees', [
                    'heading' => 'An awards archive built to be scanned',
                    'summary' => 'Structured nominee cards keep winners, score ranges, and judging years legible without the theme owning entry records.',
                ]),
                $this->section('content-listing'),
                $this->section('previous-winners'),
                $this->section('cta'),
                $this->footer(),
            ],
            'scoreboard-showcase-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results across winners and nominees',
                    'summary' => 'A structured results listing keeps discovery across categories, countries, creators, and voting states premium and legible.',
                ]),
                $this->section('newest-nominees'),
                $this->footer(),
            ],
            'scoreboard-showcase-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Reach the awards through one confident path',
                    'summary' => 'A non-submitting newsletter and inquiry CTA proves the conversion journey feels like part of the scoreboard experience.',
                ]),
                $this->section('voting-status'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): ScoreboardShowcaseScreenshotSection
    {
        return new ScoreboardShowcaseScreenshotSection($sectionKey, $data);
    }

    private function navigation(): ScoreboardShowcaseScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Scoreboard Awards',
            'items' => [
                ['label' => 'Winners', 'url' => '#previous-winners'],
                ['label' => 'Nominees', 'url' => '#newest-nominees'],
                ['label' => 'Scores', 'url' => '#score-criteria'],
                ['label' => 'Vote', 'url' => '#voting-status'],
            ],
            'consultationUrl' => '#voting-status',
        ]);
    }

    private function hero(): ScoreboardShowcaseScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Design awards ranked by the numbers',
            'eyebrow' => 'Scoreboard Showcase',
            'summary' => 'A scoreboard homepage for winner-of-the-day modules, segmented score criteria, newest nominees, previous winners, public voting, and creator credits.',
            'actions' => [
                ['label' => 'Cast a vote', 'url' => '#voting-status'],
                ['label' => 'View the scoreboard', 'url' => '#score-criteria'],
            ],
        ]);
    }

    private function footer(): ScoreboardShowcaseScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Scoreboard Awards',
            'items' => [
                ['label' => 'Winners', 'url' => '#previous-winners'],
                ['label' => 'Nominees', 'url' => '#newest-nominees'],
                ['label' => 'Scores', 'url' => '#score-criteria'],
                ['label' => 'Vote', 'url' => '#voting-status'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'scoreboard-showcase-landing-page' => 'Theme Scoreboard Showcase landing page',
            'scoreboard-showcase-list-page' => 'Theme Scoreboard Showcase awards archive',
            'scoreboard-showcase-search-results' => 'Theme Scoreboard Showcase search results',
            'scoreboard-showcase-contact-form' => 'Theme Scoreboard Showcase contact form',
            default => 'Theme Scoreboard Showcase homepage',
        };
    }
}
