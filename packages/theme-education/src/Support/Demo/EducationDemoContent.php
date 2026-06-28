<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Education\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Education theme.
 *
 * Copy and section payloads are mined verbatim from the theme's screenshot
 * renderer (EducationScreenshotRenderer) and its section Blade views, so each
 * seeded surface emits the theme's signature course-first renderers
 * (course-catalog / pathway-comparison / outcomes / instructors / events /
 * enrolment-cta / faculty-directory / admissions-funnel / content-listing)
 * alongside the standard hero/proof/cta — a full learner-journey site per
 * surface rather than the shared five-section skeleton.
 */
final class EducationDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Northgate Academy';

    /**
     * @return array<int, ThemeDemoPageDefinition>
     */
    public function definitions(string $themeKey, string $themeName, string $baseUrl): array
    {
        $media = ThemeDemoMedia::groupedForTheme($themeKey);

        return [
            $this->homepage($themeKey, $media),
            $this->directory($themeKey, $media),
            $this->detail($themeKey, $media),
            $this->contact($themeKey, $media),
            $this->empty($themeKey, $media),
            $this->notFound($themeKey, $media),
            $this->cta($themeKey, $media),
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function homepage(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'homepage',
            name: self::BRAND . ' Home',
            title: self::BRAND . ' — Course & School Theme',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A learning journey course providers can stand behind',
                'A course-first homepage for programme discovery, faculty trust, open days, and enrolment-led learner journeys.',
            ),
            renderData: [
                'summary' => 'A course-first homepage for programme discovery, faculty trust, open days, and enrolment-led learner journeys.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Education',
                        'heading' => 'A learning journey course providers can stand behind',
                        'summary' => 'A course-first homepage for programme discovery, faculty trust, open days, and enrolment-led learner journeys.',
                        'actions' => [
                            ['label' => 'Browse courses', 'url' => '#courses', 'style' => 'primary'],
                            ['label' => 'Start enrolment', 'url' => '#enrol', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Northgate Academy learners on campus',
                    ],
                    $this->courseCatalogSection(
                        heading: 'A catalogue built to compare learning paths',
                    ),
                    $this->pathwayComparisonSection(
                        heading: 'Learning pathways that build on each other',
                        summary: 'Three structured pathways take a learner from first principles to a portfolio they can stand behind.',
                    ),
                    $this->outcomesSection(
                        heading: 'Outcomes learners and employers recognise',
                        summary: 'Every programme is built backwards from the result a learner needs on the other side.',
                    ),
                    $this->instructorsSection(
                        heading: 'Meet the instructors and mentors behind the programme',
                    ),
                    $this->eventsSection(
                        heading: 'Open days and workshops worth turning up for',
                    ),
                    $this->enrolmentCtaSection(
                        heading: 'Turn course interest into a confident application',
                    ),
                    $this->proofSection(
                        heading: 'Proof from the cohorts so far',
                        summary: 'Outcomes from the last two years of teaching across the academy.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to start your learning journey?',
                        summary: 'Browse the catalogue, book an open day, or begin an application — every path leads back to a real person on the team.',
                    ),
                ],
            ],
            type: PageTypeEnum::Home,
            layout: LayoutEnum::Home,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function directory(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'directory',
            name: self::BRAND . ' Courses',
            title: 'Courses — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A catalogue built to compare learning paths',
                'Structured course cards let learners weigh programmes without the page feeling like a plain resource grid.',
            ),
            renderData: [
                'summary' => 'Structured course cards let learners weigh programmes without the page feeling like a plain resource grid.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Courses',
                        'heading' => 'A catalogue built to compare learning paths',
                        'summary' => 'Structured course cards let learners weigh programmes without the page feeling like a plain resource grid.',
                        'actions' => [
                            ['label' => 'Start enrolment', 'url' => '#enrol', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Northgate Academy course studios',
                    ],
                    $this->courseCatalogSection(
                        heading: 'A catalogue built to compare learning paths',
                    ),
                    $this->pathwayComparisonSection(
                        heading: 'Learning pathways that build on each other',
                        summary: 'Three structured pathways take a learner from first principles to a portfolio they can stand behind.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the catalogue',
                        summary: 'Short courses, workshops, and resources alongside the flagship pathways.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'See a course that fits?',
                        summary: 'Tell us where you are starting from and we will point you to the right pathway.',
                    ),
                ],
            ],
            layout: LayoutEnum::Results,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function detail(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Course',
            title: 'Product Design Diploma — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Product Design Diploma',
                'A twelve-week, mentor-led pathway from research to a shipped portfolio piece.',
            ),
            renderData: [
                'summary' => 'A twelve-week, mentor-led pathway from research to a shipped portfolio piece.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Course',
                        'heading' => 'Product Design Diploma',
                        'summary' => 'A twelve-week, mentor-led pathway from research to a shipped portfolio piece — built backwards from the role a learner wants next.',
                        'actions' => [
                            ['label' => 'Start enrolment', 'url' => '#enrol', 'style' => 'primary'],
                            ['label' => 'Browse courses', 'url' => '#courses', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Product Design Diploma studio session',
                    ],
                    $this->outcomesSection(
                        heading: 'What you will be able to do',
                        summary: 'Every module ends in evidence you can show an employer, not just a certificate.',
                    ),
                    $this->instructorsSection(
                        heading: 'Your instructors and mentors',
                    ),
                    $this->admissionsFunnelSection(
                        heading: 'How enrolment works',
                        summary: 'Four steps from first enquiry to your first day in the cohort.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to join this cohort?',
                        summary: 'Applications for the next intake are open. Start yours in a few minutes.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function contact(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'contact',
            name: self::BRAND . ' Enrol',
            title: 'Enrol — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Turn course interest into a confident application',
                'A guided enrolment journey that feels like part of the learning experience.',
            ),
            renderData: [
                'summary' => 'A guided enrolment journey that feels like part of the learning experience, with a real person at the end of it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Enrol',
                        'heading' => 'Turn course interest into a confident application',
                        'summary' => 'Tell us which pathway fits and we will guide you through enrolment. Email admissions@northgate.example or use the steps below — we reply within one working day.',
                        'actions' => [
                            ['label' => 'Email admissions', 'url' => 'mailto:admissions@northgate.example', 'style' => 'primary'],
                            ['label' => 'Browse courses', 'url' => '#courses', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Northgate Academy admissions team',
                    ],
                    $this->enrolmentCtaSection(
                        heading: 'Turn course interest into a confident application',
                    ),
                    $this->admissionsFunnelSection(
                        heading: 'How enrolment works',
                        summary: 'Four steps from first enquiry to your first day in the cohort.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Book an open day or a call with admissions and we will help you choose the right pathway.',
                    ),
                ],
            ],
            layout: LayoutEnum::System,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function empty(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'empty',
            name: self::BRAND . ' No Courses',
            title: 'No courses found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No courses match that filter yet',
                'A graceful empty state for a filtered course catalogue with no matching programmes.',
            ),
            renderData: [
                'summary' => 'No courses match that filter yet — but the academy can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Course catalogue',
                        'heading' => 'No courses match that filter — yet',
                        'summary' => 'We have not published a programme in this category. Clear the filter to see everything, or tell us what you want to learn.',
                        'actions' => [
                            ['label' => 'Browse all courses', 'url' => '#courses', 'style' => 'primary'],
                            ['label' => 'Start enrolment', 'url' => '#enrol', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'course-catalog',
                        'heading' => 'Nothing in this category yet',
                        'items' => [],
                    ],
                    $this->pathwayComparisonSection(
                        heading: 'Learning pathways that build on each other',
                        summary: 'While you are here, the three pathways the academy is best known for.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific subject?',
                        summary: 'Tell us what you want to study and we will send the closest pathway from the catalogue.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function notFound(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'not-found',
            name: self::BRAND . ' 404',
            title: 'Page not found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'Page not found',
                'A not-found page that routes visitors back into the course catalogue and enrolment paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back to the catalogue.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page is no longer on the syllabus',
                        'summary' => 'The link is broken or the page has moved. Head back to the courses, or start a conversation with admissions.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Browse courses', 'url' => '#courses', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will point you to the right place.',
                    ),
                ],
            ],
            type: PageTypeEnum::NotFound,
            layout: LayoutEnum::System,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function cta(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'cta',
            name: self::BRAND . ' Apply',
            title: 'Apply now — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to start your learning journey?',
                'A focused conversion page inviting new enrolments.',
            ),
            renderData: [
                'summary' => 'Ready to start your learning journey? Begin an application with the academy.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Enrol',
                        'heading' => 'Ready to start your learning journey?',
                        'summary' => 'Whether it is a full diploma or a single workshop, you get the same mentors and the same standard.',
                        'actions' => [
                            ['label' => 'Start enrolment', 'url' => '#enrol', 'style' => 'primary'],
                            ['label' => 'Email admissions', 'url' => 'mailto:admissions@northgate.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Northgate Academy graduation',
                    ],
                    $this->proofSection(
                        heading: 'Why learners choose Northgate',
                        summary: 'The numbers behind the cohorts.',
                    ),
                    $this->enrolmentCtaSection(
                        heading: 'Turn course interest into a confident application',
                    ),
                    $this->ctaSection(
                        heading: 'One application away',
                        summary: 'Send your application over and admissions will come back within one working day with next steps.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function courseCatalogSection(string $heading): array
    {
        return [
            'type' => 'course-catalog',
            'heading' => $heading,
            'items' => [
                [
                    'format' => 'Diploma',
                    'title' => 'Product Design Diploma',
                    'summary' => 'Twelve weeks from user research to a shipped portfolio piece, mentored end to end.',
                    'url' => '#course-product-design',
                ],
                [
                    'format' => 'Certificate',
                    'title' => 'Front-End Engineering',
                    'summary' => 'Build production interfaces with a design system, accessibility, and a real deploy.',
                    'url' => '#course-frontend',
                ],
                [
                    'format' => 'Short course',
                    'title' => 'Data Foundations',
                    'summary' => 'Six evenings on the data literacy every modern team is now expected to share.',
                    'url' => '#course-data',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pathwayComparisonSection(string $heading, string $summary): array
    {
        return [
            'type' => 'pathway-comparison',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'type' => 'Foundation',
                    'title' => 'Explore',
                    'summary' => 'Short courses and workshops to test a subject before you commit to a full pathway.',
                ],
                [
                    'type' => 'Core',
                    'title' => 'Build',
                    'summary' => 'Mentor-led certificates that take you from fundamentals to a working portfolio.',
                ],
                [
                    'type' => 'Advanced',
                    'title' => 'Specialise',
                    'summary' => 'Diplomas with a capstone project assessed by practitioners in the field.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function outcomesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'outcomes',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'metric' => '86%',
                    'title' => 'In work within six months',
                    'summary' => 'Graduates moving into a related role or promotion within half a year of finishing.',
                ],
                [
                    'metric' => '1:8',
                    'title' => 'Mentor to learner ratio',
                    'summary' => 'Small cohorts mean feedback on your actual work, every week.',
                ],
                [
                    'metric' => '40+',
                    'title' => 'Hiring partners',
                    'summary' => 'Employers who interview our graduates directly from the showcase.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function instructorsSection(string $heading): array
    {
        return [
            'type' => 'instructors',
            'heading' => $heading,
            'summary' => 'Editorial instructor cards keep the teaching team credible and legible without the theme owning people records.',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventsSection(string $heading): array
    {
        return [
            'type' => 'events',
            'heading' => $heading,
            'items' => [
                [
                    'signal' => 'Open day',
                    'title' => 'Autumn open evening',
                    'summary' => 'Tour the studios, meet the mentors, and sit in on a live critique.',
                    'date' => 'Thu 18 Sep',
                    'url' => '#event-open-day',
                ],
                [
                    'signal' => 'Workshop',
                    'title' => 'Portfolio clinic',
                    'summary' => 'Bring work in progress and get honest feedback from a hiring partner.',
                    'date' => 'Sat 27 Sep',
                    'url' => '#event-portfolio',
                ],
                [
                    'signal' => 'Deadline',
                    'title' => 'Winter cohort applications close',
                    'summary' => 'Last day to apply for the January intake across all pathways.',
                    'date' => 'Fri 31 Oct',
                    'url' => '#event-deadline',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function enrolmentCtaSection(string $heading): array
    {
        return [
            'type' => 'enrolment-cta',
            'heading' => $heading,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function admissionsFunnelSection(string $heading, string $summary): array
    {
        return [
            'type' => 'admissions-funnel',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'step' => '01',
                    'title' => 'Enquire',
                    'summary' => 'Tell us which pathway fits and where you are starting from.',
                ],
                [
                    'step' => '02',
                    'title' => 'Apply',
                    'summary' => 'A short application and, for some pathways, a portfolio or task.',
                ],
                [
                    'step' => '03',
                    'title' => 'Interview',
                    'summary' => 'A friendly conversation with a mentor to confirm the fit both ways.',
                ],
                [
                    'step' => '04',
                    'title' => 'Enrol',
                    'summary' => 'Confirm your place, settle fees or finance, and meet your cohort.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['metric' => '86%', 'name' => 'In work within six months', 'summary' => 'Graduates moving into a related role or promotion within half a year of finishing.'],
                ['metric' => '40+', 'name' => 'Hiring partners', 'summary' => 'Employers who interview our graduates directly from the end-of-cohort showcase.'],
                ['metric' => '1 day', 'name' => 'Reply to every enquiry', 'summary' => 'You hear back from a real person in admissions, fast.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Intro to UX Research', 'type' => 'Short course', 'summary' => 'A weekend primer on talking to users and turning notes into decisions.'],
            ['title' => 'Accessibility Essentials', 'type' => 'Workshop', 'summary' => 'Ship interfaces that work for everyone, with a practical audit you can reuse.'],
            ['title' => 'Career Switcher Guide', 'type' => 'Resource', 'summary' => 'How learners moved into design and engineering from unrelated careers.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#resource-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Start enrolment', 'url' => '#enrol', 'style' => 'primary'],
                ['label' => 'Browse courses', 'url' => '#courses', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brandName' => self::BRAND,
            'items' => [
                ['label' => 'Courses', 'url' => '#courses'],
                ['label' => 'Instructors', 'url' => '#instructors'],
                ['label' => 'Open days', 'url' => '#events'],
                ['label' => 'Enrol', 'url' => '#enrol'],
            ],
            'ctaLabel' => 'Enrol now',
            'ctaUrl' => '#enrol',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
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
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
