<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ExperimentalDirectory\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class ExperimentalDirectoryScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-experimental-directory::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (ExperimentalDirectoryScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-experimental-directory::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#050505',
                accentColor: '#d44a1f',
                neutralColor: '#55524d',
                headingFont: 'condensed',
                bodyFont: 'system',
                spacing: 'balanced',
                cardStyle: 'sharp',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'feature-slab',
                radius: 'none',
                surfaceColor: '#eee9df',
                foregroundColor: '#050505',
                headingScale: 'balanced',
                cardDensity: 'dense',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'experimental-directory',
        ])->render();

        return view('capell-theme-experimental-directory::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, ExperimentalDirectoryScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'experimental-directory-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('featured-today'),
                $this->section('metadata-filters'),
                $this->section('latest-submissions'),
                $this->section('winners-collections'),
                $this->section('profiles-resources'),
                $this->section('sponsor-modules'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'experimental-directory-landing-page' => [
                $this->navigation(),
                $this->section('featured-today', [
                    'heading' => 'A project feature built for oversized titles and image slabs',
                    'summary' => 'A single project view pairs compact metadata, status badges, and credits so visitors can absorb the work without the theme owning project records.',
                ]),
                $this->section('winners-collections'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'experimental-directory-list-page' => [
                $this->navigation(),
                $this->section('latest-submissions', [
                    'heading' => 'An archive of submissions built to be scanned',
                    'summary' => 'Tight grid rules keep latest submissions, winners, and collections legible without the theme owning entry records.',
                ]),
                $this->section('metadata-filters'),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'experimental-directory-search-results' => [
                $this->navigation(),
                $this->section('metadata-filters', [
                    'heading' => 'Discovery across projects, studios, and categories',
                    'summary' => 'Compact metadata filters keep search results across statuses, mediums, and countries fast and legible.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'experimental-directory-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Reach the directory through one confident path',
                    'summary' => 'A non-submitting newsletter and contact CTA proves the conversion journey feels like part of the directory experience.',
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
    private function section(string $sectionKey, array $data = []): ExperimentalDirectoryScreenshotSection
    {
        return new ExperimentalDirectoryScreenshotSection($sectionKey, $data);
    }

    private function navigation(): ExperimentalDirectoryScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Index of Practice',
            'items' => [
                ['label' => 'Submissions', 'url' => '#latest-submissions'],
                ['label' => 'Winners', 'url' => '#winners-collections'],
                ['label' => 'Collections', 'url' => '#winners-collections'],
                ['label' => 'Profiles', 'url' => '#profiles-resources'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): ExperimentalDirectoryScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A directory creative work can stand behind',
            'eyebrow' => 'Experimental Directory',
            'summary' => 'A pale, grid-ruled directory homepage for featured projects, latest submissions, winners, collections, profiles, resources, and sponsor modules.',
            'actions' => [
                ['label' => 'Browse submissions', 'url' => '#latest-submissions'],
                ['label' => 'View winners', 'url' => '#winners-collections'],
            ],
        ]);
    }

    private function footer(): ExperimentalDirectoryScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Index of Practice',
            'items' => [
                ['label' => 'Submissions', 'url' => '#latest-submissions'],
                ['label' => 'Winners', 'url' => '#winners-collections'],
                ['label' => 'Profiles', 'url' => '#profiles-resources'],
                ['label' => 'Newsletter', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'experimental-directory-landing-page' => 'Theme Experimental Directory feature',
            'experimental-directory-list-page' => 'Theme Experimental Directory archive',
            'experimental-directory-search-results' => 'Theme Experimental Directory search results',
            'experimental-directory-contact-form' => 'Theme Experimental Directory contact form',
            default => 'Theme Experimental Directory homepage',
        };
    }
}
