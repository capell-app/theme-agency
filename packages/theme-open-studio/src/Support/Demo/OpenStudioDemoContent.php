<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OpenStudio\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Open Studio theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature project-showcase renderers
 * (discipline filters, project feed, process notes, credits & tools, creator
 * hero, related projects, newsletter) alongside the standard hero/proof/cta —
 * giving every surface a full, individual creative case-study directory
 * rather than the shared five-section skeleton. Every card-bearing section
 * carries real cover photography via ThemeDemoMedia, and every action url is
 * traced against the sections that actually render on that surface.
 */
final class OpenStudioDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Studio Index';

    private const string DESK_EMAIL = 'studio@studioindex.example';

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
            title: self::BRAND . ' — Creative Case Study Directory',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A directory of creative case studies',
                'Studio Index curates project case studies across product, brand, motion, and engineering — with the creators, credits, and tools behind every build.',
            ),
            renderData: [
                'summary' => 'Studio Index is an editorial directory of creative case studies. Browse projects by discipline, read the process behind each build, and find the creators who shipped it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Creative case study directory',
                        'kicker' => 'Creative case study directory',
                        'heading' => 'Case studies worth studying, from the people who shipped them',
                        'summary' => 'Studio Index pairs each project with its process notes, credits, and toolchain — so every case study reads like the real account of how the work got made.',
                        'primary_label' => 'Browse projects',
                        'primary_url' => '#project-feed',
                        'secondary_label' => 'Explore disciplines',
                        'secondary_url' => '#discipline-filters',
                        'actions' => [
                            ['label' => 'Browse projects', 'url' => '#project-feed', 'style' => 'primary'],
                            ['label' => 'Explore disciplines', 'url' => '#discipline-filters', 'style' => 'secondary'],
                        ],
                        'notes' => [
                            'Featured: Atlas Ledger — a fintech onboarding rebuild',
                            'Every project carries credits, tools, and timelines',
                            'New case studies land in the directory each week',
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Studio Index featured case study cover',
                    ],
                    $this->disciplineFiltersSection(
                        heading: 'Browse by discipline',
                        summary: 'Filter the directory to the kind of work you came to study.',
                    ),
                    $this->projectFeedSection(
                        heading: 'Fresh case studies',
                        summary: 'The latest projects added to the directory, newest first.',
                        media: $media,
                    ),
                    $this->processNotesSection(
                        heading: 'How the work actually got made',
                        summary: 'Each case study opens up its process — the constraints, the calls, and the trade-offs.',
                        media: $media,
                        url: '#project-feed',
                        label: 'Browse the projects behind these notes',
                    ),
                    $this->disciplineCarouselBrowseSection(
                        heading: 'Browse the whole directory by discipline',
                        summary: 'A sticky filter for scanning every case study one discipline at a time.',
                        media: $media,
                    ),
                    $this->relatedProjectsSection(
                        heading: 'Related projects to study next',
                        summary: 'Builds that share a discipline, a constraint, or a creator with this week\'s feature.',
                        media: $media,
                    ),
                    $this->creditsToolsSection(
                        heading: 'Credits & tools behind the builds',
                        summary: 'Who shipped it and what they reached for — credited in full on every case study.',
                    ),
                    $this->proofSection(
                        heading: 'A directory creators trust',
                    ),
                    $this->newsletterSection(
                        heading: 'Get the week\'s best case studies',
                        summary: 'One email a week: the standout projects, the process notes worth reading, and who is hiring.',
                    ),
                    $this->ctaSection(
                        heading: 'Have a project worth documenting?',
                        summary: 'Submit your case study and we will help you tell the real story behind the build.',
                        actions: [
                            ['label' => 'Submit a case study', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'Browse projects', 'url' => '#project-feed', 'style' => 'secondary'],
                        ],
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
            name: self::BRAND . ' Projects',
            title: 'Projects — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every case study in the directory',
                'Browse the full Studio Index archive of creative case studies across product, brand, motion, and engineering.',
            ),
            renderData: [
                'summary' => 'The full project archive — scan editorial case-study cards by discipline, creator, and the metrics that mattered.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Project directory',
                        'kicker' => 'Project directory',
                        'heading' => 'A project feed built to be scanned',
                        'summary' => 'Editorial case-study cards keep disciplines, creators, and appreciation metrics legible — so you find the build you want to study without digging.',
                        'primary_label' => 'Submit a case study',
                        'primary_url' => '#newsletter',
                        'secondary_label' => 'Filter by discipline',
                        'secondary_url' => '#discipline-filters',
                        'actions' => [
                            ['label' => 'Submit a case study', 'url' => '#newsletter', 'style' => 'primary'],
                            ['label' => 'Filter by discipline', 'url' => '#discipline-filters', 'style' => 'secondary'],
                        ],
                        'notes' => [
                            '240+ case studies in the directory',
                            'Filter by discipline, creator, or tool',
                            'Sorted by what the community is studying now',
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Studio Index project directory cover',
                    ],
                    $this->disciplineFiltersSection(
                        heading: 'Filter the directory',
                        summary: 'Narrow the archive to a single discipline before you start reading.',
                    ),
                    $this->projectFeedSection(
                        heading: 'All case studies',
                        summary: 'Every project in the archive, with creators and outcomes on each card.',
                        media: $media,
                        itemUrl: '#content-listing',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific kind of build?',
                        summary: 'Tell us the discipline or constraint and we will surface the most relevant case studies.',
                        actions: [
                            ['label' => 'Submit a case study', 'url' => '#newsletter', 'style' => 'primary'],
                        ],
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
            name: self::BRAND . ' Case Study',
            title: 'Atlas Ledger — Case Study — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Atlas Ledger — rebuilding fintech onboarding',
                'A long-form case study on how the Atlas Ledger team cut onboarding drop-off in half, with the process notes, credits, and tools behind it.',
            ),
            renderData: [
                'summary' => 'How the Atlas Ledger team rebuilt fintech onboarding and halved drop-off — the full process, credits, and toolchain.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Case study',
                        'kicker' => 'Case study',
                        'heading' => 'Atlas Ledger — rebuilding fintech onboarding',
                        'summary' => 'A two-quarter rebuild that took account opening from a nine-screen slog to a four-step flow, told by the team that shipped it.',
                        'primary_label' => 'Read the process',
                        'primary_url' => '#process-notes',
                        'secondary_label' => 'View related projects',
                        'secondary_url' => '#related-projects',
                        'actions' => [
                            ['label' => 'Read the process', 'url' => '#process-notes', 'style' => 'primary'],
                            ['label' => 'View related projects', 'url' => '#related-projects', 'style' => 'secondary'],
                        ],
                        'notes' => [
                            'Discipline: Product & UX',
                            'Team: Atlas Ledger design + engineering',
                            'Timeline: Q1–Q2 2025',
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Atlas Ledger onboarding case study cover',
                    ],
                    $this->filmstripProjectShowcaseSection(
                        heading: 'Scrub through the Atlas Ledger rebuild',
                        summary: 'Drag the scrub bar or pick a thumbnail to move through the build sequentially, from the original nine-screen flow to the shipped four-step version.',
                        media: $media,
                    ),
                    $this->creatorHeroSection(
                        heading: 'The team behind Atlas Ledger',
                        summary: 'A four-person crew owned the rebuild end to end — research, design, and the front-end build.',
                        media: $media,
                        mediaKey: 'proof',
                    ),
                    $this->processNotesSection(
                        heading: 'Inside the onboarding rebuild',
                        summary: 'The constraints they started with, and the calls that got drop-off down.',
                        media: $media,
                        url: '#credits-tools',
                        label: 'See who shipped it',
                    ),
                    $this->processNotesTimelineSection(
                        heading: 'The build, one decision at a time',
                        summary: 'A chronological spine of the calls the team made, from constraint to shipped result.',
                    ),
                    $this->creditsToolsSection(
                        heading: 'Credits & tools',
                        summary: 'Everyone who shipped Atlas Ledger, and the stack they built it on.',
                    ),
                    $this->creditsGridRosterSection(
                        heading: 'Everyone who shipped this build',
                        summary: 'The full roster behind Atlas Ledger, credited in full.',
                        media: $media,
                    ),
                    $this->relatedProjectsSection(
                        heading: 'Related case studies',
                        summary: 'Other onboarding and fintech rebuilds in the directory.',
                        media: $media,
                        itemUrl: '/',
                    ),
                    $this->ctaSection(
                        heading: 'Documenting a rebuild like this?',
                        summary: 'Submit your case study and reach a directory of creators who study the details.',
                        actions: [
                            ['label' => 'Submit a case study', 'url' => '#newsletter', 'style' => 'primary'],
                        ],
                    ),
                    $this->newsletterSection(
                        heading: 'Follow more builds like this',
                        summary: 'Get the next onboarding, brand, or systems case study the moment it is published.',
                    ),
                    $this->nextProjectCtaSection(
                        media: $media,
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
            name: self::BRAND . ' Submit',
            title: 'Submit a case study — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Submit a case study',
                'Pitch your project to Studio Index. Tell us what you built, who shipped it, and what you learned.',
            ),
            renderData: [
                'summary' => 'Pitch your project to Studio Index. One confident path to get your case study, your team, or your open roles in front of the directory.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get in touch',
                        'kicker' => 'Get in touch',
                        'heading' => 'One confident path to get in touch',
                        'summary' => 'Whether you are submitting a case study, listing a studio, or posting a role, it all starts here — and you hear back within two working days.',
                        'primary_label' => 'Email the desk',
                        'primary_url' => 'mailto:' . self::DESK_EMAIL,
                        'secondary_label' => 'Join the newsletter',
                        'secondary_url' => '#newsletter',
                        'actions' => [
                            ['label' => 'Email the desk', 'url' => 'mailto:' . self::DESK_EMAIL, 'style' => 'primary'],
                            ['label' => 'Join the newsletter', 'url' => '#newsletter', 'style' => 'secondary'],
                        ],
                        'notes' => [
                            'Submit a case study for the directory',
                            'List a studio or post a creative role',
                            'We reply within two working days',
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Submit to Studio Index',
                    ],
                    $this->newsletterSection(
                        heading: 'Send us your project',
                        summary: 'Drop your email and we will send the submission brief — what we need from you to feature the build.',
                    ),
                    $this->processNotesSection(
                        heading: 'What happens after you submit',
                        summary: 'Our editors review every pitch and work with you to shape the case study.',
                        media: $media,
                    ),
                    $this->proofSection(
                        heading: 'Submissions worth making',
                    ),
                    $this->ctaSection(
                        heading: 'Ready when you are',
                        summary: 'Send the project over and an editor will come back with the next step.',
                        actions: [
                            ['label' => 'Email the desk', 'url' => 'mailto:' . self::DESK_EMAIL, 'style' => 'primary'],
                        ],
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
            name: self::BRAND . ' No Results',
            title: 'No case studies match — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No case studies match that filter yet',
                'A graceful empty state for a filtered directory with no matching projects.',
            ),
            renderData: [
                'summary' => 'No case studies match that filter yet — but the directory can still point you somewhere worth reading.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Project directory',
                        'kicker' => 'Project directory',
                        'heading' => 'No case studies match that filter — yet',
                        'summary' => 'Nothing in the directory fits that combination of discipline and tool. Clear the filter to see everything, or browse a related discipline.',
                        'primary_label' => 'Clear filters',
                        'primary_url' => '#discipline-filters',
                        'secondary_label' => 'Browse disciplines',
                        'secondary_url' => '#discipline-filters',
                        'actions' => [
                            ['label' => 'Clear filters', 'url' => '#discipline-filters', 'style' => 'primary'],
                        ],
                        'notes' => [
                            'Try a broader discipline',
                            'Or drop one of the tool filters',
                            'New case studies arrive every week',
                        ],
                    ],
                    $this->disciplineFiltersSection(
                        heading: 'Try a different discipline',
                        summary: 'These categories all have case studies ready to read.',
                        itemUrl: '#creator-hero',
                    ),
                    $this->creatorHeroSection(
                        heading: 'Featured while you are here',
                        summary: 'A standout case study from the wider directory, in case it is what you were after.',
                        media: $media,
                        mediaKey: 'listing',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us the brief and we will surface the closest case studies in the archive.',
                        actions: [
                            ['label' => 'Browse disciplines', 'url' => '#discipline-filters', 'style' => 'primary'],
                        ],
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
                'A not-found page that routes visitors back into the case-study directory.',
            ),
            renderData: [
                'summary' => 'That case study has moved or never existed — here is the way back into the directory.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'kicker' => '404',
                        'heading' => 'This case study went unpublished',
                        'summary' => 'The link is broken or the project was pulled. Head back to the homepage, or browse by discipline to find what you were studying.',
                        'primary_label' => 'Back to home',
                        'primary_url' => '/',
                        'secondary_label' => 'Browse disciplines',
                        'secondary_url' => '#discipline-filters',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Browse disciplines', 'url' => '#discipline-filters', 'style' => 'secondary'],
                        ],
                        'notes' => [
                            'The directory is still right here',
                            'Browse by discipline to get back on track',
                        ],
                    ],
                    $this->disciplineFiltersSection(
                        heading: 'Pick up where you left off',
                        summary: 'Jump straight into a discipline and keep studying.',
                        itemUrl: '/',
                    ),
                    $this->ctaSection(
                        heading: 'Still hunting for a build?',
                        summary: 'Tell us what you needed and we will point you to the right case study.',
                        actions: [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                        ],
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
            name: self::BRAND . ' Submit Work',
            title: 'Feature your work — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Get your project in the directory',
                'A focused conversion page inviting creators to submit their case studies.',
            ),
            renderData: [
                'summary' => 'Get your project in front of a directory of creators who study the details. Submit your case study to Studio Index.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Feature your work',
                        'kicker' => 'Feature your work',
                        'heading' => 'Get your build in the directory',
                        'summary' => 'Studio Index puts your case study in front of the people who actually read the process notes — designers, engineers, and the teams hiring them.',
                        'primary_label' => 'Submit a case study',
                        'primary_url' => '#newsletter',
                        'secondary_label' => 'Join the newsletter',
                        'secondary_url' => '#newsletter',
                        'actions' => [
                            ['label' => 'Submit a case study', 'url' => '#newsletter', 'style' => 'primary'],
                        ],
                        'notes' => [
                            'Reach a directory of working creators',
                            'Editors help you shape the story',
                            'Featured builds get their own creator profile',
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Feature your work on Studio Index',
                    ],
                    $this->proofSection(
                        heading: 'Why creators submit to Studio Index',
                    ),
                    $this->newsletterSection(
                        heading: 'Start your submission',
                        summary: 'Leave your email and we will send the submission brief within the day.',
                    ),
                    $this->ctaSection(
                        heading: 'One project away',
                        summary: 'Send the build over and an editor will come back within two working days with next steps.',
                        actions: [
                            ['label' => 'Submit a case study', 'url' => '#newsletter', 'style' => 'primary'],
                        ],
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function disciplineFiltersSection(string $heading, string $summary, string $itemUrl = '#project-feed'): array
    {
        return [
            'type' => 'discipline-filters',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Product & UX', 'summary' => 'Onboarding, dashboards, and the flows that decide whether a product gets used.', 'url' => $itemUrl],
                ['title' => 'Brand & Identity', 'summary' => 'Rebrands, naming systems, and the visual work that signals a new chapter.', 'url' => $itemUrl],
                ['title' => 'Motion & Film', 'summary' => 'Launch films, product motion, and the toolkits that scale across channels.', 'url' => $itemUrl],
                ['title' => 'Engineering & Systems', 'summary' => 'Design systems, performance rebuilds, and the architecture under the surface.', 'url' => $itemUrl],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function filmstripProjectShowcaseSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['detail'], $media['hero'], $media['proof'])));

        $frames = [
            ['title' => 'The original flow', 'caption' => 'Nine screens, three of them redundant compliance checks.'],
            ['title' => 'Mapping the drop-off', 'caption' => 'Session recordings showed exactly where applicants gave up.'],
            ['title' => 'Collapsing the steps', 'caption' => 'Verification moved to the background; the flow fell to four steps.'],
            ['title' => 'The shipped version', 'caption' => 'A four-step account opening flow, live in production.'],
        ];

        $items = [];

        foreach ($frames as $index => $frame) {
            $items[] = [
                ...$frame,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $frame['title'],
            ];
        }

        return [
            'type' => 'filmstrip-project-showcase',
            'heading' => $heading,
            'summary' => $summary,
            'frames' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function disciplineCarouselBrowseSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['detail'])));

        $groups = [
            'Product & UX' => [
                ['title' => 'Atlas Ledger — onboarding rebuild', 'meta' => 'Product & UX'],
                ['title' => 'Harbour — KYC in three taps', 'meta' => 'Product & UX'],
            ],
            'Brand & Identity' => [
                ['title' => 'Verde — coffee brand system', 'meta' => 'Brand & Identity'],
                ['title' => 'Kindred — lending rebrand', 'meta' => 'Brand & Identity'],
            ],
            'Motion & Film' => [
                ['title' => 'Atlas Festival — launch film', 'meta' => 'Motion & Film'],
            ],
            'Engineering & Systems' => [
                ['title' => 'Northwind — field engineer UI', 'meta' => 'Engineering & Systems'],
                ['title' => 'Lumen — design system reset', 'meta' => 'Engineering & Systems'],
            ],
        ];

        $disciplines = [];
        $imageIndex = 0;

        foreach ($groups as $title => $projects) {
            $groupItems = [];

            foreach ($projects as $project) {
                $groupItems[] = [
                    ...$project,
                    'url' => '#project-feed',
                    'image' => $images[$imageIndex % max(count($images), 1)] ?? null,
                    'imageAlt' => $project['title'],
                ];
                $imageIndex++;
            }

            $disciplines[] = [
                'title' => $title,
                'items' => $groupItems,
            ];
        }

        return [
            'type' => 'discipline-carousel-browse',
            'heading' => $heading,
            'summary' => $summary,
            'disciplines' => $disciplines,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function processNotesTimelineSection(string $heading, string $summary): array
    {
        return [
            'type' => 'process-notes-timeline',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['meta' => 'Week 1', 'title' => 'The constraint', 'summary' => 'Account opening leaked users at every step — nine screens, three of them redundant compliance checks.'],
                ['meta' => 'Week 3', 'title' => 'The research', 'summary' => 'Session recordings and support tickets pinpointed where applicants abandoned the flow.'],
                ['meta' => 'Week 6', 'title' => 'The call', 'summary' => 'Move verification to the background and collapse the flow to four steps, accepting a slower first deposit.'],
                ['meta' => 'Week 10', 'title' => 'The build', 'summary' => 'Engineering shipped the new flow behind a flag, tested against the compliance contract.'],
                ['meta' => 'Week 12', 'title' => 'The result', 'summary' => 'Drop-off fell 51%, and support tickets about account setup dropped with it.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function creditsGridRosterSection(string $heading, string $summary, array $media): array
    {
        return [
            'type' => 'credits-grid-roster',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['name' => 'Mara Devlin', 'role' => 'Product Lead', 'image' => $media['proof'][0] ?? null],
                ['name' => 'Theo Park', 'role' => 'Staff Engineer'],
                ['name' => 'Aisha Roy', 'role' => 'Researcher'],
                ['name' => 'Sam Okonkwo', 'role' => 'Design Systems'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function nextProjectCtaSection(array $media): array
    {
        return [
            'type' => 'next-project-cta',
            'kicker' => 'Next project',
            'title' => 'Verde — a coffee brand system for a first three-site rollout',
            'summary' => 'Naming, identity, and packaging for an independent roaster opening its first three sites.',
            'url' => '#project-feed',
            'label' => 'View next project',
            'image' => $media['listing'][0] ?? $media['hero'][0],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function projectFeedSection(string $heading, string $summary, array $media, string $itemUrl = '#related-projects'): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'], $media['detail'])));

        $projects = [
            [
                'title' => 'Atlas Ledger — onboarding rebuild',
                'summary' => 'A nine-screen account opening cut to four steps, halving drop-off in a single quarter.',
                'meta' => 'Product & UX · Atlas Ledger · 2025',
                'care_note' => 'Read the case study',
            ],
            [
                'title' => 'Verde — coffee brand system',
                'summary' => 'Naming, identity, and packaging for an independent roaster opening its first three sites.',
                'meta' => 'Brand & Identity · Field Studio · 2025',
                'care_note' => 'Read the case study',
            ],
            [
                'title' => 'Northwind — field engineer UI',
                'summary' => 'A design system and product UI for a renewables platform used in the field every day.',
                'meta' => 'Engineering & Systems · Northwind · 2024',
                'care_note' => 'Read the case study',
            ],
        ];

        $items = [];

        foreach ($projects as $index => $project) {
            $items[] = [
                ...$project,
                'url' => $itemUrl,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $project['title'],
            ];
        }

        return [
            'type' => 'project-feed',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function processNotesSection(string $heading, string $summary, array $media, ?string $url = null, ?string $label = null): array
    {
        $section = [
            'type' => 'process-notes',
            'heading' => $heading,
            'summary' => $summary,
            'image' => $media['detail'][0] ?? $media['hero'][0],
            'imageAlt' => $heading,
            'items' => [
                ['title' => 'The constraint', 'summary' => 'Account opening leaked users at every step — nine screens, three of them redundant compliance checks.'],
                ['title' => 'The call', 'summary' => 'Move verification to the background and collapse the flow to four steps, accepting a slower first deposit.'],
                ['title' => 'The result', 'summary' => 'Drop-off fell 51%, and support tickets about account setup dropped with it.'],
            ],
        ];

        if (filled($url) && filled($label)) {
            $section['url'] = $url;
            $section['label'] = $label;
        }

        return $section;
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function relatedProjectsSection(string $heading, string $summary, array $media, string $itemUrl = '#project-feed'): array
    {
        $images = array_values(array_unique(array_merge($media['proof'], $media['listing'])));

        $related = [
            ['meta' => 'Product & UX', 'title' => 'Harbour — KYC in three taps', 'summary' => 'A challenger bank that pushed identity checks into the background to keep signup moving.'],
            ['meta' => 'Engineering & Systems', 'title' => 'Lumen — design system reset', 'summary' => 'Four product teams unified on one component language and a single token set.'],
            ['meta' => 'Brand & Identity', 'title' => 'Kindred — lending rebrand', 'summary' => 'A warmer identity and tone for a community lending app finding its voice.'],
        ];

        $items = [];

        foreach ($related as $index => $project) {
            $items[] = [
                ...$project,
                'url' => $itemUrl,
                'image' => $images[$index % max(count($images), 1)] ?? null,
                'imageAlt' => $project['title'],
            ];
        }

        return [
            'type' => 'related-projects',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function creditsToolsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'credits-tools',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Credits', 'summary' => 'Mara Devlin (Product Lead), Theo Park (Staff Engineer), Aisha Roy (Researcher), Sam Okonkwo (Design Systems).'],
                ['title' => 'Design stack', 'summary' => 'Figma for design and prototyping, with a shared token library kept in sync to code.'],
                ['title' => 'Build stack', 'summary' => 'A Laravel back end, a React onboarding client, and Playwright covering the critical path.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function creatorHeroSection(string $heading, string $summary, array $media, string $mediaKey): array
    {
        $mediaUrl = $media[$mediaKey][0] ?? $media['hero'][0];

        return [
            'type' => 'creator-hero',
            'heading' => $heading,
            'summary' => $summary,
            'mediaUrl' => $mediaUrl,
            'mediaAlt' => $heading,
            'items' => [
                ['title' => 'Mara Devlin — Product Lead', 'summary' => 'Owned the flow rework and the research that justified every cut screen.'],
                ['title' => 'Theo Park — Staff Engineer', 'summary' => 'Moved verification to the background without breaking the compliance contract.'],
                ['title' => 'Aisha Roy — Researcher', 'summary' => 'Ran the drop-off study that turned a hunch into a roadmap.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(string $heading, string $summary): array
    {
        return [
            'type' => 'newsletter',
            'heading' => $heading,
            'summary' => $summary,
            'action' => '#newsletter',
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['category' => 'Product & UX', 'title' => 'Harbour — KYC in three taps', 'summary' => 'How a challenger bank pushed identity checks into the background.'],
            ['category' => 'Motion & Film', 'title' => 'Atlas Festival — launch film', 'summary' => 'A motion toolkit that took a regional arts festival national.'],
            ['category' => 'Engineering & Systems', 'title' => 'Foundry — performance reset', 'summary' => 'A rebuild that cut time-to-interactive in half across four products.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#project-feed',
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(string $heading): array
    {
        return [
            'type' => 'proof',
            'heading' => $heading,
            'items' => [
                ['value' => '240+', 'label' => 'Case studies in the directory'],
                ['value' => '18k', 'label' => 'Creators reading each month'],
                ['value' => '2 days', 'label' => 'Average reply to a submission'],
            ],
        ];
    }

    /**
     * @param  list<array<string, string>>  $actions
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary, array $actions): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => $actions,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'items' => [
                ['label' => 'Projects', 'url' => '#project-feed'],
                ['label' => 'Disciplines', 'url' => '#discipline-filters'],
                ['label' => 'Process', 'url' => '#process-notes'],
                ['label' => 'Hiring', 'url' => '#newsletter'],
            ],
            'ctaLabel' => 'Submit a case study',
            'ctaUrl' => '#newsletter',
            'consultationUrl' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'summary' => 'An editorial directory of creative case studies — the projects, the process, and the people behind the work.',
            'columns' => [
                [
                    'heading' => 'Directory',
                    'title' => 'Directory',
                    'links' => [
                        ['label' => 'All projects', 'url' => '#project-feed'],
                        ['label' => 'Disciplines', 'url' => '#discipline-filters'],
                        ['label' => 'Tools', 'url' => '#credits-tools'],
                    ],
                ],
                [
                    'heading' => 'For creators',
                    'title' => 'For creators',
                    'links' => [
                        ['label' => 'Submit a case study', 'url' => '#newsletter'],
                        ['label' => 'List a studio', 'url' => '#newsletter'],
                        ['label' => 'Post a role', 'url' => '#newsletter'],
                    ],
                ],
                [
                    'heading' => 'Studio Index',
                    'title' => 'Studio Index',
                    'links' => [
                        ['label' => 'Weekly newsletter', 'url' => '#newsletter'],
                        ['label' => self::DESK_EMAIL, 'url' => 'mailto:' . self::DESK_EMAIL],
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
