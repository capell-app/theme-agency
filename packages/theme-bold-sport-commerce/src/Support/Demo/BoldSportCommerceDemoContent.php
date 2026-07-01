<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\BoldSportCommerce\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Bold Sport Commerce theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (campaign-launch /
 * category-jumps / training-stories / community-offer / fit-specs / newsletter)
 * alongside the standard hero/proof/features/cta — giving every surface a full,
 * individual sport-commerce storefront rather than the shared skeleton.
 */
final class BoldSportCommerceDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Vanta Athletics';

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
            title: self::BRAND . ' — Performance Sportswear & Teamwear',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Performance sportswear built for the season ahead',
                'Vanta Athletics kits out runners, clubs, and weekend athletes with gear engineered to move as hard as they do.',
            ),
            renderData: [
                'summary' => 'Vanta Athletics is a performance sportswear and teamwear store. We drop seasonal kit, outfit whole clubs, and back every athlete chasing a PB.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Gear engineered to move as hard as you do',
                        'summary' => 'The Velocity autumn drop lands this week — race-day shoes, weatherproof layers, and club teamwear, all built for athletes who train through every season.',
                        'notes' => [
                            'New: Velocity autumn drop — limited first run',
                            'Free returns and 60-day fit guarantee on every order',
                            'Club teamwear with numbering and badges from 6 units',
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Vanta Athletics runner mid-stride in the Velocity kit',
                    ],
                    $this->campaignLaunchSection(
                        heading: 'The Velocity autumn drop',
                        summary: 'A focused seasonal release built to convert fast — race-day footwear, weatherproof layering, and the club kit your team has been waiting for.',
                    ),
                    $this->categoryJumpsSection(),
                    $this->featuresSection(
                        heading: 'This week\'s standout kit',
                        summary: 'The three pieces our athletes keep reaching for as the temperature drops.',
                    ),
                    $this->communityOfferSection(),
                    $this->trainingStoriesSection(),
                    $this->proofSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Shop the drop before it sells through',
                        summary: 'First runs go fast. Grab the Velocity kit now, or set a drop alert and we will tell you the moment the next batch lands.',
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
            name: self::BRAND . ' Shop',
            title: 'Shop all — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Shop the full Vanta range',
                'A product wall built to be scanned — footwear, layers, and teamwear across running, training, and club sport.',
            ),
            renderData: [
                'summary' => 'The full Vanta Athletics range — footwear, weatherproof layers, and club teamwear, built to be scanned fast and filtered hard.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Shop the full range',
                        'summary' => 'Every drop, every category, in one fast-loading product wall. Filter by sport, then dig into the spec on anything that catches your eye.',
                        'notes' => [
                            '320+ products across running, training, and club sport',
                            'Real-time stock — no sold-out surprises at checkout',
                            'Free 60-day returns on every order',
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Wall of Vanta Athletics product cards',
                    ],
                    $this->contentListingSection(
                        heading: 'A product wall built to be scanned',
                        summary: 'Bold, legible product cards keep the collection quick to read on any device.',
                    ),
                    $this->categoryJumpsSection(),
                    $this->fitSpecsSection(),
                    $this->ctaSection(
                        heading: 'Cannot find your size?',
                        summary: 'Set a back-in-stock alert and we will email you the second your size lands — no spam, just the restock.',
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
            name: self::BRAND . ' Product',
            title: 'Velocity Race Shoe — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Velocity Race Shoe — built for PB day',
                'A featherweight race-day shoe with a carbon plate and a 60-day fit guarantee, made for the runners chasing a personal best.',
            ),
            renderData: [
                'summary' => 'The Velocity Race Shoe — a featherweight, carbon-plated racer built for PB day, backed by our 60-day fit guarantee.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Velocity Race Shoe — built for PB day',
                        'summary' => 'A 198g carbon-plated racer with a responsive supercritical foam midsole. Engineered for the final 10k when every gram and every watt counts.',
                        'notes' => [
                            '£185 — free express delivery, ships today',
                            '198g in a UK 9 — carbon plate, supercritical foam',
                            '60-day fit guarantee: race it, then decide',
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Velocity Race Shoe side profile',
                    ],
                    $this->fitSpecsSection(),
                    $this->featuresSection(
                        heading: 'Complete the race-day kit',
                        summary: 'What our athletes pair with the Velocity on PB day.',
                    ),
                    $this->trainingStoriesSection(),
                    $this->ctaSection(
                        heading: 'Lace up for your next PB',
                        summary: 'Add the Velocity to your bag now — ships today with free express delivery and a 60-day fit guarantee.',
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
            name: self::BRAND . ' Support',
            title: 'Help & drop alerts — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Talk to the Vanta team',
                'Fit advice, club teamwear quotes, and drop alerts — reach a real human who actually trains in the kit.',
            ),
            renderData: [
                'summary' => 'Fit advice, club teamwear quotes, returns, and drop alerts — handled by a small team that actually trains in the kit.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Talk to the Vanta team',
                        'summary' => 'Sizing questions, club orders, or a return — email help@vanta-athletics.example and a real person replies within one working day.',
                        'notes' => [
                            'Live chat weekdays, 8am–8pm UK time',
                            'Club & teamwear quotes within 24 hours',
                            'Free 60-day returns, no questions asked',
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Vanta Athletics support team at the counter',
                    ],
                    $this->newsletterSection(),
                    $this->communityOfferSection(),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Kitting out a whole club?',
                        summary: 'Send us your squad size and colours and we will quote numbering, badges, and bulk pricing within a day.',
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
            title: 'Nothing in stock for that filter — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing matches that filter yet',
                'A graceful empty state for a filtered product wall with no matching stock.',
            ),
            renderData: [
                'summary' => 'Nothing in stock matches that filter right now — but here is where to look next, and how to get told the moment it lands.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Nothing in stock for that filter — yet',
                        'summary' => 'That size and colour combination has sold through. Clear a filter to widen the search, or set a drop alert and we will tell you the moment it restocks.',
                        'notes' => [
                            'Try widening the colour or size filter',
                            'Set a back-in-stock alert below',
                            'Browse the categories that are stocked right now',
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Empty Vanta Athletics product wall',
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing to show for this filter',
                        'summary' => 'When stock in this category lands it will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->categoryJumpsSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Want this the moment it restocks?',
                        summary: 'Set a back-in-stock alert and skip the refresh button — we will email you the instant your size lands.',
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
                'This page went out of stock',
                'A not-found page that routes shoppers back into the categories, the latest drop, and support.',
            ),
            renderData: [
                'summary' => 'That page has moved or sold through — here is the fast way back to the kit.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'This page went out of stock',
                        'summary' => 'The link is broken or the product has retired. Jump back to the latest drop, browse a category, or head home.',
                        'notes' => [
                            'Back to the Velocity autumn drop',
                            'Browse running, training, and club sport',
                            'Need a hand? The support team is one click away',
                        ],
                    ],
                    $this->categoryJumpsSection(),
                    $this->ctaSection(
                        heading: 'Back to the good stuff',
                        summary: 'Head to the latest drop, or tell us what you were after and we will point you to the right rail.',
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
            name: self::BRAND . ' Drop Alert',
            title: 'Join the drop alert — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Never miss a Vanta drop',
                'A focused conversion page inviting shoppers to join the drop alert and shop the season ahead of everyone else.',
            ),
            renderData: [
                'summary' => 'Join the drop alert and get first access to every Vanta release before it sells through.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'heading' => 'Never miss a Vanta drop',
                        'summary' => 'Drop-alert members get a 24-hour head start on every release, early sizes, and members-only colourways. First runs are limited — this is how you stay ahead.',
                        'notes' => [
                            '24-hour early access to every drop',
                            'Members-only colourways and sizes',
                            'One email per drop — never more',
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Vanta Athletics drop launch',
                    ],
                    $this->newsletterSection(),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Get first access to the next drop',
                        summary: 'Join the drop alert now and shop the Velocity restock a full day before everyone else.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function campaignLaunchSection(string $heading, string $summary): array
    {
        return [
            'type' => 'campaign-launch',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Velocity race-day footwear',
                    'summary' => 'The carbon-plated Velocity racer and its everyday trainer sibling, built for runners chasing a personal best this autumn.',
                ],
                [
                    'title' => 'Stormshell weatherproof layers',
                    'summary' => 'Packable, fully taped jackets and thermal mid-layers that keep training honest when the weather turns.',
                ],
                [
                    'title' => 'Club teamwear, ready to number',
                    'summary' => 'Match kits, training tops, and warm-ups in your colours, with numbering and badges from just six units.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryJumpsSection(): array
    {
        return [
            'type' => 'category-jumps',
            'heading' => 'Jump straight to your sport',
            'items' => [
                [
                    'title' => 'Running',
                    'summary' => 'Race-day shoes, daily trainers, and reflective layers for the dark mornings.',
                ],
                [
                    'title' => 'Training',
                    'summary' => 'Gym kit, lifting shoes, and breathable base layers built to be sweated in.',
                ],
                [
                    'title' => 'Club sport',
                    'summary' => 'Match kits, boots, and teamwear for football, rugby, hockey, and netball.',
                ],
                [
                    'title' => 'Recovery',
                    'summary' => 'Compression, slides, and warm-down layers for the day after the hard session.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function communityOfferSection(): array
    {
        return [
            'type' => 'community-offer',
            'heading' => 'Built for clubs, not just individuals',
            'summary' => 'Kitting out a squad? Vanta Club gives your whole team one place to order, with numbering, badges, and pricing that scales as you grow.',
            'label' => 'Start a club order',
            'url' => '#contact',
            'items' => [
                [
                    'title' => 'Vanta Club for teams',
                    'summary' => 'One shared storefront for the squad — numbering, badges, and bulk pricing from six units up.',
                ],
                [
                    'title' => 'Members get more',
                    'summary' => 'Free returns, early drop access, and a points-back reward on every order once you join.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function trainingStoriesSection(): array
    {
        return [
            'type' => 'training-stories',
            'heading' => 'Worn where it counts',
            'items' => [
                [
                    'meta' => 'Marathon',
                    'title' => 'A first sub-3 in the Velocity',
                    'summary' => 'Club runner Mara Adeyemi took 14 minutes off her marathon PB racing in the Velocity through a wet, windy autumn London.',
                ],
                [
                    'meta' => 'Club rugby',
                    'title' => 'Kitting out Southside RFC',
                    'summary' => 'Forty players, three age grades, one order. Southside RFC ran the whole season in Vanta teamwear with custom numbering.',
                ],
                [
                    'meta' => 'Trail',
                    'title' => 'All-weather in the Stormshell',
                    'summary' => 'Ultra runner Theo Marsh logged 1,200 winter miles in the Stormshell jacket without once cutting a session for the weather.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fitSpecsSection(): array
    {
        return [
            'type' => 'fit-specs',
            'heading' => 'Get the fit right the first time',
            'summary' => 'Every product ships with a true-to-size guide, and the 60-day fit guarantee means you can train in it before you commit.',
            'items' => [
                [
                    'title' => 'True-to-size fit',
                    'summary' => 'Detailed size charts and athlete fit notes on every page — most runners take their usual size.',
                ],
                [
                    'title' => 'Colours that last',
                    'summary' => 'Sweat- and wash-tested pigments that hold their colour through a full season of training.',
                ],
                [
                    'title' => '60-day fit guarantee',
                    'summary' => 'Train in it for up to 60 days. If the fit is not right, return it free — worn or not.',
                ],
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
                [
                    'title' => 'Velocity Race Shoe',
                    'summary' => 'A 198g carbon-plated racer with supercritical foam — the fastest shoe we have ever made.',
                    'meta' => 'Running · £185',
                    'care_note' => 'Air-dry only · do not machine wash',
                ],
                [
                    'title' => 'Stormshell Jacket',
                    'summary' => 'Fully taped, packable, and reflective. Keeps the rain out without trapping the heat in.',
                    'meta' => 'Layers · £120',
                    'care_note' => 'Machine wash cold · re-proof yearly',
                ],
                [
                    'title' => 'Club Match Kit',
                    'summary' => 'Breathable, snag-resistant teamwear ready for numbering, badges, and a full season of fixtures.',
                    'meta' => 'Teamwear · from £38',
                    'care_note' => 'Machine wash cold · do not iron print',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(): array
    {
        return [
            'type' => 'proof',
            'heading' => 'Why athletes shop Vanta',
            'summary' => 'The numbers behind the kit.',
            'items' => [
                ['value' => '60 days', 'label' => 'Fit guarantee — train in it, then decide'],
                ['value' => '4.8/5', 'label' => 'Average rating across 12,000+ verified reviews'],
                ['value' => '300+', 'label' => 'Clubs kitted out in the last season alone'],
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
                [
                    'category' => 'Running',
                    'title' => 'Velocity Race Shoe',
                    'summary' => 'Carbon-plated racer, 198g, built for PB day. Free express delivery.',
                ],
                [
                    'category' => 'Layers',
                    'title' => 'Stormshell Jacket',
                    'summary' => 'Taped, packable, reflective. The session goes ahead whatever the forecast.',
                ],
                [
                    'category' => 'Training',
                    'title' => 'Core Base Layer',
                    'summary' => 'Breathable, seamless, and built to be sweated in day after day.',
                ],
                [
                    'category' => 'Teamwear',
                    'title' => 'Club Match Kit',
                    'summary' => 'Match-ready teamwear, numbered and badged, from six units up.',
                ],
                [
                    'category' => 'Recovery',
                    'title' => 'Warm-Down Joggers',
                    'summary' => 'Soft, warm, and roomy — exactly what the day after a hard session needs.',
                ],
                [
                    'category' => 'Footwear',
                    'title' => 'Daily Trainer',
                    'summary' => 'The cushioned everyday shoe that racks up the easy miles between races.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(): array
    {
        return [
            'type' => 'newsletter',
            'heading' => 'Join the drop alert',
            'summary' => 'Be first to every release with a 24-hour head start, early sizes, and members-only colourways. One email per drop, never more.',
            'action' => '#contact',
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
            'label' => 'Shop the latest drop',
            'url' => '#campaign-launch',
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
                ['label' => 'Shop', 'url' => '#campaign-launch'],
                ['label' => 'Running', 'url' => '#category-jumps'],
                ['label' => 'Training', 'url' => '#category-jumps'],
                ['label' => 'Club & teamwear', 'url' => '#community-offer'],
                ['label' => 'Stories', 'url' => '#training-stories'],
            ],
            'ctaLabel' => 'Join the drop alert',
            'ctaUrl' => '#contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'Performance sportswear and teamwear for runners, clubs, and weekend athletes. Built to move as hard as you do.',
            'columns' => [
                [
                    'heading' => 'Shop',
                    'links' => [
                        ['label' => 'Running', 'url' => '#category-jumps'],
                        ['label' => 'Training', 'url' => '#category-jumps'],
                        ['label' => 'Club sport', 'url' => '#community-offer'],
                        ['label' => 'Latest drop', 'url' => '#campaign-launch'],
                    ],
                ],
                [
                    'heading' => 'Service',
                    'links' => [
                        ['label' => 'Club & teamwear orders', 'url' => '#community-offer'],
                        ['label' => 'Shipping & returns', 'url' => '#contact'],
                        ['label' => 'Fit & sizing help', 'url' => '#contact'],
                        ['label' => '60-day fit guarantee', 'url' => '#contact'],
                    ],
                ],
                [
                    'heading' => 'Stay in the loop',
                    'links' => [
                        ['label' => 'Join the drop alert', 'url' => '#contact'],
                        ['label' => 'Training stories', 'url' => '#training-stories'],
                        ['label' => 'help@vanta-athletics.example', 'url' => 'mailto:help@vanta-athletics.example'],
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
