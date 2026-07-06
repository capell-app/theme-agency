<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MainStage\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;
use Capell\ThemeStudio\MainStage\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\MainStage\MainStageThemeServiceProvider;

/**
 * Complete, vertical-authentic demo content for the Main Stage theme,
 * seeding a fictional three-day creator/technology conference: the
 * "Nightcast Summit". Mirrors NightShiftDemoContent's structure exactly.
 *
 * Main Stage is definition-only (see MainStageThemeServiceProvider): it
 * registers no ThemeRenderer or section renderers, so every surface renders
 * through the shared `x-capell::layout` + layout-builder container pipeline.
 * Each surface below seeds a `containers` payload (a 'main' container
 * carrying a `page-content` widget plus one bespoke Main Stage widget
 * instance per signature section in that surface's {@see sectionCopy()})
 * plus the matching `widgets` blueprint `ThemeDemoPageInstaller` dispatches
 * through `WidgetCreator` before writing the containers onto the page's
 * Layout.
 */
final class MainStageDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Nightcast Summit';

    private const string TICKETS_EMAIL = 'tickets@nightcast-summit.example';

    /**
     * Bespoke Main Stage section types that {@see sectionCopy()} may
     * contain and that this class turns into real, seeded
     * `capell.widget.main-stage.*` widget instances.
     *
     * @var array<string, WidgetComponentEnum>
     */
    private const array BESPOKE_SECTION_WIDGETS = [
        'agenda-grid-days-tracks-rooms' => WidgetComponentEnum::AgendaGrid,
        'speaker-wall-hover-bios' => WidgetComponentEnum::SpeakerWall,
        'ticket-tier-comparison' => WidgetComponentEnum::TicketTierComparison,
        'countdown-band' => WidgetComponentEnum::CountdownBand,
        'venue-travel-panels' => WidgetComponentEnum::VenueTravelPanels,
        'sponsor-tier-walls' => WidgetComponentEnum::SponsorTierWalls,
        'live-now-replay-state' => WidgetComponentEnum::LiveNowReplayState,
        'past-editions-archive' => WidgetComponentEnum::PastEditionsArchive,
    ];

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
     * The ordered per-surface section copy, keyed by `surface`. Recovered
     * verbatim structure from NightShiftDemoContent's own `sectionCopy()`
     * pattern — {@see bespokeSectionsForSurface()} reads this to seed real
     * `capell.widget.main-stage.*` widgets.
     *
     * @return list<array<string, mixed>>
     */
    public function sectionCopy(string $surface): array
    {
        $themeKey = MainStageThemeServiceProvider::THEME_KEY;
        $media = ThemeDemoMedia::groupedForTheme($themeKey);

        return match ($surface) {
            'homepage' => [
                $this->heroSection(
                    eyebrow: 'October 14 to 16 — Lisbon',
                    heading: 'Three days on the Nightcast Summit main stage',
                    summary: 'Ninety speakers, six tracks, and one countdown clock. Get your ticket before the early-bird tier closes.',
                    media: $media,
                    mediaKey: 'hero',
                    mediaAlt: 'Nightcast Summit main stage at night, packed auditorium',
                    primaryUrl: '/theme-' . $themeKey . '-cta',
                    primaryLabel: 'Get tickets',
                    secondaryUrl: '#agenda',
                    secondaryLabel: 'View the agenda',
                ),
                $this->countdownBandSection(
                    heading: 'Early-bird tickets close soon',
                    summary: 'Lock in the lowest price before the tier closes and the price steps up.',
                    deadline: '2026-09-01T00:00:00+00:00',
                ),
                $this->liveStateSection(
                    state: 'upcoming',
                    heading: 'Doors open October 14',
                    summary: 'Registration and the welcome reception open the evening before day one.',
                    actionLabel: 'See the full schedule',
                    actionUrl: '#agenda',
                ),
                $this->agendaSection(
                    heading: 'Three days, six tracks, one main stage',
                    summary: 'Filter by room or just scroll — every session is here, with the room happening right now highlighted automatically.',
                ),
                $this->speakerWallSection(
                    heading: 'Ninety speakers taking the stage',
                    summary: 'Founders, researchers, and builders from across the industry, hover any card for their full bio.',
                ),
                $this->ticketTierSection(
                    heading: 'Pick your ticket tier',
                    summary: 'Every tier includes full session access and the after-party. Higher tiers add workshops and the speaker dinner.',
                ),
                $this->venueTravelSection(
                    heading: 'Venue and travel',
                    summary: 'Everything you need to plan the trip, from the venue address to where to stay.',
                ),
                $this->sponsorTierSection(
                    heading: 'Thanks to our sponsors',
                    summary: 'Nightcast Summit runs because of the partners below.',
                ),
                $this->proofSection(
                    heading: 'Why builders keep coming back',
                    summary: 'What past attendees say about the Summit.',
                ),
                $this->pastEditionsSection(
                    heading: 'Past editions',
                    summary: 'A look back at how the Summit has grown.',
                ),
                $this->ctaSection(
                    heading: 'Save your seat on the main stage',
                    summary: 'Early-bird pricing ends soon. Bring your team and save on group tickets.',
                    primaryUrl: '/theme-' . $themeKey . '-cta',
                    secondaryUrl: 'mailto:' . self::TICKETS_EMAIL,
                    secondaryLabel: 'Email the ticket desk',
                ),
            ],
            'directory' => [
                $this->heroSection(
                    eyebrow: 'Full schedule',
                    heading: 'Every session across all three days',
                    summary: 'Browse the complete Nightcast Summit agenda by day, track, and room.',
                    media: $media,
                    mediaKey: 'listing',
                    mediaAlt: 'Nightcast Summit schedule board',
                    primaryUrl: '/theme-' . $themeKey . '-cta',
                    primaryLabel: 'Get tickets',
                ),
                $this->agendaSection(
                    heading: 'The complete agenda',
                    summary: 'Every session, every room, all three days.',
                ),
                $this->speakerWallSection(
                    heading: 'Featured speakers this year',
                    summary: 'A first look at who is taking the main stage.',
                ),
                $this->ctaSection(
                    heading: 'Found a session you cannot miss?',
                    summary: 'Get your ticket now before the early-bird tier closes.',
                    primaryUrl: '/theme-' . $themeKey . '-cta',
                    secondaryUrl: '#agenda',
                    secondaryLabel: 'Back to the agenda',
                ),
            ],
            'detail' => [
                $this->heroSection(
                    eyebrow: 'Speaker spotlight',
                    heading: 'Mara Okonkwo takes the main stage on day two',
                    summary: 'The founder of Loomwork Robotics on shipping hardware at the speed of software — and what broke along the way.',
                    media: $media,
                    mediaKey: 'detail',
                    mediaAlt: 'Mara Okonkwo speaking at a past Nightcast Summit',
                    primaryUrl: '/theme-' . $themeKey . '-cta',
                    primaryLabel: 'Get tickets',
                    secondaryUrl: '#speakers',
                    secondaryLabel: 'See the full speaker wall',
                ),
                $this->speakerWallSection(
                    heading: 'More from this track',
                    summary: 'Other speakers sharing the hardware and robotics track with Mara.',
                ),
                $this->liveStateSection(
                    state: 'replay',
                    heading: 'Catch up on last year\'s keynote',
                    summary: 'Mara\'s 2025 talk is available now as a replay while you wait for this year\'s session.',
                    actionLabel: 'Watch the replay',
                    actionUrl: '#past-editions',
                ),
                $this->ctaSection(
                    heading: 'Want to see Mara live?',
                    summary: 'Her day-two keynote is included in every ticket tier.',
                    primaryUrl: '/theme-' . $themeKey . '-cta',
                    secondaryUrl: '#agenda',
                    secondaryLabel: 'See when she speaks',
                ),
            ],
            'contact' => [
                $this->heroSection(
                    eyebrow: 'Talk to the team',
                    heading: 'Questions about Nightcast Summit?',
                    summary: 'Ticketing, sponsorship, speaking proposals, and group bookings — the ticket desk replies within one business day.',
                    media: $media,
                    mediaKey: 'contact',
                    mediaAlt: 'Nightcast Summit ticket desk at a past event',
                    primaryUrl: 'mailto:' . self::TICKETS_EMAIL,
                    primaryLabel: 'Email the ticket desk',
                    secondaryUrl: '#venue',
                    secondaryLabel: 'Venue and travel info',
                ),
                $this->venueTravelSection(
                    heading: 'Getting to the venue',
                    summary: 'Address, transit, parking, and where to stay.',
                ),
                $this->sponsorTierSection(
                    heading: 'Sponsor Nightcast Summit',
                    summary: 'Get in front of six hundred builders across three days.',
                ),
            ],
            'empty' => [
                $this->heroSection(
                    eyebrow: 'Full schedule',
                    heading: 'No sessions match that filter — yet',
                    summary: 'Nothing is scheduled in that room or track right now. Clear the filter to see the full agenda, or get your ticket to see everything live.',
                    media: [],
                    mediaKey: 'hero',
                    mediaAlt: null,
                    primaryUrl: '#agenda',
                    primaryLabel: 'View the full agenda',
                    secondaryUrl: '/theme-' . $themeKey . '-cta',
                    secondaryLabel: 'Get tickets',
                    withMedia: false,
                ),
                $this->emptyStateListingCopy(),
                $this->agendaSection(
                    heading: 'Popular sessions to start from',
                    summary: 'The sessions past attendees rated highest.',
                ),
                $this->ctaSection(
                    heading: 'Do not want to miss a session?',
                    summary: 'Get your ticket and plan your three days once the full agenda is live.',
                    primaryUrl: '/theme-' . $themeKey . '-cta',
                    secondaryUrl: '/',
                    secondaryLabel: 'Back to home',
                ),
            ],
            'not-found' => [
                $this->heroSection(
                    eyebrow: '404',
                    heading: 'This session is not on the schedule',
                    summary: 'The link is broken or the session has moved. Head back to the agenda, or get your ticket.',
                    media: [],
                    mediaKey: 'hero',
                    mediaAlt: null,
                    primaryUrl: '/',
                    primaryLabel: 'Back to home',
                    secondaryUrl: '/theme-' . $themeKey . '-cta',
                    secondaryLabel: 'Get tickets',
                    withMedia: false,
                ),
                $this->ctaSection(
                    heading: 'Still looking for something?',
                    summary: 'Tell the ticket desk what you needed and they will point you the right way.',
                    primaryUrl: 'mailto:' . self::TICKETS_EMAIL,
                    secondaryUrl: '/',
                    secondaryLabel: 'Back to home',
                ),
            ],
            'cta' => [
                $this->heroSection(
                    eyebrow: 'Get your ticket',
                    heading: 'Save your seat on the main stage',
                    summary: 'Three days, ninety speakers, six tracks. Early-bird pricing ends soon.',
                    media: $media,
                    mediaKey: 'cta',
                    mediaAlt: 'Nightcast Summit audience ready for the keynote',
                    primaryUrl: 'mailto:' . self::TICKETS_EMAIL,
                    primaryLabel: 'Email the ticket desk',
                    secondaryUrl: '#tickets',
                    secondaryLabel: 'Compare ticket tiers',
                ),
                $this->countdownBandSection(
                    heading: 'Early-bird pricing ends soon',
                    summary: 'The price steps up once the early-bird tier closes.',
                    deadline: '2026-09-01T00:00:00+00:00',
                ),
                $this->ticketTierSection(
                    heading: 'Every ticket tier, compared',
                    summary: 'All tiers include full session access and the after-party.',
                ),
                $this->ctaSection(
                    heading: 'One ticket away from the main stage',
                    summary: 'Bring your team — group tickets of five or more get a discount automatically.',
                    primaryUrl: 'mailto:' . self::TICKETS_EMAIL,
                    secondaryUrl: '#venue',
                    secondaryLabel: 'Plan your trip',
                ),
            ],
            default => [],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function emptyStateListingCopy(): array
    {
        return [
            'type' => 'content-listing',
            'heading' => 'Nothing scheduled on this filter',
            'summary' => 'When sessions match this filter they appear here, in schedule order.',
            'items' => [],
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
            title: self::BRAND . ' — Three days on the main stage',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Three days on the Nightcast Summit main stage',
                'Ninety speakers, six tracks, and one countdown clock — Nightcast Summit brings builders together every October.',
            ),
            renderData: [
                'summary' => 'Nightcast Summit is a three-day conference for builders: ninety speakers, six tracks, ticket tiers, and a countdown to the early-bird deadline.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            type: PageTypeEnum::Home,
            layout: LayoutEnum::Home,
            containers: $this->containers('homepage'),
            widgets: $this->widgets('homepage'),
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
                'Every session across all three days',
                'Browse the complete Nightcast Summit agenda by day, track, and room.',
            ),
            renderData: [
                'summary' => 'A scannable index of every Nightcast Summit session, speaker, and room across all three days.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            layout: LayoutEnum::Results,
            containers: $this->containers('directory'),
            widgets: $this->widgets('directory'),
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function detail(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Speaker Spotlight',
            title: 'Mara Okonkwo — Speaker Spotlight — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Mara Okonkwo takes the main stage on day two',
                'The founder of Loomwork Robotics on shipping hardware at the speed of software.',
            ),
            renderData: [
                'summary' => 'A speaker spotlight on Mara Okonkwo, founder of Loomwork Robotics, ahead of her day-two Nightcast Summit keynote.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            containers: $this->containers('detail'),
            widgets: $this->widgets('detail'),
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function contact(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'contact',
            name: self::BRAND . ' Contact',
            title: 'Contact the ticket desk — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Questions about Nightcast Summit?',
                'Ticketing, sponsorship, speaking proposals, and group bookings — the ticket desk replies within one business day.',
            ),
            renderData: [
                'summary' => 'Contact the Nightcast Summit ticket desk for ticketing, sponsorship, speaking, and group-booking questions.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            layout: LayoutEnum::System,
            containers: $this->containers('contact'),
            widgets: $this->widgets('contact'),
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
            title: 'No matching sessions — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No sessions match that filter',
                'A graceful empty state for a filtered agenda with no matching results.',
            ),
            renderData: [
                'summary' => 'No Nightcast Summit sessions match that filter yet — but the agenda can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            containers: $this->containers('empty'),
            widgets: $this->widgets('empty'),
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
                'summary' => 'That page has moved or never existed — here is the way back into the Nightcast Summit agenda.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            type: PageTypeEnum::NotFound,
            layout: LayoutEnum::System,
            containers: $this->containers('not-found'),
            widgets: $this->widgets('not-found'),
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function cta(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'cta',
            name: self::BRAND . ' Get Tickets',
            title: 'Get tickets — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Save your seat on the main stage',
                'A focused ticketing page inviting builders to save their seat before the early-bird tier closes.',
            ),
            renderData: [
                'summary' => 'Save your seat on the Nightcast Summit main stage before the early-bird ticket tier closes.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            containers: $this->containers('cta'),
            widgets: $this->widgets('cta'),
        );
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function containers(string $surface): array
    {
        $widgets = [
            ['widget_key' => 'page-content', 'occurrence' => 1],
        ];

        foreach ($this->bespokeSectionsForSurface($surface) as $bespokeSection) {
            $widgets[] = [
                'widget_key' => $bespokeSection['key'],
                'occurrence' => 1,
            ];
        }

        return [
            'main' => [
                'widgets' => $widgets,
            ],
        ];
    }

    /**
     * @return list<array{method: string, args?: array<array-key, mixed>}>
     */
    private function widgets(string $surface): array
    {
        $widgets = [
            ['method' => 'pageContentWidget'],
        ];

        foreach ($this->bespokeSectionsForSurface($surface) as $bespokeSection) {
            $widgets[] = [
                'method' => 'bespokeContentWidget',
                'args' => [
                    $bespokeSection['key'],
                    $bespokeSection['name'],
                    $bespokeSection['component'],
                    $bespokeSection['meta'],
                ],
            ];
        }

        return $widgets;
    }

    /**
     * @return list<array{key: string, name: string, component: string, meta: array<string, mixed>}>
     */
    private function bespokeSectionsForSurface(string $surface): array
    {
        $bespokeSections = [];
        $occurrenceByType = [];

        foreach ($this->sectionCopy($surface) as $section) {
            $type = $section['type'] ?? null;

            if (! is_string($type) || ! array_key_exists($type, self::BESPOKE_SECTION_WIDGETS)) {
                continue;
            }

            $occurrenceByType[$type] = ($occurrenceByType[$type] ?? 0) + 1;
            $occurrence = $occurrenceByType[$type];
            $component = self::BESPOKE_SECTION_WIDGETS[$type];

            $bespokeSections[] = [
                'key' => sprintf('main-stage-%s-%s-%d', $type, $surface, $occurrence),
                'name' => sprintf('Main Stage %s (%s)', ucfirst($type), $surface),
                'component' => $component->value,
                'meta' => $section,
            ];
        }

        return $bespokeSections;
    }

    /**
     * @param  array<string, list<string>>  $media
     * @return array<string, mixed>
     */
    private function heroSection(
        string $eyebrow,
        string $heading,
        string $summary,
        array $media,
        string $mediaKey,
        ?string $mediaAlt,
        string $primaryUrl,
        string $secondaryUrl = '',
        string $secondaryLabel = '',
        string $primaryLabel = 'Get tickets',
        bool $withMedia = true,
    ): array {
        $actions = [
            ['label' => $primaryLabel, 'url' => $primaryUrl, 'style' => 'primary'],
        ];

        if ($secondaryUrl !== '' && $secondaryLabel !== '') {
            $actions[] = ['label' => $secondaryLabel, 'url' => $secondaryUrl, 'style' => 'secondary'];
        }

        $section = [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'actions' => $actions,
        ];

        if ($withMedia) {
            $section['mediaUrl'] = $media[$mediaKey][0] ?? null;
            $section['mediaAlt'] = $mediaAlt;
        }

        return $section;
    }

    /**
     * @return array<string, mixed>
     */
    private function agendaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'agenda-grid-days-tracks-rooms',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'grid',
            'days' => [
                [
                    'label' => 'Day 1 — October 14',
                    'tracks' => [
                        [
                            'room' => 'Main Stage',
                            'sessions' => [
                                ['time' => '09:00', 'title' => 'Opening keynote: Building in public', 'speaker' => 'Priya Chandrasekar', 'startsAt' => '2026-10-14T09:00:00+00:00', 'endsAt' => '2026-10-14T09:45:00+00:00'],
                                ['time' => '11:00', 'title' => 'The next decade of developer tools', 'speaker' => 'Julien Farrow', 'startsAt' => '2026-10-14T11:00:00+00:00', 'endsAt' => '2026-10-14T11:40:00+00:00'],
                            ],
                        ],
                        [
                            'room' => 'Studio B',
                            'sessions' => [
                                ['time' => '09:30', 'title' => 'Workshop: Shipping your first agent', 'speaker' => 'Devon Marsh', 'startsAt' => '2026-10-14T09:30:00+00:00', 'endsAt' => '2026-10-14T10:30:00+00:00'],
                            ],
                        ],
                    ],
                ],
                [
                    'label' => 'Day 2 — October 15',
                    'tracks' => [
                        [
                            'room' => 'Main Stage',
                            'sessions' => [
                                ['time' => '10:00', 'title' => 'Shipping hardware at the speed of software', 'speaker' => 'Mara Okonkwo', 'startsAt' => '2026-10-15T10:00:00+00:00', 'endsAt' => '2026-10-15T10:45:00+00:00'],
                            ],
                        ],
                        [
                            'room' => 'Studio A',
                            'sessions' => [
                                ['time' => '13:00', 'title' => 'Panel: Funding a hardware startup', 'speaker' => 'Three founders', 'startsAt' => '2026-10-15T13:00:00+00:00', 'endsAt' => '2026-10-15T14:00:00+00:00'],
                            ],
                        ],
                    ],
                ],
                [
                    'label' => 'Day 3 — October 16',
                    'tracks' => [
                        [
                            'room' => 'Main Stage',
                            'sessions' => [
                                ['time' => '09:30', 'title' => 'Closing keynote: What we build next', 'speaker' => 'Priya Chandrasekar', 'startsAt' => '2026-10-16T09:30:00+00:00', 'endsAt' => '2026-10-16T10:15:00+00:00'],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function speakerWallSection(string $heading, string $summary): array
    {
        return [
            'type' => 'speaker-wall-hover-bios',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'wall',
            'speakers' => [
                ['name' => 'Priya Chandrasekar', 'role' => 'CEO, Fieldform Systems', 'photo' => null, 'bio' => 'Priya has spent a decade building developer platforms and opens Nightcast Summit for the third year running.'],
                ['name' => 'Mara Okonkwo', 'role' => 'Founder, Loomwork Robotics', 'photo' => null, 'bio' => 'Mara builds warehouse robotics and speaks candidly about what breaks when hardware ships as fast as software.'],
                ['name' => 'Julien Farrow', 'role' => 'Staff Engineer, Basecamp Tools', 'photo' => null, 'bio' => 'Julien has shipped developer tools used by half a million engineers and studies where the next decade of tooling is headed.'],
                ['name' => 'Devon Marsh', 'role' => 'Independent researcher', 'photo' => null, 'bio' => 'Devon runs hands-on workshops on shipping autonomous agents safely into production.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ticketTierSection(string $heading, string $summary): array
    {
        return [
            'type' => 'ticket-tier-comparison',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'table',
            'tiers' => [
                ['name' => 'General', 'colorToken' => 'general', 'price' => '$349', 'buttonLabel' => 'Get General', 'url' => 'mailto:' . self::TICKETS_EMAIL, 'features' => ['All three days', 'All six tracks', 'After-party access']],
                ['name' => 'Pro', 'colorToken' => 'pro', 'price' => '$599', 'buttonLabel' => 'Get Pro', 'url' => 'mailto:' . self::TICKETS_EMAIL, 'features' => ['Everything in General', 'Workshop seats', 'Recorded session access']],
                ['name' => 'VIP', 'colorToken' => 'vip', 'price' => '$999', 'buttonLabel' => 'Get VIP', 'url' => 'mailto:' . self::TICKETS_EMAIL, 'features' => ['Everything in Pro', 'Speaker dinner', 'Front-row main stage seating']],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function countdownBandSection(string $heading, string $summary, string $deadline): array
    {
        return [
            'type' => 'countdown-band',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'band',
            'deadline' => $deadline,
            'actionLabel' => 'Get tickets',
            'actionUrl' => 'mailto:' . self::TICKETS_EMAIL,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function venueTravelSection(string $heading, string $summary): array
    {
        return [
            'type' => 'venue-travel-panels',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'panels',
            'panels' => [
                ['icon' => '📍', 'title' => 'Venue', 'summary' => 'Fábrica do Braço de Prata, Lisbon — a converted factory turned event hall in the Marvila district.', 'url' => null],
                ['icon' => '🚆', 'title' => 'Getting there', 'summary' => 'Metro to Braço de Prata station, then a five-minute walk. Onsite parking is limited; the metro is faster.', 'url' => null],
                ['icon' => '🏨', 'title' => 'Where to stay', 'summary' => 'A discounted block of rooms is held at three hotels within walking distance — the link is in your ticket confirmation email.', 'url' => null, 'linkLabel' => 'See partner hotels'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sponsorTierSection(string $heading, string $summary): array
    {
        return [
            'type' => 'sponsor-tier-walls',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'tiers',
            'tiers' => [
                [
                    'label' => 'Headline sponsors',
                    'colorToken' => 'headline',
                    'sponsors' => [
                        ['name' => 'Fieldform Systems', 'logo' => null, 'url' => '#'],
                        ['name' => 'Basecamp Tools', 'logo' => null, 'url' => '#'],
                    ],
                ],
                [
                    'label' => 'Supporting sponsors',
                    'colorToken' => 'supporting',
                    'sponsors' => [
                        ['name' => 'Loomwork Robotics', 'logo' => null, 'url' => '#'],
                        ['name' => 'Harborlight Cloud', 'logo' => null, 'url' => '#'],
                        ['name' => 'Driftwood Analytics', 'logo' => null, 'url' => '#'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function liveStateSection(string $state, string $heading, string $summary, string $actionLabel, string $actionUrl): array
    {
        return [
            'type' => 'live-now-replay-state',
            'state' => $state,
            'variant' => 'banner',
            'heading' => $heading,
            'summary' => $summary,
            'actionLabel' => $actionLabel,
            'actionUrl' => $actionUrl,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pastEditionsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'past-editions-archive',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'timeline',
            'editions' => [
                ['year' => '2025', 'title' => 'Nightcast Summit 2025', 'stat' => '480 attendees', 'summary' => 'The year Mara Okonkwo\'s hardware keynote sold out within an hour of doors opening.', 'url' => '#', 'linkLabel' => 'Watch the 2025 recap'],
                ['year' => '2024', 'title' => 'Nightcast Summit 2024', 'stat' => '310 attendees', 'summary' => 'The first year we added a dedicated hardware and robotics track.', 'url' => '#', 'linkLabel' => 'Watch the 2024 recap'],
                ['year' => '2023', 'title' => 'Nightcast Summit 2023', 'stat' => '190 attendees', 'summary' => 'The founding year, held in a single room above a Lisbon café.', 'url' => '#', 'linkLabel' => 'Watch the 2023 recap'],
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
                ['value' => '480', 'label' => 'Builders at the 2025 edition, up from 310 the year before.'],
                ['value' => '90', 'label' => 'Speakers across six tracks this year.'],
                ['value' => '4.9/5', 'label' => 'Average attendee rating across the last three editions.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary, string $primaryUrl, string $secondaryUrl, string $secondaryLabel): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'url' => $primaryUrl,
            'label' => 'Get tickets',
            'actions' => [
                ['label' => 'Get tickets', 'url' => $primaryUrl, 'style' => 'primary'],
                ['label' => $secondaryLabel, 'url' => $secondaryUrl, 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'items' => [
                ['label' => 'Agenda', 'url' => '/#agenda'],
                ['label' => 'Speakers', 'url' => '/#speakers'],
                ['label' => 'Tickets', 'url' => '/#tickets'],
                ['label' => 'Venue', 'url' => '/#venue'],
            ],
            'ctaLabel' => 'Get tickets',
            'ctaUrl' => '/theme-main-stage-cta',
            'consultationUrl' => '/theme-main-stage-contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'heading' => 'Event',
                'title' => 'Event',
                'links' => [
                    ['label' => 'Agenda', 'url' => '/#agenda'],
                    ['label' => 'Speakers', 'url' => '/#speakers'],
                    ['label' => 'Venue and travel', 'url' => '/#venue'],
                    ['label' => 'Past editions', 'url' => '/#past-editions'],
                ],
            ],
            [
                'heading' => 'Tickets',
                'title' => 'Tickets',
                'links' => [
                    ['label' => 'Compare ticket tiers', 'url' => '/#tickets'],
                    ['label' => 'Group tickets', 'url' => 'mailto:' . self::TICKETS_EMAIL],
                    ['label' => 'Sponsor the Summit', 'url' => '/theme-main-stage-contact'],
                ],
            ],
            [
                'heading' => 'Contact',
                'title' => 'Contact',
                'links' => [
                    ['label' => 'Contact the ticket desk', 'url' => '/theme-main-stage-contact'],
                    ['label' => self::TICKETS_EMAIL, 'url' => 'mailto:' . self::TICKETS_EMAIL],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'Three days, ninety speakers, six tracks — Nightcast Summit is the main stage for builders every October.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
