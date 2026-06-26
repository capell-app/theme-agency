<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MotionArchive\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class MotionArchiveScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-motion-archive::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (MotionArchiveScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-motion-archive::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#050505',
                accentColor: '#ff5c35',
                neutralColor: '#626262',
                headingFont: 'condensed',
                bodyFont: 'system',
                spacing: 'balanced',
                cardStyle: 'sharp',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'motion-preview',
                radius: 'none',
                surfaceColor: '#d8d8d4',
                foregroundColor: '#050505',
                headingScale: 'balanced',
                cardDensity: 'dense',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'motion-archive',
        ])->render();

        return view('capell-theme-motion-archive::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, MotionArchiveScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'motion-archive-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('date-filter-rail'),
                $this->section('winner-list'),
                $this->section('featured-project'),
                $this->section('jury-score-explainer'),
                $this->section('media-credits'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'motion-archive-landing-page' => [
                $this->navigation(),
                $this->section('featured-project', [
                    'heading' => 'A project record that reads like an archive entry',
                    'summary' => 'Synopsis, credits, technology stack, and awards stay structured so a single project feels like part of the historical record.',
                ]),
                $this->section('media-credits'),
                $this->section('jury-score-explainer'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'motion-archive-list-page' => [
                $this->navigation(),
                $this->section('archive-hero', [
                    'heading' => 'The winner archive built to be scanned by year',
                    'summary' => 'A date-led archive keeps years, months, winners, and categories legible without the theme owning award records.',
                ]),
                $this->section('date-filter-rail'),
                $this->section('winner-list'),
                $this->section('content-listing'),
                $this->footer(),
            ],
            'motion-archive-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search the archive across winners and projects',
                    'summary' => 'A structured results view keeps winners, credits, technology labels, and archive periods scannable while the theme stays content-light.',
                ]),
                $this->section('winner-list'),
                $this->footer(),
            ],
            'motion-archive-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Reach the archive through one restrained path',
                    'summary' => 'A non-submitting contact path proves newsletter signups, submissions, and inquiries feel like part of the archive experience.',
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
    private function section(string $sectionKey, array $data = []): MotionArchiveScreenshotSection
    {
        return new MotionArchiveScreenshotSection($sectionKey, $data);
    }

    private function navigation(): MotionArchiveScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Frame Index',
            'items' => [
                ['label' => 'Archive', 'url' => '#archive'],
                ['label' => 'Winners', 'url' => '#winners'],
                ['label' => 'Jury scores', 'url' => '#jury-scores'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'consultationUrl' => '#contact',
        ]);
    }

    private function hero(): MotionArchiveScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'An archive every motion project can be filed in',
            'eyebrow' => 'Motion Archive',
            'summary' => 'An industrial digital-awards homepage for date filters, winner lists, featured projects, jury scores, media credits, and archive journeys.',
            'actions' => [
                ['label' => 'Browse the archive', 'url' => '#archive'],
                ['label' => 'View winners', 'url' => '#winners'],
            ],
        ]);
    }

    private function footer(): MotionArchiveScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Frame Index',
            'items' => [
                ['label' => 'Archive', 'url' => '#archive'],
                ['label' => 'Winners', 'url' => '#winners'],
                ['label' => 'Jury scores', 'url' => '#jury-scores'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'motion-archive-landing-page' => 'Theme Motion Archive feature page',
            'motion-archive-list-page' => 'Theme Motion Archive winner archive',
            'motion-archive-search-results' => 'Theme Motion Archive search results',
            'motion-archive-contact-form' => 'Theme Motion Archive contact form',
            default => 'Theme Motion Archive homepage',
        };
    }
}
