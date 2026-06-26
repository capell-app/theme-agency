<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CaseStudyPlatform\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class CaseStudyPlatformScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-case-study-platform::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (CaseStudyPlatformScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-case-study-platform::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#1769ff',
                neutralColor: '#111827',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#f4f4f1',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'case-study-platform',
        ])->render();

        return view('capell-theme-case-study-platform::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, CaseStudyPlatformScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'case-study-platform-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('discipline-filters'),
                $this->section('project-feed'),
                $this->section('process-notes'),
                $this->section('related-projects'),
                $this->section('credits-tools'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'case-study-platform-landing-page' => [
                $this->navigation(),
                $this->section('creator-hero', [
                    'heading' => 'A featured case study that reads like a story',
                    'summary' => 'A long media stack pairs a creator profile with process notes, credits, and tools so the work stands on its own.',
                ]),
                $this->section('process-notes'),
                $this->section('credits-tools'),
                $this->section('related-projects'),
                $this->section('cta'),
                $this->footer(),
            ],
            'case-study-platform-list-page' => [
                $this->navigation(),
                $this->section('project-feed', [
                    'heading' => 'A project feed built to be scanned',
                    'summary' => 'Editorial project cards keep disciplines, creators, and appreciation metrics legible without the theme owning project records.',
                ]),
                $this->section('discipline-filters'),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'case-study-platform-search-results' => [
                $this->navigation(),
                $this->section('discipline-filters', [
                    'heading' => 'Discovery that stays fast and legible',
                    'summary' => 'Search across projects, creators, studios, technologies, and disciplines while the layout keeps results structured.',
                ]),
                $this->section('project-feed'),
                $this->section('content-listing'),
                $this->footer(),
            ],
            'case-study-platform-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'One confident path to get in touch',
                    'summary' => 'A non-submitting conversion form proves newsletter, submission, and hiring journeys feel like part of the platform experience.',
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
    private function section(string $sectionKey, array $data = []): CaseStudyPlatformScreenshotSection
    {
        return new CaseStudyPlatformScreenshotSection($sectionKey, $data);
    }

    private function navigation(): CaseStudyPlatformScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Studio Index',
            'items' => [
                ['label' => 'Projects', 'url' => '#project-feed'],
                ['label' => 'Disciplines', 'url' => '#discipline-filters'],
                ['label' => 'Creators', 'url' => '#creator-hero'],
                ['label' => 'Hiring', 'url' => '#newsletter'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): CaseStudyPlatformScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Creative case studies a platform can stand behind',
            'eyebrow' => 'Case Study Platform',
            'summary' => 'An editorial creative homepage for project feeds, discipline filters, creator profiles, credits, tools, process notes, and hiring paths.',
            'actions' => [
                ['label' => 'Browse projects', 'url' => '#project-feed'],
                ['label' => 'Explore disciplines', 'url' => '#discipline-filters'],
            ],
        ]);
    }

    private function footer(): CaseStudyPlatformScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Studio Index',
            'items' => [
                ['label' => 'Projects', 'url' => '#project-feed'],
                ['label' => 'Disciplines', 'url' => '#discipline-filters'],
                ['label' => 'Creators', 'url' => '#creator-hero'],
                ['label' => 'Hiring', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'case-study-platform-landing-page' => 'Theme Case Study Platform landing page',
            'case-study-platform-list-page' => 'Theme Case Study Platform list page',
            'case-study-platform-search-results' => 'Theme Case Study Platform search results',
            'case-study-platform-contact-form' => 'Theme Case Study Platform contact form',
            default => 'Theme Case Study Platform homepage',
        };
    }
}
