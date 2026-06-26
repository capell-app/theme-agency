<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Education\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class EducationScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-education::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (EducationScreenshotSection $section): string => $viewFactory->make(
                $this->viewFor($section->key()),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-education::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#4338ca',
                accentColor: '#14b8a6',
                neutralColor: '#111827',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#f8fbff',
                foregroundColor: '#111827',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'education',
        ])->render();

        return view('capell-theme-education::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, EducationScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'frontend-page-rendered-with-education-theme', 'education-homepage-layout' => [
                $this->navigation(),
                $this->hero(),
                $this->section('course-catalog'),
                $this->section('pathway-comparison'),
                $this->section('outcomes'),
                $this->section('instructors'),
                $this->section('events'),
                $this->section('enrolment-cta'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'education-course-catalogue-layout' => [
                $this->navigation(),
                $this->section('course-catalog', [
                    'heading' => 'A catalogue built to compare learning paths',
                    'summary' => 'Structured course cards let learners weigh programmes without the page feeling like a plain resource grid.',
                ]),
                $this->section('pathway-comparison'),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'education-instructors-layout' => [
                $this->navigation(),
                $this->section('instructors', [
                    'heading' => 'Meet the instructors and mentors behind the programme',
                    'summary' => 'Editorial instructor cards keep the teaching team credible and legible without the theme owning people records.',
                ]),
                $this->section('faculty-directory'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'education-events-layout' => [
                $this->navigation(),
                $this->section('events', [
                    'heading' => 'Open days and workshops worth turning up for',
                    'summary' => 'A structured events composition promotes open days, workshops, and cohort deadlines without custom Blade.',
                ]),
                $this->section('outcomes'),
                $this->section('cta'),
                $this->footer(),
            ],
            'education-enrolment-layout' => [
                $this->navigation(),
                $this->section('enrolment-cta', [
                    'heading' => 'Turn course interest into a confident application',
                    'summary' => 'A non-submitting enrolment CTA proves the application journey feels like part of the learning experience.',
                ]),
                $this->section('admissions-funnel'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'education-resource-library-layout' => [
                $this->navigation(),
                $this->section('resources', [
                    'heading' => 'Learning resources tied to the pathway',
                    'summary' => 'Support material stays anchored to learning pathways instead of drifting into a plain blog.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * Education renders navigation and footer from the shared foundation theme
     * rather than from local section views, so those two keys resolve to the
     * foundation namespace while every other section stays theme-local.
     */
    private function viewFor(string $sectionKey): string
    {
        if ($sectionKey === 'navigation' || $sectionKey === 'footer') {
            return 'capell-foundation-theme::theme.sections.' . $sectionKey;
        }

        return self::VIEW_PREFIX . $sectionKey;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): EducationScreenshotSection
    {
        return new EducationScreenshotSection($sectionKey, $data);
    }

    private function navigation(): EducationScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Northgate Academy',
            'items' => [
                ['label' => 'Courses', 'url' => '#courses'],
                ['label' => 'Instructors', 'url' => '#instructors'],
                ['label' => 'Open days', 'url' => '#events'],
                ['label' => 'Enrol', 'url' => '#enrol'],
            ],
            'ctaLabel' => 'Enrol now',
            'ctaUrl' => '#enrol',
        ]);
    }

    private function hero(): EducationScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A learning journey course providers can stand behind',
            'eyebrow' => 'Education',
            'summary' => 'A course-first homepage for programme discovery, faculty trust, open days, and enrolment-led learner journeys.',
            'actions' => [
                ['label' => 'Browse courses', 'url' => '#courses'],
                ['label' => 'Start enrolment', 'url' => '#enrol'],
            ],
        ]);
    }

    private function footer(): EducationScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Northgate Academy',
            'summary' => 'Course discovery, faculty trust, and enrolment journeys in one accessible learning theme.',
            'columns' => [
                [
                    'heading' => 'Study',
                    'links' => [
                        ['label' => 'Courses', 'url' => '#courses'],
                        ['label' => 'Pathways', 'url' => '#pathways'],
                    ],
                ],
                [
                    'heading' => 'About',
                    'links' => [
                        ['label' => 'Instructors', 'url' => '#instructors'],
                        ['label' => 'Outcomes', 'url' => '#outcomes'],
                    ],
                ],
                [
                    'heading' => 'Enrol',
                    'links' => [
                        ['label' => 'Open days', 'url' => '#events'],
                        ['label' => 'Start enrolment', 'url' => '#enrol'],
                    ],
                ],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'education-course-catalogue-layout' => 'Theme Education course catalogue',
            'education-instructors-layout' => 'Theme Education instructors',
            'education-events-layout' => 'Theme Education open days',
            'education-enrolment-layout' => 'Theme Education enrolment',
            'education-resource-library-layout' => 'Theme Education learning resources',
            default => 'Theme Education homepage',
        };
    }
}
