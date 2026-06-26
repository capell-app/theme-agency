<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CharacterPortfolioIndex\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class CharacterPortfolioIndexScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-character-portfolio-index::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (CharacterPortfolioIndexScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-character-portfolio-index::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#7b4ee6',
                neutralColor: '#21182b',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#f7f2ff',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'character-portfolio-index',
        ])->render();

        return view('capell-theme-character-portfolio-index::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, CharacterPortfolioIndexScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'character-portfolio-index-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('showcase-headline'),
                $this->section('category-tabs'),
                $this->section('curated-grid'),
                $this->section('standout-notes'),
                $this->section('related-recommendations'),
                $this->section('creator-summary'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'character-portfolio-index-landing-page' => [
                $this->navigation(),
                $this->hero(),
                $this->section('showcase-headline', [
                    'heading' => 'A featured portfolio that reads like an editorial spread',
                    'summary' => 'A standout landing view pairs the curated work with creator context so visitors can engage with the index at a glance.',
                ]),
                $this->section('curated-grid'),
                $this->section('creator-summary'),
                $this->section('cta'),
                $this->footer(),
            ],
            'character-portfolio-index-list-page' => [
                $this->navigation(),
                $this->section('category-tabs', [
                    'heading' => 'A portfolio listing built to be scanned',
                    'summary' => 'Category tabs and curated cards keep disciplines, mediums, and creators legible without the theme owning the records.',
                ]),
                $this->section('curated-grid'),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'character-portfolio-index-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results that stay curated and legible',
                    'summary' => 'Portfolios, creators, and tags surface in a structured grid so discovery feels like part of the index experience.',
                ]),
                $this->section('curated-grid'),
                $this->section('cta'),
                $this->footer(),
            ],
            'character-portfolio-index-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Join the index through one curated path',
                    'summary' => 'A non-submitting newsletter and submission CTA proves the conversion journey feels like part of the portfolio index.',
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
    private function section(string $sectionKey, array $data = []): CharacterPortfolioIndexScreenshotSection
    {
        return new CharacterPortfolioIndexScreenshotSection($sectionKey, $data);
    }

    private function navigation(): CharacterPortfolioIndexScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Index & Co.',
            'items' => [
                ['label' => 'Portfolios', 'url' => '#curated-grid'],
                ['label' => 'Categories', 'url' => '#category-tabs'],
                ['label' => 'Standouts', 'url' => '#standout-notes'],
                ['label' => 'Creators', 'url' => '#creator-summary'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): CharacterPortfolioIndexScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A curated index of portfolios worth bookmarking',
            'eyebrow' => 'Character Portfolio Index',
            'summary' => 'An editorial portfolio index for curated personal, company, design, development, and video work with standout notes, creator summaries, and related recommendations.',
            'actions' => [
                ['label' => 'Browse portfolios', 'url' => '#curated-grid'],
                ['label' => 'Join the index', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function footer(): CharacterPortfolioIndexScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Index & Co.',
            'items' => [
                ['label' => 'Portfolios', 'url' => '#curated-grid'],
                ['label' => 'Categories', 'url' => '#category-tabs'],
                ['label' => 'Standouts', 'url' => '#standout-notes'],
                ['label' => 'Creators', 'url' => '#creator-summary'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'character-portfolio-index-landing-page' => 'Theme Character Portfolio Index landing page',
            'character-portfolio-index-list-page' => 'Theme Character Portfolio Index listing',
            'character-portfolio-index-search-results' => 'Theme Character Portfolio Index search results',
            'character-portfolio-index-contact-form' => 'Theme Character Portfolio Index contact form',
            default => 'Theme Character Portfolio Index homepage',
        };
    }
}
