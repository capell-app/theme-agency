<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Restaurant\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Restaurant theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature hospitality renderers (menu-highlights
 * / reservation-panel / private-dining / events-calendar / chef-story /
 * opening-hours / location-guide) alongside the standard hero/proof/cta — giving
 * every surface a full, individual restaurant site rather than the shared
 * five-section skeleton.
 */
final class RestaurantDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'The Greenhouse Table';

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
            title: self::BRAND . ' — Seasonal Dining & Reservations',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A neighbourhood table worth booking ahead',
                'The Greenhouse Table is a seasonal kitchen and bar serving a daily-changing menu, private dining, and a calendar of supper clubs.',
            ),
            renderData: [
                'summary' => 'A seasonal kitchen and bar. We cook what the market gives us, pour natural wine, and keep a few tables back for walk-ins.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Seasonal kitchen & bar',
                        'heading' => 'A neighbourhood table worth booking ahead',
                        'summary' => 'A daily-changing menu built around the morning market, a low-intervention wine list, and a dining room that fills up fast. Reserve a table or sit at the bar.',
                        'actions' => [
                            ['label' => 'Reserve a table', 'url' => '#reservations', 'style' => 'primary'],
                            ['label' => 'View the menu', 'url' => '#menu', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'The Greenhouse Table dining room',
                    ],
                    $this->menuHighlightsSection(
                        heading: 'On the menu this week',
                        summary: 'The carte changes with the market, but a few plates have earned a permanent place. Prices are per dish, with a daily set lunch at noon.',
                    ),
                    $this->reservationSection(
                        heading: 'Book a table in one confident path',
                        summary: 'Choose your date, party size, and sitting. Larger groups and last-minute tables are best handled by the team — call us and we will make it work.',
                    ),
                    $this->chefStorySection($media),
                    $this->privateDiningSection(
                        heading: 'Host a private table',
                        summary: 'Two spaces for celebrations, tastings, and team dinners — each with its own set menu and a dedicated host for the evening.',
                    ),
                    $this->eventsSection(
                        heading: 'Suppers, tastings & residencies',
                        summary: 'A monthly calendar of guest chefs, wine flights, and late-night kitchen takeovers.',
                    ),
                    $this->featuresSection(
                        heading: 'What an evening here feels like',
                        summary: 'The details we obsess over so the dining room never has to think about them.',
                    ),
                    $this->proofSection(
                        heading: 'Earned the regulars',
                        summary: 'A few numbers from our first three years on the corner.',
                    ),
                    $this->ctaSection(
                        heading: 'Hungry already? Hold your table',
                        summary: 'We keep a handful of tables back each service, but weekends book out a fortnight ahead. Reserve now and we will have a seat waiting.',
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
            name: self::BRAND . ' Menu',
            title: 'The Menu — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The full menu',
                'Small plates, larger plates, and a daily set lunch — all built around the morning market and changing with the season.',
            ),
            renderData: [
                'summary' => 'The full carte, the set lunch, and the bar snacks. Everything is cooked to order, so dishes sell out — the kitchen will steer you to what is best on the night.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'The menu',
                        'heading' => 'Small plates, big plates & the daily set lunch',
                        'summary' => 'Read the kitchen in full. The carte changes most days, so treat this as a taste of the house style rather than a fixed list.',
                        'actions' => [
                            ['label' => 'Reserve a table', 'url' => '#reservations', 'style' => 'primary'],
                            ['label' => 'See opening hours', 'url' => '#hours', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Plates from the kitchen',
                    ],
                    $this->menuHighlightsSection(
                        heading: 'From the carte',
                        summary: 'A representative slice of the kitchen. Dishes rotate with the market; ask the team what landed this morning.',
                    ),
                    $this->featuresSection(
                        heading: 'How we cook',
                        summary: 'The standards behind every plate that leaves the pass.',
                    ),
                    $this->openingHoursSection(),
                    $this->ctaSection(
                        heading: 'Seen something you want to eat?',
                        summary: 'Tables for tonight and the week ahead are open now. Book online or call the team for anything last-minute.',
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
            name: self::BRAND . ' Kitchen Story',
            title: 'In the kitchen with Maya Oduya — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'In the kitchen with Maya Oduya',
                'How our head chef builds a menu around a single morning at the market, and why the list changes by lunch.',
            ),
            renderData: [
                'summary' => 'A morning at the market, an afternoon at the pass, and a menu that did not exist yesterday. Head chef Maya Oduya on cooking the season.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'In the kitchen',
                        'heading' => 'A menu that did not exist yesterday',
                        'summary' => 'Head chef Maya Oduya shops the market at six, writes the carte by ten, and serves it by noon. Here is how a single morning becomes an evening at the table.',
                        'actions' => [
                            ['label' => 'Reserve a table', 'url' => '#reservations', 'style' => 'primary'],
                            ['label' => 'Back to the menu', 'url' => '#menu', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Head chef plating at the pass',
                    ],
                    $this->chefStorySection($media),
                    $this->menuHighlightsSection(
                        heading: 'What that morning became',
                        summary: 'The plates that came out of one trip to the market — gone by the end of service, replaced by tomorrow.',
                    ),
                    $this->eventsSection(
                        heading: 'Cook alongside the kitchen',
                        summary: 'Tastings and supper clubs where Maya and the team open up the pass.',
                    ),
                    $this->ctaSection(
                        heading: 'Taste the season for yourself',
                        summary: 'The menu you read today will be gone tomorrow. Book a table and let the kitchen cook what the market gave us.',
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
            name: self::BRAND . ' Reservations',
            title: 'Reservations & find us — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reserve, or just find us',
                'Book a table, ask about a private event, or get directions to the dining room on the corner of Provender Lane.',
            ),
            renderData: [
                'summary' => 'Book online, call the team, or email about a private event. We are on the corner of Provender Lane — two minutes from the station and open six days a week.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Reservations',
                        'heading' => 'Reserve a table, or just find us',
                        'summary' => 'Book online for parties of one to six. For larger groups, private dining, or anything last-minute, call us on 020 7946 0123 or email hello@greenhousetable.example.',
                        'actions' => [
                            ['label' => 'Book online', 'url' => '#reservations', 'style' => 'primary'],
                            ['label' => 'Email the team', 'url' => 'mailto:hello@greenhousetable.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The dining room from the street',
                    ],
                    $this->reservationSection(
                        heading: 'Pick a date and we will set the table',
                        summary: 'Reservations open thirty days ahead. Weekend tables go quickly, so book early — and tell us if you are celebrating anything.',
                    ),
                    $this->openingHoursSection(),
                    $this->locationGuideSection(),
                    $this->ctaSection(
                        heading: 'Planning something bigger?',
                        summary: 'Private dining, set menus, and full venue hire are handled by our events team. Tell us the date and the headcount and we will build a plan.',
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
            name: self::BRAND . ' No Tables',
            title: 'No tables for that date — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Fully booked for that sitting',
                'A graceful no-availability state offering the bar, the waitlist, and nearby dates when a chosen sitting is full.',
            ),
            renderData: [
                'summary' => 'That sitting is fully booked — but the bar takes walk-ins, the waitlist moves fast, and there is usually a table a night either side.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Reservations',
                        'heading' => 'That sitting is fully booked',
                        'summary' => 'We are at capacity for the date and time you chose. Join the waitlist, try a nearby evening, or come sit at the bar — we always keep a few stools back.',
                        'actions' => [
                            ['label' => 'Try another date', 'url' => '#reservations', 'style' => 'primary'],
                            ['label' => 'Join the waitlist', 'url' => '#reservations', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'menu-highlights',
                        'heading' => 'While you decide on a date',
                        'summary' => 'Here is what the kitchen is cooking this week — a good reason to keep trying.',
                        'items' => [],
                    ],
                    $this->openingHoursSection(),
                    $this->ctaSection(
                        heading: 'Want us to call when a table opens?',
                        summary: 'Cancellations happen most evenings. Join the waitlist and we will text the moment a table on your date frees up.',
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
                'This page is off the menu',
                'A not-found page that routes hungry visitors back to the menu and the reservation book.',
            ),
            renderData: [
                'summary' => 'That page is off the menu — but the kitchen is open. Here is the way back to a table.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page is off the menu',
                        'summary' => 'The link is stale or the page has moved. Head back to the dining room — the menu and the reservation book are where you left them.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View the menu', 'url' => '#menu', 'style' => 'secondary'],
                        ],
                    ],
                    $this->openingHoursSection(),
                    $this->ctaSection(
                        heading: 'Came here to book a table?',
                        summary: 'The reservation book is one tap away. Pick a date and we will have a seat waiting.',
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
            name: self::BRAND . ' Book',
            title: 'Book a table — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Hold your table',
                'A focused conversion page inviting diners to reserve a table or enquire about a private event.',
            ),
            renderData: [
                'summary' => 'Hold your table at The Greenhouse Table. Weekends book a fortnight ahead — reserve now and we will have a seat waiting.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Reservations',
                        'heading' => 'Hold your table tonight',
                        'summary' => 'A daily-changing menu, a dining room that fills up fast, and a few tables kept back for the people who plan ahead. Reserve in under a minute.',
                        'actions' => [
                            ['label' => 'Reserve a table', 'url' => '#reservations', 'style' => 'primary'],
                            ['label' => 'Email the team', 'url' => 'mailto:hello@greenhousetable.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'A laid table at The Greenhouse Table',
                    ],
                    $this->reservationSection(
                        heading: 'Choose your sitting',
                        summary: 'Lunch from noon, dinner from half past five. Pick the evening that suits and the kitchen will do the rest.',
                    ),
                    $this->proofSection(
                        heading: 'Why diners keep coming back',
                        summary: 'The numbers behind a room that books out most weekends.',
                    ),
                    $this->ctaSection(
                        heading: 'One table away from a good night',
                        summary: 'Reserve now and we will hold your seat. Plans change? Cancel free up to four hours before your sitting.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function menuHighlightsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'menu-highlights',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Stone bass crudo, fennel & blood orange', 'summary' => 'Day-boat fish cured in citrus, dressed with new-season olive oil and wild fennel.', 'price' => '16'],
                ['title' => 'Hand-rolled cavatelli, nettle & aged pecorino', 'summary' => 'Foraged nettles from the morning market, brown butter, and a generous shower of pecorino.', 'price' => '19'],
                ['title' => 'Dry-aged Hereford rib, smoked bone marrow', 'summary' => 'Thirty-day-aged rib over embers, marrow butter, and burnt-onion jus. For two to share.', 'price' => '34'],
                ['title' => 'Roast cauliflower, brown butter & capers', 'summary' => 'Whole roasted in the wood oven, finished with toasted hazelnuts and a caper crumb.', 'price' => '14'],
                ['title' => 'Set lunch — three courses', 'summary' => 'A market menu that changes daily, served noon to two, Tuesday through Friday.', 'price' => '26'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function reservationSection(string $heading, string $summary): array
    {
        return [
            'type' => 'reservation-panel',
            'heading' => $heading,
            'summary' => $summary,
            'form_action' => '#reservations',
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function chefStorySection(array $media): array
    {
        return [
            'type' => 'chef-story',
            'heading' => 'Maya Oduya cooks the morning, not the menu',
            'summary' => 'Our head chef trained between Lyon and Lisbon before bringing a market-led kitchen home. She shops at dawn, writes the carte by mid-morning, and lets the season set the pace. Nothing is on the menu because it always has been — only because it was good this week.',
            'mediaUrl' => $media['detail'][0] ?? $media['hero'][0],
            'mediaAlt' => 'Head chef Maya Oduya in the kitchen',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function privateDiningSection(string $heading, string $summary): array
    {
        return [
            'type' => 'private-dining',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'The Glasshouse', 'summary' => 'A light-filled room off the main dining room, with its own bar and a set tasting menu for celebrations.', 'metric' => '18'],
                ['title' => 'Full venue hire', 'summary' => 'The whole restaurant, the open kitchen, and a dedicated team for launches, weddings, and long lunches.', 'metric' => '60'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function eventsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'events-calendar',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Guest chef: Ottoline Reyes', 'date' => 'Thu 12 Sep · 7pm', 'summary' => 'A five-course Basque tasting menu cooked alongside our kitchen, paired with txakoli and cider.'],
                ['title' => 'Natural wine flight & cheese', 'date' => 'Wed 25 Sep · 6.30pm', 'summary' => 'Six low-intervention pours from small growers, matched with a board from our cheesemonger.'],
                ['title' => 'Late kitchen: pasta & vinyl', 'date' => 'Fri 4 Oct · 10pm', 'summary' => 'Hand-rolled pasta, amaro, and records until one. Walk-ins only, bar seating.'],
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
            'features' => [
                ['title' => 'Market-led, daily', 'description' => 'The carte is written each morning around what the market and our growers send. No two weeks taste the same.'],
                ['title' => 'Low-intervention cellar', 'description' => 'A list that leans on small growers and natural wine, with by-the-glass pours that change as fast as the food.'],
                ['title' => 'Open kitchen, no theatre', 'description' => 'Sit at the pass and watch the team work. The cooking is the show; we keep everything else quiet.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function openingHoursSection(): array
    {
        return [
            'type' => 'opening-hours',
            'heading' => 'When we are open',
            'items' => [
                ['title' => 'Lunch · Tue–Fri', 'summary' => '12:00 – 14:30'],
                ['title' => 'Dinner · Tue–Sat', 'summary' => '17:30 – 22:30'],
                ['title' => 'Bar · Tue–Sat', 'summary' => '16:00 – late'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function locationGuideSection(): array
    {
        return [
            'type' => 'location-guide',
            'heading' => 'Find the dining room',
            'summary' => 'On the corner of Provender Lane, two minutes from the station and a short walk from the canal. Look for the green awning and the glasshouse window.',
            'items' => [
                ['title' => 'By train', 'summary' => 'Two minutes from Provender Cross station — exit towards the market and turn left at the canal bridge.'],
                ['title' => 'Parking', 'summary' => 'Evening parking on Lane Street after 6pm; the Market Square car park is a four-minute walk.'],
                ['title' => 'Step-free access', 'summary' => 'Level entry from Provender Lane, an accessible WC, and a ground-floor private room on request.'],
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
                ['metric' => '4.8', 'name' => 'Average guest rating', 'summary' => 'Across more than two thousand reviews since we opened the doors.'],
                ['metric' => '36', 'name' => 'Covers a night', 'summary' => 'A deliberately small room, so every table gets the kitchen at its best.'],
                ['metric' => '90%', 'name' => 'Weekend tables rebooked', 'summary' => 'Nine in ten weekend diners leave with their next reservation already made.'],
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
                ['label' => 'Reserve a table', 'url' => '#reservations', 'style' => 'primary'],
                ['label' => 'View the menu', 'url' => '#menu', 'style' => 'secondary'],
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
                ['label' => 'Menu', 'url' => '#menu'],
                ['label' => 'Reservations', 'url' => '#reservations'],
                ['label' => 'Private dining', 'url' => '#private-dining'],
                ['label' => 'Events', 'url' => '#events'],
                ['label' => 'Find us', 'url' => '#location'],
            ],
            'reservationUrl' => '#reservations',
            'ctaLabel' => 'Reserve a table',
            'ctaUrl' => '#reservations',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A seasonal kitchen and bar on the corner of Provender Lane. Cooking the market, six days a week.',
            'columns' => [
                [
                    'heading' => 'Dine',
                    'links' => [
                        ['label' => 'The menu', 'url' => '#menu'],
                        ['label' => 'Set lunch', 'url' => '#menu'],
                        ['label' => 'Reservations', 'url' => '#reservations'],
                        ['label' => 'Opening hours', 'url' => '#hours'],
                    ],
                ],
                [
                    'heading' => 'Host',
                    'links' => [
                        ['label' => 'Private dining', 'url' => '#private-dining'],
                        ['label' => 'Venue hire', 'url' => '#private-dining'],
                        ['label' => 'Events calendar', 'url' => '#events'],
                    ],
                ],
                [
                    'heading' => 'Visit',
                    'links' => [
                        ['label' => 'Find us', 'url' => '#location'],
                        ['label' => 'hello@greenhousetable.example', 'url' => 'mailto:hello@greenhousetable.example'],
                        ['label' => '020 7946 0123', 'url' => 'tel:+442079460123'],
                    ],
                ],
            ],
            'items' => [
                ['label' => 'Menu', 'url' => '#menu'],
                ['label' => 'Reservations', 'url' => '#reservations'],
                ['label' => 'Private dining', 'url' => '#private-dining'],
                ['label' => 'Events', 'url' => '#events'],
                ['label' => 'Find us', 'url' => '#location'],
            ],
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
