<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EstateAgents\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Estate Agents theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (property-search /
 * featured-properties / valuation-cta / local-guide / agent-team / market-proof
 * / viewing-request) alongside the standard hero/proof/features/cta — giving
 * every surface a full, individual estate-agency website rather than the shared
 * five-section skeleton.
 */
final class EstateAgentsDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Oak & Field';

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
            title: self::BRAND . ' — Estate Agents',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Move with a property team that knows the street',
                'Oak & Field is an independent estate agency built around search intent, honest valuations, local knowledge, and viewings that lead somewhere.',
            ),
            renderData: [
                'summary' => 'Oak & Field is an independent estate agency. We pair sharp property search with honest valuations, real local knowledge, and a team that answers the phone.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Estate agents',
                        'heading' => 'Move with a property team that knows the street',
                        'summary' => 'Search homes across the area, book a valuation in minutes, and view with agents who actually live where they sell. Premium service, no estate-agent theatre.',
                        'actions' => [
                            ['label' => 'Search homes', 'url' => '#property-search', 'style' => 'primary'],
                            ['label' => 'Book a valuation', 'url' => '#valuation', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'A bright period home for sale through Oak & Field',
                    ],
                    $this->propertySearchSection(
                        heading: 'Find the right home from the first screen',
                        summary: 'Filter by area, budget, and bedrooms. We keep the search honest — every result is a home our team has walked.',
                    ),
                    $this->featuredPropertiesSection(
                        heading: 'Featured homes this week',
                        summary: 'A handful of the homes we are proud to be selling right now.',
                    ),
                    $this->valuationCtaSection(
                        heading: 'Know what your home is really worth',
                        summary: 'Start with a postcode and get a considered, evidence-led valuation — not an inflated number designed to win the instruction.',
                    ),
                    $this->localGuideSection(
                        heading: 'Local knowledge you can actually use',
                        summary: 'Schools, commutes, and market context for the neighbourhoods we know best.',
                    ),
                    $this->agentTeamSection(
                        heading: 'The people who will sell your home',
                        summary: 'A small, senior team. You deal with the agent on the board, not a call centre.',
                    ),
                    $this->marketProofSection(
                        heading: 'Numbers from the last twelve months',
                        summary: 'The results behind the boards across our patch.',
                    ),
                    $this->proofSection(
                        heading: 'Why vendors and buyers choose Oak & Field',
                        summary: 'What people tell us once the sale completes.',
                    ),
                    $this->ctaSection(
                        heading: 'Thinking of selling or letting?',
                        summary: 'Book a valuation and we will give you an honest view of price, timing, and the best way to bring your home to market.',
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
            name: self::BRAND . ' Search',
            title: 'Property search — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every home we are selling, in one place',
                'Search the full Oak & Field listing book by area, budget, and bedrooms, then read the guides that help you move with confidence.',
            ),
            renderData: [
                'summary' => 'The full Oak & Field listing book — filter by area, budget, and bedrooms, and read the buyer guides and market reports that sit alongside them.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Property search',
                        'heading' => 'Every home we are selling, in one place',
                        'summary' => 'Browse the full listing book. Filter by area, budget, and bedrooms, save the ones you like, and book viewings straight from the result.',
                        'actions' => [
                            ['label' => 'Book a valuation', 'url' => '#valuation', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Oak & Field property search results',
                    ],
                    $this->propertySearchSection(
                        heading: 'Search every instruction from the first screen',
                        summary: 'A high-intent search band lets buyers filter by area, budget, and beds before they scroll a single result.',
                    ),
                    $this->featuredPropertiesSection(
                        heading: 'Featured homes',
                        summary: 'The homes drawing the most viewings this week.',
                    ),
                    $this->contentListingSection(
                        heading: 'Buyer guides and market reports',
                        summary: 'Evergreen guides, recent market notes, and local proof to support your search.',
                    ),
                    $this->ctaSection(
                        heading: 'Cannot see what you are after?',
                        summary: 'Tell us your area, budget, and must-haves and we will call you the moment the right home comes to market.',
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
            name: self::BRAND . ' Property',
            title: 'Maple House, Elm Quarter — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Maple House — a period home with room to grow',
                'A three-bedroom Victorian home with a south-facing garden, walking distance to the station and two outstanding schools.',
            ),
            renderData: [
                'summary' => 'A three-bedroom Victorian home with a south-facing garden, chain-free, and a short walk from the station and two outstanding schools.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'For sale · 725,000',
                        'heading' => 'Maple House — a period home with room to grow',
                        'summary' => 'Three bedrooms, a south-facing garden, and a kitchen built for Sunday lunches. Chain-free, and five minutes from the station.',
                        'actions' => [
                            ['label' => 'Request a viewing', 'url' => '#viewing', 'style' => 'primary'],
                            ['label' => 'Back to search', 'url' => '#property-search', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'The front elevation of Maple House',
                    ],
                    $this->featuredPropertiesSection(
                        heading: 'More homes like this',
                        summary: 'Other period homes in the same neighbourhood and price band.',
                    ),
                    $this->viewingRequestSection(
                        heading: 'Request a viewing at Maple House',
                        summary: 'Pick a time that suits you and an agent who knows the home will meet you there.',
                    ),
                    $this->localGuideSection(
                        heading: 'Living in Elm Quarter',
                        summary: 'What it is actually like to live on this street.',
                    ),
                    $this->ctaSection(
                        heading: 'Selling something similar?',
                        summary: 'Homes like Maple House move quickly with the right agent. Book a valuation and we will show you how we would price and present yours.',
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
            name: self::BRAND . ' Valuation',
            title: 'Book a valuation — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Book a valuation',
                'Start with your postcode and we will arrange an honest, evidence-led valuation with the agent who would actually market your home.',
            ),
            renderData: [
                'summary' => 'Start with your postcode and we will arrange an honest, evidence-led valuation — handled by the agent who would market your home, not a junior.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Valuation',
                        'heading' => 'Book a valuation',
                        'summary' => 'Branches across the area, open seven days. Call the office or start with your postcode below — we reply the same working day.',
                        'actions' => [
                            ['label' => 'Email the office', 'url' => 'mailto:hello@oakandfield.example', 'style' => 'primary'],
                            ['label' => 'Search homes', 'url' => '#property-search', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Oak & Field branch office',
                    ],
                    $this->valuationCtaSection(
                        heading: 'Book a premium valuation in one focused path',
                        summary: 'Start with a postcode, understand exactly what happens next, and move into a managed appraisal with your agent.',
                    ),
                    $this->agentTeamSection(
                        heading: 'Who you will be dealing with',
                        summary: 'The senior team who run valuations and viewings across the patch.',
                    ),
                    $this->proofSection(
                        heading: 'What to expect once we are instructed',
                        summary: 'How we work between valuation and completion.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through first?',
                        summary: 'Call the office for a no-obligation chat. We will tell you honestly whether now is the right time to move.',
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
            title: 'No homes match — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No homes match that search yet',
                'A graceful empty state for a filtered property search with no matching homes.',
            ),
            renderData: [
                'summary' => 'No homes match that search yet — but we can still point you somewhere useful while the right one comes to market.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Property search',
                        'heading' => 'No homes match that search — yet',
                        'summary' => 'Nothing on the market fits those filters right now. Widen the search to see more, or register and we will call you the moment a match lands.',
                        'actions' => [
                            ['label' => 'Search all homes', 'url' => '#property-search', 'style' => 'primary'],
                            ['label' => 'Book a valuation', 'url' => '#valuation', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'featured-properties',
                        'heading' => 'Nothing to show for that search',
                        'summary' => 'When a home matches these filters it will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->propertySearchSection(
                        heading: 'Try a broader search',
                        summary: 'Loosen the area, budget, or bedrooms and more homes will appear.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us exactly what you need and we will hunt it down across our network of vendors.',
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
                'That page has moved on',
                'A not-found page that routes visitors back into property search and valuations.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back to the homes.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That listing has already moved on',
                        'summary' => 'The link is broken or the home has sold. Head back to property search, or book a valuation while you are here.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Search homes', 'url' => '#property-search', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for the right home?',
                        summary: 'Tell us what you need and we will point you to the listings worth your time.',
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
            name: self::BRAND . ' Get Started',
            title: 'Sell with Oak & Field — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to bring your home to market?',
                'A focused conversion page inviting vendors to book a valuation and start their move.',
            ),
            renderData: [
                'summary' => 'Ready to bring your home to market? Book a valuation and start your move with Oak & Field.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Sell with us',
                        'heading' => 'Ready to bring your home to market?',
                        'summary' => 'Whether you are selling a first flat or a family home, you get the same senior team, honest pricing, and the photography that gets people through the door.',
                        'actions' => [
                            ['label' => 'Book a valuation', 'url' => '#valuation', 'style' => 'primary'],
                            ['label' => 'Email the office', 'url' => 'mailto:hello@oakandfield.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'A sold board outside an Oak & Field home',
                    ],
                    $this->marketProofSection(
                        heading: 'The results behind the boards',
                        summary: 'Why vendors trust us with the biggest sale of their year.',
                    ),
                    $this->valuationCtaSection(
                        heading: 'Start with your postcode',
                        summary: 'One field is all it takes to set the valuation in motion. We handle the rest.',
                    ),
                    $this->ctaSection(
                        heading: 'One valuation away from moving',
                        summary: 'Book the appointment and we will come back the same working day with next steps.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function propertySearchSection(string $heading, string $summary): array
    {
        return [
            'type' => 'property-search',
            'heading' => $heading,
            'summary' => $summary,
            'searchAvailable' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuredPropertiesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'featured-properties',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Maple House, Elm Quarter',
                    'summary' => 'A three-bedroom Victorian home with a south-facing garden and a chain-free sale.',
                    'price' => '725,000',
                    'meta' => '3 bed / Garden / Chain free',
                ],
                [
                    'title' => 'The Old Granary, Wheatfield',
                    'summary' => 'A converted period barn on the village edge with four bedrooms and far-reaching views.',
                    'price' => '1,150,000',
                    'meta' => '4 bed / Period / Village edge',
                ],
                [
                    'title' => 'Apartment 6, Station Wharf',
                    'summary' => 'A bright two-bedroom apartment with a private balcony, moments from the station.',
                    'price' => '495,000',
                    'meta' => '2 bed / Balcony / Station quarter',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function valuationCtaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'valuation-cta',
            'heading' => $heading,
            'summary' => $summary,
            'formBuilderAvailable' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function viewingRequestSection(string $heading, string $summary): array
    {
        return [
            'type' => 'viewing-request',
            'heading' => $heading,
            'summary' => $summary,
            'formBuilderAvailable' => false,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function localGuideSection(string $heading, string $summary): array
    {
        return [
            'type' => 'local-guide',
            'heading' => $heading,
            'summary' => $summary,
            'addressAvailable' => false,
            'items' => [
                ['title' => 'Schools', 'summary' => 'Two outstanding-rated primaries and a well-regarded secondary all within the catchment.'],
                ['title' => 'Commute', 'summary' => 'Thirty-eight minutes to the city terminus, with fast trains every fifteen minutes at peak.'],
                ['title' => 'Market', 'summary' => 'Period homes here have held their value through a softer year, with steady demand from families.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function agentTeamSection(string $heading, string $summary): array
    {
        return [
            'type' => 'agent-team',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Hannah W5', 'summary' => 'Twenty years selling homes across the patch. She values the homes she markets herself.', 'meta' => 'Sales lead'],
                ['title' => 'Marcus Bell', 'summary' => 'Runs the lettings book and looks after landlords who want their property in safe hands.', 'meta' => 'Lettings lead'],
                ['title' => 'Priya Shah', 'summary' => 'Knows the local market street by street and prices to sell, not to flatter.', 'meta' => 'Valuations'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function marketProofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'market-proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['metric' => '98%', 'name' => 'Of asking price achieved', 'summary' => 'Average sale price against asking across the last twelve months.'],
                ['metric' => '21', 'name' => 'Days to agree a sale', 'summary' => 'Median time from launch to a sale agreed on our instructions.'],
                ['metric' => '14d', 'name' => 'To exchange, fall-throughs aside', 'summary' => 'How quickly our chases move a typical sale toward exchange.'],
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
                ['metric' => '4.9', 'name' => 'Average review score', 'summary' => 'Across hundreds of verified vendor and buyer reviews.'],
                ['metric' => '82%', 'name' => 'Sales from repeat or referral', 'summary' => 'Most of our instructions come from people we have already moved.'],
                ['metric' => '31', 'name' => 'Years on the high street', 'summary' => 'An independent agency that is not going anywhere.'],
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
                ['title' => 'A first-time buyer guide for this market', 'summary' => 'What to budget, what to ask, and how to move quickly when the right home appears.', 'type' => 'Guide', 'url' => '#'],
                ['title' => 'Spring market report', 'summary' => 'Where prices, demand, and time-to-sell are heading across our patch this quarter.', 'type' => 'Report', 'url' => '#'],
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
                ['label' => 'Book a valuation', 'url' => '#valuation', 'style' => 'primary'],
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
                ['label' => 'Search', 'url' => '#property-search'],
                ['label' => 'Valuation', 'url' => '#valuation'],
                ['label' => 'Guides', 'url' => '#guides'],
                ['label' => 'Viewings', 'url' => '#viewing'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'valuationUrl' => '#valuation',
            'ctaLabel' => 'Book a valuation',
            'ctaUrl' => '#valuation',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'An independent estate agency built on local knowledge, honest valuations, and viewings that lead somewhere.',
            'items' => [
                ['label' => 'Search', 'url' => '#property-search'],
                ['label' => 'Valuations', 'url' => '#valuation'],
                ['label' => 'Local guides', 'url' => '#guides'],
                ['label' => 'Viewings', 'url' => '#viewing'],
            ],
            'columns' => [
                [
                    'heading' => 'Buy & rent',
                    'links' => [
                        ['label' => 'Property search', 'url' => '#property-search'],
                        ['label' => 'Featured homes', 'url' => '#property-search'],
                        ['label' => 'Request a viewing', 'url' => '#viewing'],
                        ['label' => 'Local guides', 'url' => '#guides'],
                    ],
                ],
                [
                    'heading' => 'Sell & let',
                    'links' => [
                        ['label' => 'Book a valuation', 'url' => '#valuation'],
                        ['label' => 'Our team', 'url' => '#team'],
                        ['label' => 'Market reports', 'url' => '#guides'],
                        ['label' => 'Why Oak & Field', 'url' => '#proof'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'hello@oakandfield.example', 'url' => 'mailto:hello@oakandfield.example'],
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
