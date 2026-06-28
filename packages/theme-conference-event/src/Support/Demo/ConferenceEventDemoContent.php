<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ConferenceEvent\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Conference Event theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (agenda / speakers /
 * ticket-tiers / sponsors / venue) alongside the standard hero/proof/cta —
 * giving every surface a full conference site rather than the shared
 * five-section skeleton.
 */
final class ConferenceEventDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Horizon Summit';

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
            title: self::BRAND . ' — Two Days on the Future of Product',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Two days on the future of product',
                'Horizon Summit brings 1,200 product, design, and engineering leaders to Lisbon for two days of talks, workshops, and the people who ship the work.',
            ),
            renderData: [
                'summary' => 'Horizon Summit is a two-day product conference in Lisbon — 60 speakers, four tracks, and 1,200 builders in one room.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Lisbon · 14–15 October 2025',
                        'heading' => 'Two days on the future of product',
                        'summary' => '1,200 product, design, and engineering leaders. 60 speakers across four tracks. One room where the people who ship the work compare notes.',
                        'actions' => [
                            ['label' => 'Get your ticket', 'url' => '#tickets', 'style' => 'primary'],
                            ['label' => 'View the agenda', 'url' => '#agenda', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Horizon Summit main stage',
                    ],
                    $this->agendaSection(
                        heading: 'Two days, four tracks',
                        summary: 'A multi-track programme built to be scanned. Pick a path or move between rooms.',
                    ),
                    $this->speakersSection(
                        heading: 'Speakers worth crossing a continent for',
                        summary: 'Founders, principal engineers, and design leaders from the teams behind the products you use every day.',
                    ),
                    $this->ticketTiersSection(
                        heading: 'Tickets',
                        summary: 'Early-bird pricing holds until 31 August. Group rates available for teams of four or more.',
                    ),
                    $this->featuresSection(
                        heading: 'More than a stage',
                        summary: 'The hallway track, the workshops, and the after-hours are where the real conversations happen.',
                    ),
                    $this->sponsorsSection(
                        heading: 'Backed by teams who build for builders',
                        summary: 'The companies powering Horizon Summit 2025.',
                    ),
                    $this->venueSection(
                        heading: 'Centro de Congressos, Lisbon',
                        summary: 'A riverside venue ten minutes from the old town, with everything within a short walk.',
                    ),
                    $this->proofSection(
                        heading: 'Why people come back',
                        summary: 'What the last three editions delivered.',
                    ),
                    $this->ctaSection(
                        heading: 'Be in the room this October',
                        summary: 'Early-bird tickets are limited. Secure your place before prices step up on 1 September.',
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
            name: self::BRAND . ' Agenda',
            title: 'Agenda — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The full two-day agenda',
                'Every talk, workshop, and panel across four tracks, mapped to the time you have.',
            ),
            renderData: [
                'summary' => 'The full Horizon Summit agenda — four tracks, two days, 48 sessions you can plan around.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Programme',
                        'heading' => 'The full two-day agenda',
                        'summary' => 'Browse every session across the Product, Engineering, Design, and Leadership tracks. Build a schedule that fits the day you want.',
                        'actions' => [
                            ['label' => 'Get your ticket', 'url' => '#tickets', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Horizon Summit session in progress',
                    ],
                    $this->agendaSection(
                        heading: 'Day one — building the thing',
                        summary: 'Discovery, delivery, and the craft of shipping. Tracks run in parallel from 09:00.',
                    ),
                    $this->contentListingSection(
                        heading: 'Day two — scaling the thing',
                        summary: 'Teams, platforms, and the org problems that show up once the product works.',
                    ),
                    $this->ctaSection(
                        heading: 'See a session you cannot miss?',
                        summary: 'Lock in your ticket and we will send a personal scheduler so you never double-book a slot.',
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
            name: self::BRAND . ' Speaker',
            title: 'Amara Osei — Speaker — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Amara Osei — Keynote',
                'VP of Product at Tidelift on building durable roadmaps when everything around you is changing.',
            ),
            renderData: [
                'summary' => 'Amara Osei, VP of Product at Tidelift, on the keynote stage — plus the two sessions she is hosting.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Keynote speaker',
                        'heading' => 'Amara Osei — VP of Product, Tidelift',
                        'summary' => 'Twelve years shipping developer tools at scale. Amara opens day one with a keynote on building roadmaps that survive contact with reality.',
                        'actions' => [
                            ['label' => 'Back to speakers', 'url' => '#speakers', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Amara Osei on stage',
                    ],
                    $this->speakersSection(
                        heading: 'Amara is also hosting',
                        summary: 'Two smaller-room sessions where the keynote ideas get put to work.',
                    ),
                    $this->agendaSection(
                        heading: 'Where to catch her',
                        summary: 'Both of Amara\'s sessions, mapped across the two days.',
                    ),
                    $this->proofSection(
                        heading: 'On the Horizon stage before',
                        summary: 'What past attendees took from Amara\'s talks.',
                    ),
                    $this->ctaSection(
                        heading: 'Want a front-row seat?',
                        summary: 'Keynote rooms fill first. Grab a ticket and reserve your spot for the day-one opener.',
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
            name: self::BRAND . ' Venue',
            title: 'Venue & travel — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Venue & travel',
                'Everything you need to plan the trip — where we are, where to stay, and how to reach the team.',
            ),
            renderData: [
                'summary' => 'How to find Horizon Summit in Lisbon, where to stay, and how to reach the organising team.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Plan your trip',
                        'heading' => 'Venue & travel',
                        'summary' => 'Centro de Congressos de Lisboa, on the river in Belém. Reach the team at hello@horizonsummit.example — we answer within one working day.',
                        'actions' => [
                            ['label' => 'Email the team', 'url' => 'mailto:hello@horizonsummit.example', 'style' => 'primary'],
                            ['label' => 'Get your ticket', 'url' => '#tickets', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Centro de Congressos de Lisboa',
                    ],
                    $this->venueSection(
                        heading: 'Getting to the venue',
                        summary: 'A riverside venue with the airport 20 minutes away and the metro at the door.',
                    ),
                    $this->featuresSection(
                        heading: 'While you are in Lisbon',
                        summary: 'Hotel blocks, recommended neighbourhoods, and the after-hours map.',
                    ),
                    $this->ctaSection(
                        heading: 'Still have a question?',
                        summary: 'Email the organising team and a real person will get back to you within one working day.',
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
            name: self::BRAND . ' No Sessions',
            title: 'No sessions yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No sessions in this track yet',
                'A graceful empty state for a filtered agenda with no matching sessions.',
            ),
            renderData: [
                'summary' => 'No sessions match that track yet — but the programme is still filling in.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Agenda',
                        'heading' => 'No sessions in this track yet',
                        'summary' => 'We are still confirming speakers for this track. Clear the filter to see the full programme, or check back as the line-up grows.',
                        'actions' => [
                            ['label' => 'View full agenda', 'url' => '#agenda', 'style' => 'primary'],
                            ['label' => 'Get your ticket', 'url' => '#tickets', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'agenda',
                        'heading' => 'Sessions land here as they are confirmed',
                        'summary' => 'When talks are added to this track they will appear here, ordered by start time.',
                        'items' => [],
                    ],
                    $this->ticketTiersSection(
                        heading: 'Tickets are open now',
                        summary: 'The programme grows weekly, but ticket prices only go one way. Lock in early-bird while it lasts.',
                    ),
                    $this->ctaSection(
                        heading: 'Want the line-up as it drops?',
                        summary: 'Grab a ticket and we will email you the moment new speakers and sessions are announced.',
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
                'A not-found page that routes visitors back into the agenda and ticket paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back to the summit.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This session is not on the schedule',
                        'summary' => 'The link is broken or the page has moved. Head back to the agenda, or jump straight to securing your ticket.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View the agenda', 'url' => '#agenda', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and the team will point you to the right place.',
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
            name: self::BRAND . ' Tickets',
            title: 'Get your ticket — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Secure your place at Horizon Summit',
                'A focused conversion page that turns intent into a booked ticket.',
            ),
            renderData: [
                'summary' => 'Secure your place at Horizon Summit 2025 before early-bird pricing ends.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get your ticket',
                        'heading' => 'Secure your place this October',
                        'summary' => 'Two days, 60 speakers, 1,200 builders. Early-bird tickets are limited and prices step up on 1 September.',
                        'actions' => [
                            ['label' => 'Get your ticket', 'url' => '#tickets', 'style' => 'primary'],
                            ['label' => 'View the agenda', 'url' => '#agenda', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Horizon Summit crowd',
                    ],
                    $this->ticketTiersSection(
                        heading: 'Choose your tier',
                        summary: 'Three ways in, from single-day passes to the full workshop experience.',
                    ),
                    $this->proofSection(
                        heading: 'In good company',
                        summary: 'What past attendees say about the room.',
                    ),
                    $this->ctaSection(
                        heading: 'Early-bird ends 31 August',
                        summary: 'Book before the deadline and save €120 on the full two-day pass.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function agendaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'agenda',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => '09:00 · Keynote — Roadmaps that survive reality', 'summary' => 'Amara Osei opens the summit on building product plans that hold up when the ground shifts.'],
                ['title' => '10:30 · Discovery without the theatre', 'summary' => 'A practical look at research that actually changes what gets built — no sticky-note ceremonies.'],
                ['title' => '13:00 · Shipping at the edge of the org chart', 'summary' => 'How platform teams unblock product teams instead of becoming the new bottleneck.'],
                ['title' => '15:30 · Design systems that earn their keep', 'summary' => 'When to invest in a system, when to tear it down, and how to know the difference.'],
                ['title' => '17:00 · Fireside — Leading through a rewrite', 'summary' => 'Three engineering leaders on the rewrites that worked and the ones that nearly sank the company.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function speakersSection(string $heading, string $summary): array
    {
        return [
            'type' => 'speakers',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Amara Osei', 'summary' => 'VP of Product, Tidelift — keynote on durable roadmaps.'],
                ['title' => 'Diego Marques', 'summary' => 'Principal Engineer, Stripe — on platforms that scale with the team.'],
                ['title' => 'Hana Sato', 'summary' => 'Head of Design, Figma — design systems that hold up under pressure.'],
                ['title' => 'Tomas Bauer', 'summary' => 'Founder, Northrim — building a product company without losing the craft.'],
                ['title' => 'Lena Fischer', 'summary' => 'Director of Research, Spotify — discovery that changes the roadmap.'],
                ['title' => 'Kwame Mensah', 'summary' => 'CTO, Loop — leading engineering through hypergrowth.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ticketTiersSection(string $heading, string $summary): array
    {
        return [
            'type' => 'ticket-tiers',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Single Day · €290', 'summary' => 'One full day of talks and access to the hallway track. Pick day one or day two.'],
                ['title' => 'Full Conference · €490', 'summary' => 'Both days, all four tracks, the speaker dinner, and the after-party. The complete summit.'],
                ['title' => 'Workshop Pass · €690', 'summary' => 'Everything in Full Conference plus a hands-on small-group workshop on day two.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(string $heading, string $summary): array
    {
        return [
            'type' => 'features',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'The hallway track', 'summary' => 'Curated lunch tables and topic corners so the best conversations are not left to chance.'],
                ['title' => 'Hands-on workshops', 'summary' => 'Small-group sessions on day two where speakers go deep with twenty people, not two hundred.'],
                ['title' => 'After hours', 'summary' => 'A riverside party on night one and informal dinners hosted across the city.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sponsorsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'sponsors',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Stripe', 'summary' => 'Headline partner — powering the workshop track.'],
                ['title' => 'Figma', 'summary' => 'Design partner — hosting the design lounge.'],
                ['title' => 'Linear', 'summary' => 'Stage partner — sponsoring the day-one keynote.'],
                ['title' => 'Vercel', 'summary' => 'Community partner — backing the student scholarship programme.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function venueSection(string $heading, string $summary): array
    {
        return [
            'type' => 'venue',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Centro de Congressos de Lisboa', 'summary' => 'Praça das Indústrias, Belém — a riverside congress centre with four stages under one roof.'],
                ['title' => 'Getting there', 'summary' => '20 minutes from Humberto Delgado airport, a short tram ride from the city centre, parking on site.'],
                ['title' => 'Where to stay', 'summary' => 'Discounted room blocks at three hotels within a ten-minute walk, booked through your ticket confirmation.'],
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
                ['title' => '1,200 attendees', 'quote' => 'Sold out three years running, with a waitlist every edition.'],
                ['title' => '94% would return', 'quote' => 'Post-event survey across the 2024 cohort of product and engineering leaders.'],
                ['title' => '60 speakers', 'quote' => 'Founders and practitioners from the teams behind the tools you use daily.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary): array
    {
        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => '09:30 · Platforms that scale with the team', 'summary' => 'Diego Marques on the internal tooling that grows headcount without growing friction.'],
                ['title' => '11:00 · Research that moves the roadmap', 'summary' => 'Lena Fischer on discovery work that leadership actually acts on.'],
                ['title' => '14:00 · Leading engineering through hypergrowth', 'summary' => 'Kwame Mensah on keeping quality high while the org doubles every year.'],
                ['title' => '16:00 · Closing panel — What we got wrong', 'summary' => 'Speakers reflect on the bets that did not pay off and what they learned.'],
            ],
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
                ['label' => 'Get your ticket', 'url' => '#tickets', 'style' => 'primary'],
                ['label' => 'View the agenda', 'url' => '#agenda', 'style' => 'secondary'],
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
                ['label' => 'Agenda', 'url' => '#agenda'],
                ['label' => 'Speakers', 'url' => '#speakers'],
                ['label' => 'Tickets', 'url' => '#tickets'],
                ['label' => 'Venue', 'url' => '#venue'],
                ['label' => 'Sponsors', 'url' => '#sponsors'],
            ],
            'ctaLabel' => 'Get your ticket',
            'ctaUrl' => '#tickets',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A two-day product conference in Lisbon. 14–15 October 2025.',
            'columns' => [
                [
                    'heading' => 'Event',
                    'links' => [
                        ['label' => 'Agenda', 'url' => '#agenda'],
                        ['label' => 'Speakers', 'url' => '#speakers'],
                        ['label' => 'Venue', 'url' => '#venue'],
                        ['label' => 'Sponsors', 'url' => '#sponsors'],
                    ],
                ],
                [
                    'heading' => 'Attend',
                    'links' => [
                        ['label' => 'Tickets', 'url' => '#tickets'],
                        ['label' => 'Group rates', 'url' => '#tickets'],
                        ['label' => 'Travel & stay', 'url' => '#venue'],
                        ['label' => 'Scholarships', 'url' => '#tickets'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#venue'],
                        ['label' => 'hello@horizonsummit.example', 'url' => 'mailto:hello@horizonsummit.example'],
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
