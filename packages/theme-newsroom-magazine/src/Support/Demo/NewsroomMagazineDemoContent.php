<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\NewsroomMagazine\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Newsroom Magazine theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature newsroom renderers (featured-story /
 * category-nav / story-grid / most-read / contributors / newsletter-signup)
 * alongside the standard hero/proof/cta — giving every surface a full editorial
 * front page rather than the shared skeleton.
 */
final class NewsroomMagazineDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'The Mainframe';

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
            name: self::BRAND . ' Front Page',
            title: self::BRAND . ' — Independent Newsroom',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Independent journalism, published daily',
                'The Mainframe is a reader-funded newsroom covering technology, policy, and the people shaping both.',
            ),
            renderData: [
                'summary' => 'The Mainframe is a reader-funded newsroom covering technology, policy, and the people shaping both — reported in the open, edited with care.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Today on The Mainframe',
                        'heading' => 'The newsroom that reads the fine print so you don\'t have to',
                        'summary' => 'Original reporting on technology and public life, written for people who want the full picture and have no time for spin. New stories every weekday, free to read for the first 48 hours.',
                        'actions' => [
                            ['label' => 'Read today\'s edition', 'url' => '#front-page', 'style' => 'primary'],
                            ['label' => 'Become a member', 'url' => '#membership', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'The Mainframe newsroom front page',
                    ],
                    $this->featuredStorySection($media),
                    $this->categoryNavSection(),
                    $this->storyGridSection(
                        heading: 'The latest reporting',
                        summary: 'Fresh from the desk this morning, across every beat we cover.',
                        media: $media,
                    ),
                    $this->mostReadSection(),
                    $this->contributorsSection(),
                    $this->proofSection(
                        heading: 'Journalism you can stand behind',
                        summary: 'Why tens of thousands of readers fund our reporting directly.',
                    ),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Reporting this independent is paid for by readers',
                        summary: 'No paywall on breaking news, no investor pulling the strings. Members keep the lights on and the bylines honest.',
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
            name: self::BRAND . ' Sections',
            title: 'All sections — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every beat, every byline',
                'Browse the full archive of reporting by section, from the technology desk to long-form investigations.',
            ),
            renderData: [
                'summary' => 'Browse the full archive of reporting by section — technology, policy, business, culture, and our long-form investigations desk.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Sections',
                        'heading' => 'Every beat we cover, in one place',
                        'summary' => 'Pick a desk and dive in. Each section is edited by a lead reporter who has spent years on the beat.',
                        'actions' => [
                            ['label' => 'Become a member', 'url' => '#membership', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'The Mainframe section index',
                    ],
                    $this->categoryNavSection(),
                    $this->storyGridSection(
                        heading: 'Most recent across all sections',
                        summary: 'The newest reporting from every desk, newest first.',
                        media: $media,
                    ),
                    $this->contentListingSection(
                        heading: 'From the long-form desk',
                        summary: 'Investigations and features that take weeks to report and an afternoon to read.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Want every section in your inbox?',
                        summary: 'Members get the full archive, ad-free reading, and our private morning briefing before it goes public.',
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
            name: self::BRAND . ' Story',
            title: 'Inside the data centre boom — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Inside the data centre boom reshaping the grid',
                'A three-month investigation into how the rush to build AI infrastructure is straining power networks in three countries.',
            ),
            renderData: [
                'summary' => 'A three-month investigation into how the rush to build AI infrastructure is straining power networks — and who pays for it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Investigation · Technology',
                        'heading' => 'Inside the data centre boom reshaping the grid',
                        'summary' => 'By Amara Idris and Joel Kearns. Across three countries, the scramble to power AI is quietly rewriting who gets electricity first — and who foots the bill.',
                        'actions' => [
                            ['label' => 'Back to all stories', 'url' => '#front-page', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Data centre under construction',
                    ],
                    $this->featuredStorySection($media),
                    $this->mostReadSection(),
                    $this->contributorsSection(),
                    $this->contentListingSection(
                        heading: 'More on this beat',
                        summary: 'Other reporting from our technology and energy desks.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Investigations like this take months',
                        summary: 'No advertiser asked for this story, and none could spike it. Member support is the only reason it exists.',
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
            name: self::BRAND . ' Contact the Newsroom',
            title: 'Contact the newsroom — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Contact the newsroom',
                'Tips, corrections, and pitches all reach a real editor. Securely if you need it.',
            ),
            renderData: [
                'summary' => 'Tips, corrections, and pitches all reach a real editor — securely if you need it. Here is how to get in touch.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Got a story we should be chasing?',
                        'summary' => 'The newsroom reads every message. For sensitive tips, write to tips@mainframe.example or reach us on Signal at +44 7700 900145 — we protect our sources.',
                        'actions' => [
                            ['label' => 'Email the desk', 'url' => 'mailto:newsroom@mainframe.example', 'style' => 'primary'],
                            ['label' => 'Read our ethics policy', 'url' => '#ethics', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Mainframe editorial desk',
                    ],
                    $this->categoryNavSection(),
                    $this->contributorsSection(),
                    $this->proofSection(
                        heading: 'How we handle what you send',
                        summary: 'Our standards for tips, corrections, and source protection.',
                    ),
                    $this->ctaSection(
                        heading: 'Not a tip, but want to help?',
                        summary: 'Membership is what lets us answer every tip and chase the ones that matter.',
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
            name: self::BRAND . ' No Stories',
            title: 'No stories yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No stories in this section yet',
                'A graceful empty state for a filtered section with no published articles.',
            ),
            renderData: [
                'summary' => 'No stories match that filter yet — but the newsroom can still point you to something worth reading.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Section archive',
                        'heading' => 'No stories filed under that filter — yet',
                        'summary' => 'We have not published in this section recently. Clear the filter to see everything, or follow the beat to be the first to know.',
                        'actions' => [
                            ['label' => 'Read today\'s edition', 'url' => '#front-page', 'style' => 'primary'],
                            ['label' => 'Browse all sections', 'url' => '#sections', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'story-grid',
                        'heading' => 'Nothing filed here yet',
                        'summary' => 'When reporting lands in this section it will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->mostReadSection(),
                    $this->categoryNavSection(),
                    $this->ctaSection(
                        heading: 'Looking for a story you read before?',
                        summary: 'Members get full archive search going back to our first edition.',
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
                'A not-found page that routes readers back into the front page and the most-read stories.',
            ),
            renderData: [
                'summary' => 'That story has moved or never ran — here is the way back to the newsroom.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page never made it past the copy desk',
                        'summary' => 'The link is broken or the story has been unpublished. Head back to today\'s edition, or catch up on what everyone is reading.',
                        'actions' => [
                            ['label' => 'Back to the front page', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Browse all sections', 'url' => '#sections', 'style' => 'secondary'],
                        ],
                    ],
                    $this->mostReadSection(),
                    $this->ctaSection(
                        heading: 'While you are here',
                        summary: 'Members never lose a story — every link stays live and searchable for good.',
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
            name: self::BRAND . ' Membership',
            title: 'Become a member — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Fund the journalism you rely on',
                'A focused membership page inviting readers to support independent reporting.',
            ),
            renderData: [
                'summary' => 'Fund the journalism you rely on. Membership keeps The Mainframe independent and the reporting free at the point of breaking news.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Membership',
                        'heading' => 'Fund the journalism you rely on',
                        'summary' => 'For the price of a coffee a week you keep reporters on the beat, fund the next investigation, and read everything ad-free.',
                        'actions' => [
                            ['label' => 'Become a member', 'url' => '#membership', 'style' => 'primary'],
                            ['label' => 'Compare the tiers', 'url' => '#tiers', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Support The Mainframe',
                    ],
                    $this->featuresSection(),
                    $this->proofSection(
                        heading: 'What your membership pays for',
                        summary: 'The reporting that exists because readers chose to fund it.',
                    ),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Join 40,000 readers who fund the newsroom',
                        summary: 'Cancel any time, keep the archive, and know exactly where your money goes — we publish the books every year.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function featuredStorySection(array $media): array
    {
        return [
            'type' => 'featured-story',
            'heading' => 'The lead story',
            'summary' => 'What the whole newsroom is working on today.',
            'mediaUrl' => $media['detail'][0] ?? null,
            'mediaAlt' => 'Lead story illustration',
            'items' => [
                [
                    'title' => 'Inside the data centre boom reshaping the grid',
                    'summary' => 'A three-month investigation across three countries into how the rush to power AI is quietly rewriting who gets electricity first.',
                ],
                [
                    'title' => 'The quiet rewrite of the encryption rules',
                    'summary' => 'New draft legislation would force a backdoor on messaging apps. We read all 240 pages so you don\'t have to.',
                ],
                [
                    'title' => 'Why your city\'s transit app keeps failing',
                    'summary' => 'The procurement decisions behind the screen — and the small vendor that quietly runs a dozen networks.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryNavSection(): array
    {
        return [
            'type' => 'category-nav',
            'heading' => 'Browse by section',
            'summary' => 'Five desks, each edited by a reporter who lives on the beat.',
            'items' => [
                ['title' => 'Technology', 'summary' => 'Hardware, software, and the companies betting the firm on AI.'],
                ['title' => 'Policy', 'summary' => 'Regulation, courts, and the slow grind of how rules actually get made.'],
                ['title' => 'Business', 'summary' => 'Markets, mergers, and the money moving behind the headlines.'],
                ['title' => 'Culture', 'summary' => 'How technology reshapes the way we work, watch, and argue.'],
                ['title' => 'Investigations', 'summary' => 'Long-form accountability reporting that takes weeks to stand up.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function storyGridSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $stories = [
            ['title' => 'Regulators open probe into ad-tech middlemen', 'section' => 'Policy', 'summary' => 'The investigation centres on how bid data moves between exchanges most readers have never heard of.'],
            ['title' => 'The startup quietly buying up local newspapers', 'section' => 'Business', 'summary' => 'Eleven titles in eighteen months, one holding company, and a strategy nobody will explain on the record.'],
            ['title' => 'What we learned testing six AI note-takers', 'section' => 'Technology', 'summary' => 'We ran the same meeting through all of them. Only two could be trusted with the transcript.'],
            ['title' => 'Inside the union drive at a warehouse robotics firm', 'section' => 'Business', 'summary' => 'Workers who build the robots are organising — and management is paying attention.'],
            ['title' => 'The court fight that could redefine fair use', 'section' => 'Policy', 'summary' => 'A ruling expected next month may decide what training data is allowed to contain.'],
            ['title' => 'How a small town fixed its broadband itself', 'section' => 'Culture', 'summary' => 'When the carriers said no, a retired engineer and a parish council said fine, we\'ll do it.'],
        ];

        $items = [];

        foreach ($stories as $index => $story) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                'title' => $story['title'],
                'summary' => $story['summary'],
                'section' => $story['section'],
                'url' => '#story-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $story['title'],
            ];
        }

        return [
            'type' => 'story-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mostReadSection(): array
    {
        return [
            'type' => 'most-read',
            'heading' => 'Most read this week',
            'summary' => 'The stories readers kept coming back to.',
            'items' => [
                ['title' => 'The spreadsheet error that cost a council £4m', 'summary' => 'A single hidden row, two years, and the audit that finally caught it.'],
                ['title' => 'We mapped every outage on the new payment network', 'summary' => 'The pattern the operator insisted did not exist, in one chart.'],
                ['title' => 'A field guide to spotting AI-written press releases', 'summary' => 'The tells are everywhere once you know what to look for.'],
                ['title' => 'The last fax machine in the health service', 'summary' => 'Why one hospital ward still runs on a technology older than its staff.'],
                ['title' => 'Inside the gig economy\'s appeals black box', 'summary' => 'Thousands of drivers deactivated by an algorithm, and the humans who never reply.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contributorsSection(): array
    {
        return [
            'type' => 'contributors',
            'heading' => 'Reported and edited by',
            'summary' => 'A small, senior newsroom — no wire copy, no rewrites of someone else\'s scoop.',
            'items' => [
                ['title' => 'Amara Idris', 'summary' => 'Investigations editor. Fifteen years on the technology and accountability beat.'],
                ['title' => 'Joel Kearns', 'summary' => 'Energy and infrastructure reporter. Files the stories utilities would rather you skipped.'],
                ['title' => 'Mei Lin Chau', 'summary' => 'Policy correspondent. Reads the legislation in full so the reporting is right.'],
                ['title' => 'Daniel Rourke', 'summary' => 'Business desk lead. Follows the money through the shell companies.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(): array
    {
        return [
            'type' => 'features',
            'heading' => 'What members get',
            'summary' => 'Everything that keeps the newsroom independent, in one membership.',
            'items' => [
                ['title' => 'Ad-free reading', 'summary' => 'No trackers, no pop-ups, no sponsored content dressed up as news.'],
                ['title' => 'The morning briefing', 'summary' => 'Our editors\' read on the day\'s stories, in your inbox before they go public.'],
                ['title' => 'Full archive access', 'summary' => 'Every story since our first edition, searchable and always live.'],
                ['title' => 'A say in what we chase', 'summary' => 'Members vote each quarter on which investigation we fund next.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(): array
    {
        return [
            'type' => 'newsletter-signup',
            'heading' => 'The morning briefing, free to your inbox',
            'summary' => 'One email each weekday: the three stories that matter and why, written by the editor on duty.',
            'items' => [
                ['title' => 'Weekday mornings', 'summary' => 'In your inbox by 7am, read in under five minutes.'],
                ['title' => 'No noise', 'summary' => 'Three stories, plainly explained. We never sell your address.'],
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
                ['title' => '40,000 members', 'summary' => 'Readers who fund the newsroom directly, with no advertiser able to spike a story.'],
                ['title' => '6-year track record', 'summary' => 'Independent since day one, with corrections published openly and promptly.'],
                ['title' => '11 awards', 'summary' => 'Recognised for investigations the rest of the press chose not to run.'],
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
            ['title' => 'The town that bought its own water supply', 'section' => 'Investigations', 'summary' => 'A year-long look at what happens when residents take a utility into their own hands.'],
            ['title' => 'How open-source maps quietly run your commute', 'section' => 'Technology', 'summary' => 'The volunteer project most apps depend on, and the strain it is under.'],
            ['title' => 'The lobbying campaign you were never meant to see', 'section' => 'Policy', 'summary' => 'Documents reveal a coordinated push to weaken a bill before it reached committee.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                'title' => $entry['title'],
                'summary' => $entry['summary'],
                'section' => $entry['section'],
                'url' => '#feature-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'gallery',
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
                ['label' => 'Become a member', 'url' => '#membership', 'style' => 'primary'],
                ['label' => 'Read today\'s edition', 'url' => '#front-page', 'style' => 'secondary'],
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
                ['label' => 'Front page', 'url' => '#front-page'],
                ['label' => 'Technology', 'url' => '#technology'],
                ['label' => 'Policy', 'url' => '#policy'],
                ['label' => 'Investigations', 'url' => '#investigations'],
                ['label' => 'Newsletters', 'url' => '#newsletters'],
            ],
            'ctaLabel' => 'Become a member',
            'ctaUrl' => '#membership',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A reader-funded newsroom covering technology, policy, and public life. Independent since 2019.',
            'columns' => [
                [
                    'heading' => 'Sections',
                    'links' => [
                        ['label' => 'Technology', 'url' => '#technology'],
                        ['label' => 'Policy', 'url' => '#policy'],
                        ['label' => 'Business', 'url' => '#business'],
                        ['label' => 'Investigations', 'url' => '#investigations'],
                    ],
                ],
                [
                    'heading' => 'The newsroom',
                    'links' => [
                        ['label' => 'About us', 'url' => '#about'],
                        ['label' => 'Ethics policy', 'url' => '#ethics'],
                        ['label' => 'Corrections', 'url' => '#corrections'],
                        ['label' => 'Contributors', 'url' => '#contributors'],
                    ],
                ],
                [
                    'heading' => 'Get in touch',
                    'links' => [
                        ['label' => 'Contact the desk', 'url' => '#contact'],
                        ['label' => 'newsroom@mainframe.example', 'url' => 'mailto:newsroom@mainframe.example'],
                        ['label' => 'Secure tips', 'url' => '#tips'],
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
