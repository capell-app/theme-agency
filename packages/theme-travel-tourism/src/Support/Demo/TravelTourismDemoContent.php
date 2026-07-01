<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\TravelTourism\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Travel & Tourism theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (destination-grid /
 * itinerary-builder / guide-profiles / trip-inclusions / enquiry-panel)
 * alongside the standard hero/features/proof/cta — giving every surface a full
 * travel operator site rather than the shared five-section skeleton.
 *
 * Copy and brand tokens are mined verbatim from the theme's screenshot
 * renderer (Wayfarer Journeys).
 */
final class TravelTourismDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Wayfarer Journeys';

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
            title: self::BRAND . ' — Small-Group & Tailor-Made Journeys',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Small-group and tailor-made journeys worth the time',
                'A premium travel homepage for destinations, itineraries, local guides, inclusions, and enquiry-led journeys.',
            ),
            renderData: [
                'summary' => 'A premium travel homepage for destinations, itineraries, local guides, inclusions, and enquiry-led journeys.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Travel & Tourism',
                        'heading' => 'Small-group and tailor-made journeys worth the time',
                        'summary' => 'A premium travel homepage for destinations, itineraries, local guides, inclusions, and enquiry-led journeys.',
                        'actions' => [
                            ['label' => 'Start an enquiry', 'url' => '#enquiry', 'style' => 'primary'],
                            ['label' => 'Explore destinations', 'url' => '#destinations', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Wayfarer Journeys small-group travel',
                    ],
                    $this->destinationGridSection(
                        heading: 'Explore destinations worth the journey',
                        summary: 'A premium destination grid keeps regions scannable without owning destination records.',
                    ),
                    $this->itineraryBuilderSection(
                        heading: 'Build an itinerary that breathes',
                        summary: 'Day-by-day pacing and inclusions stay editorial and premium across every trip.',
                    ),
                    $this->guideProfilesSection(
                        heading: 'Local guides who know the back roads',
                        summary: 'Every journey is led by a resident expert, not a clipboard.',
                    ),
                    $this->tripInclusionsSection(
                        heading: 'What every journey includes',
                        summary: 'Clear inclusions so a traveller knows exactly what the trip covers.',
                    ),
                    $this->featuresSection(
                        heading: 'Why travellers choose Wayfarer',
                        summary: 'The details that make a journey feel considered from the first email to the last sunset.',
                    ),
                    $this->proofSection(
                        heading: 'Proof from the road',
                        summary: 'What the last few seasons of journeys delivered.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to start planning your journey?',
                        summary: 'A confident conversion moment pairs proof and a single enquiry path.',
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
            name: self::BRAND . ' Journeys',
            title: 'Browse journeys — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Browse every journey in one scannable directory',
                'Trips, regions, and seasons stay legible and premium without the theme owning travel records.',
            ),
            renderData: [
                'summary' => 'Trips, regions, and seasons stay legible and premium without the theme owning travel records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Journeys',
                        'heading' => 'Browse every journey in one scannable directory',
                        'summary' => 'Trips, regions, and seasons stay legible and premium without the theme owning travel records.',
                        'actions' => [
                            ['label' => 'Start an enquiry', 'url' => '#enquiry', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Wayfarer Journeys directory',
                    ],
                    $this->contentListingSection(
                        heading: 'Browse every journey in one scannable directory',
                        summary: 'Trips, regions, and seasons stay legible and premium without the theme owning travel records.',
                    ),
                    $this->destinationGridSection(
                        heading: 'Explore destinations worth the journey',
                        summary: 'A premium destination grid keeps regions scannable without owning destination records.',
                    ),
                    $this->featuresSection(
                        heading: 'Why travellers choose Wayfarer',
                        summary: 'The details that make a journey feel considered from the first email to the last sunset.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to start planning your journey?',
                        summary: 'A confident conversion moment pairs proof and a single enquiry path.',
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
            name: self::BRAND . ' Itinerary',
            title: 'A single journey, day by day — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A single journey told day by day',
                'Pacing, inclusions, and local guides come together so a traveller can picture the whole trip.',
            ),
            renderData: [
                'summary' => 'Pacing, inclusions, and local guides come together so a traveller can picture the whole trip.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Itinerary',
                        'heading' => 'A single journey told day by day',
                        'summary' => 'Pacing, inclusions, and local guides come together so a traveller can picture the whole trip.',
                        'actions' => [
                            ['label' => 'Start an enquiry', 'url' => '#enquiry', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Wayfarer Journeys itinerary',
                    ],
                    $this->itineraryBuilderSection(
                        heading: 'A single journey told day by day',
                        summary: 'Pacing, inclusions, and local guides come together so a traveller can picture the whole trip.',
                    ),
                    $this->tripInclusionsSection(
                        heading: 'What every journey includes',
                        summary: 'Clear inclusions so a traveller knows exactly what the trip covers.',
                    ),
                    $this->guideProfilesSection(
                        heading: 'Local guides who know the back roads',
                        summary: 'Every journey is led by a resident expert, not a clipboard.',
                    ),
                    $this->proofSection(
                        heading: 'Proof from the road',
                        summary: 'What the last few seasons of journeys delivered.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to start planning your journey?',
                        summary: 'A confident conversion moment pairs proof and a single enquiry path.',
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
            name: self::BRAND . ' Enquiry',
            title: 'Start a conversation — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Start a conversation about your trip',
                'A non-submitting enquiry CTA proves the contact journey feels like part of the brand experience.',
            ),
            renderData: [
                'summary' => 'A non-submitting enquiry CTA proves the contact journey feels like part of the brand experience.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Enquiry',
                        'heading' => 'Start a conversation about your trip',
                        'summary' => 'A non-submitting enquiry CTA proves the contact journey feels like part of the brand experience.',
                        'actions' => [
                            ['label' => 'Start an enquiry', 'url' => '#enquiry', 'style' => 'primary'],
                            ['label' => 'Explore destinations', 'url' => '#destinations', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Wayfarer Journeys enquiry',
                    ],
                    $this->enquiryPanelSection(
                        heading: 'Start a conversation about your trip',
                        summary: 'A non-submitting enquiry CTA proves the contact journey feels like part of the brand experience.',
                    ),
                    $this->proofSection(
                        heading: 'Proof from the road',
                        summary: 'What the last few seasons of journeys delivered.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to start planning your journey?',
                        summary: 'A confident conversion moment pairs proof and a single enquiry path.',
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
            name: self::BRAND . ' No Journeys',
            title: 'No journeys match yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No journeys match yet',
                'An empty directory state stays on-brand and points travellers toward an enquiry instead of a dead end.',
            ),
            renderData: [
                'summary' => 'An empty directory state stays on-brand and points travellers toward an enquiry instead of a dead end.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Journeys',
                        'heading' => 'No journeys match yet',
                        'summary' => 'An empty directory state stays on-brand and points travellers toward an enquiry instead of a dead end.',
                        'actions' => [
                            ['label' => 'Explore destinations', 'url' => '#destinations', 'style' => 'primary'],
                            ['label' => 'Start an enquiry', 'url' => '#enquiry', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'No journeys match yet',
                        'summary' => 'When journeys match this filter they will appear here, newest season first.',
                        'items' => [],
                    ],
                    $this->destinationGridSection(
                        heading: 'Explore destinations worth the journey',
                        summary: 'A premium destination grid keeps regions scannable without owning destination records.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready to start planning your journey?',
                        summary: 'A confident conversion moment pairs proof and a single enquiry path.',
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
                'This page wandered off the map',
                'A branded 404 keeps the traveller moving back toward destinations and itineraries.',
            ),
            renderData: [
                'summary' => 'A branded 404 keeps the traveller moving back toward destinations and itineraries.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page wandered off the map',
                        'summary' => 'A branded 404 keeps the traveller moving back toward destinations and itineraries.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Explore destinations', 'url' => '#destinations', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Ready to start planning your journey?',
                        summary: 'A confident conversion moment pairs proof and a single enquiry path.',
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
            name: self::BRAND . ' Plan',
            title: 'Start planning your journey — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to start planning your journey?',
                'A confident conversion moment pairs proof and a single enquiry path.',
            ),
            renderData: [
                'summary' => 'A confident conversion moment pairs proof and a single enquiry path.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Plan your journey',
                        'heading' => 'Ready to start planning your journey?',
                        'summary' => 'A confident conversion moment pairs proof and a single enquiry path.',
                        'actions' => [
                            ['label' => 'Start an enquiry', 'url' => '#enquiry', 'style' => 'primary'],
                            ['label' => 'Explore destinations', 'url' => '#destinations', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Wayfarer Journeys planning',
                    ],
                    $this->ctaSection(
                        heading: 'Ready to start planning your journey?',
                        summary: 'A confident conversion moment pairs proof and a single enquiry path.',
                    ),
                    $this->proofSection(
                        heading: 'Proof from the road',
                        summary: 'What the last few seasons of journeys delivered.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function destinationGridSection(string $heading, string $summary): array
    {
        return [
            'type' => 'destination-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Northern Highlands', 'summary' => 'Glacial valleys, fjord crossings, and long light evenings on a slow-paced loop.'],
                ['title' => 'Coastal Andalusia', 'summary' => 'Whitewashed villages, mountain passes, and harbour towns between two seas.'],
                ['title' => 'The Atlas & Beyond', 'summary' => 'High passes, Berber villages, and desert nights under an unbroken sky.'],
                ['title' => 'Aegean Islands', 'summary' => 'Quiet harbours and hill-top tavernas reached by ferry, not crowd.'],
                ['title' => 'Patagonian Frontier', 'summary' => 'Wind, ice, and wide horizons walked with a resident mountain guide.'],
                ['title' => 'Kyoto & the Inland Sea', 'summary' => 'Temple gardens, ryokan stays, and a country railway most tours skip.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function itineraryBuilderSection(string $heading, string $summary): array
    {
        return [
            'type' => 'itinerary-builder',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Day 1 · Arrival & welcome', 'summary' => 'Met at the airport, a slow first evening, and dinner with the group and your guide.'],
                ['title' => 'Day 2 · Into the landscape', 'summary' => 'A gentle first walk to find the pace, with time built in to stop and look.'],
                ['title' => 'Day 3 · The signature day', 'summary' => 'The journey\'s high point — a long route, a local lunch, and an unhurried return.'],
                ['title' => 'Day 4 · A day at your speed', 'summary' => 'Optional excursions or an open day to wander, rest, and follow your own thread.'],
                ['title' => 'Day 5 · Coast to country', 'summary' => 'A scenic transfer with stops that most itineraries drive straight past.'],
                ['title' => 'Day 6 · Farewell', 'summary' => 'A final morning, a relaxed breakfast, and onward travel arranged for you.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function guideProfilesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'guide-profiles',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Elena Marsh', 'summary' => 'Fifteen years leading mountain routes. Knows which valley catches the morning light.'],
                ['title' => 'Tomas Reyes', 'summary' => 'Born on the coast he now guides. Friends with every harbour cook on the route.'],
                ['title' => 'Amara Diallo', 'summary' => 'Desert specialist and storyteller. Reads the dunes the way others read a map.'],
                ['title' => 'Kenji Watanabe', 'summary' => 'Former ranger turned guide. Times every temple visit to miss the crowds.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function tripInclusionsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'trip-inclusions',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Resident local guide', 'summary' => 'Every journey is led end to end by an expert who lives in the region.'],
                ['title' => 'Hand-picked stays', 'summary' => 'Small, characterful hotels and guesthouses chosen for place, not points.'],
                ['title' => 'All transfers', 'summary' => 'Airport pickups and in-country transport arranged so you never chase a timetable.'],
                ['title' => 'Most meals', 'summary' => 'Breakfasts daily, plus the long lunches and dinners that make the trip.'],
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
                ['title' => 'Small groups only', 'summary' => 'Never more than twelve travellers, so the road stays personal.'],
                ['title' => 'Tailor-made on request', 'summary' => 'Shift a date, add a region, or build a private departure from scratch.'],
                ['title' => 'Carbon-aware planning', 'summary' => 'Slower routes, fewer flights, and offsets built into every booking.'],
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
                ['title' => '4,800 travellers', 'quote' => 'Guided across six continents since the first small-group departure.'],
                ['title' => '96% would return', 'quote' => 'Post-trip survey across last season\'s travellers.'],
                ['title' => '40 resident guides', 'quote' => 'Local experts who lead the journeys they grew up in.'],
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
                ['title' => 'Northern Highlands · 7 days', 'summary' => 'Fjords and glacial valleys at a walking pace, departing May to September.'],
                ['title' => 'Coastal Andalusia · 6 days', 'summary' => 'Mountain villages to harbour towns between two seas, spring and autumn.'],
                ['title' => 'The Atlas & Beyond · 9 days', 'summary' => 'High passes and desert nights with a Berber host, October to April.'],
                ['title' => 'Kyoto & the Inland Sea · 8 days', 'summary' => 'Temple gardens and country railways, timed for blossom or autumn colour.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function enquiryPanelSection(string $heading, string $summary): array
    {
        return [
            'type' => 'enquiry-panel',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Tell us your shape of trip', 'summary' => 'Dates, regions, pace, and who is travelling — a sentence is enough to start.'],
                ['title' => 'We reply within a day', 'summary' => 'A real travel designer answers, not an auto-responder or a booking form.'],
                ['title' => 'No deposit to talk', 'summary' => 'The first conversation is free and there is no obligation to book.'],
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
                ['label' => 'Start an enquiry', 'url' => '#enquiry', 'style' => 'primary'],
                ['label' => 'Explore destinations', 'url' => '#destinations', 'style' => 'secondary'],
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
                ['label' => 'Destinations', 'url' => '#destinations'],
                ['label' => 'Itineraries', 'url' => '#itineraries'],
                ['label' => 'Guides', 'url' => '#guides'],
                ['label' => 'Enquiry', 'url' => '#enquiry'],
            ],
            'ctaLabel' => 'Start an enquiry',
            'ctaUrl' => '#enquiry',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'Small-group and tailor-made journeys worth the time.',
            'columns' => [
                [
                    'heading' => 'Travel',
                    'links' => [
                        ['label' => 'Destinations', 'url' => '#destinations'],
                        ['label' => 'Itineraries', 'url' => '#itineraries'],
                        ['label' => 'Guides', 'url' => '#guides'],
                        ['label' => 'Inclusions', 'url' => '#inclusions'],
                    ],
                ],
                [
                    'heading' => 'Plan',
                    'links' => [
                        ['label' => 'Enquiry', 'url' => '#enquiry'],
                        ['label' => 'Tailor-made trips', 'url' => '#enquiry'],
                        ['label' => 'Private departures', 'url' => '#enquiry'],
                        ['label' => 'Travel advice', 'url' => '#guides'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Enquiry', 'url' => '#enquiry'],
                        ['label' => 'hello@wayfarerjourneys.example', 'url' => 'mailto:hello@wayfarerjourneys.example'],
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
