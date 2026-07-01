<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PropertyDeveloper\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Property Developer theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (development-grid /
 * floorplans / availability-table / location-guide / viewing-panel) alongside
 * the standard hero/features/proof/cta — giving every surface a full,
 * new-build developer site rather than the shared five-section skeleton.
 *
 * Copy and brand tokens are mined verbatim from the theme's screenshot
 * renderer and section blade views; the generic section views echo a
 * heading + summary + an `items[]` grid where each item exposes a title and a
 * summary, so this provider authors those items where the renderer ships only
 * headings.
 */
final class PropertyDeveloperDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Marldon & Vale';

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
            title: self::BRAND . ' — New homes a place can be proud of',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'New homes a place can be proud of',
                'An editorial property homepage for developments, floorplans, availability, location guides, and viewing-led journeys.',
            ),
            renderData: [
                'summary' => 'Marldon & Vale build characterful new homes and apartments in well-connected places, with the specification right and the detail considered.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Property Developer',
                        'heading' => 'New homes a place can be proud of',
                        'summary' => 'An editorial property homepage for developments, floorplans, availability, location guides, and viewing-led journeys. Explore our current developments and register your interest to hear about new releases first.',
                        'actions' => [
                            ['label' => 'Register your interest', 'url' => '#viewing', 'style' => 'primary'],
                            ['label' => 'View developments', 'url' => '#developments', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Marldon & Vale new-build development',
                    ],
                    $this->developmentGridSection(
                        heading: 'Developments built to be scanned and trusted',
                        summary: 'Structured development cards keep the portfolio editorial and legible without the theme owning property records.',
                    ),
                    $this->floorplansSection(
                        heading: 'Floorplans presented with editorial clarity',
                        summary: 'Structured floorplan layouts keep specifications legible without the theme owning plan records.',
                    ),
                    $this->availabilitySection(
                        heading: 'Availability that reads at a glance',
                        summary: 'A clear plot release schedule pairs price guides and status so prospective buyers always know what is open.',
                    ),
                    $this->locationGuideSection(
                        heading: 'A location guide that sells the place',
                        summary: 'An editorial location guide keeps connectivity and context legible without owning map records.',
                    ),
                    $this->viewingPanelSection(
                        heading: 'Reach the developer through one confident path',
                        summary: 'A non-submitting viewing panel proves the enquiry journey feels like part of the development experience.',
                    ),
                    $this->featuresSection(
                        heading: 'Specification and craft, considered throughout',
                        summary: 'The detail that separates a characterful new home from a unit on a spreadsheet.',
                    ),
                    $this->proofSection(
                        heading: 'A developer buyers come back to',
                        summary: 'Outcomes from across the current and completed schemes.',
                    ),
                    $this->ctaSection(
                        heading: 'Turn interest into a booked viewing',
                        summary: 'A conversion-focused CTA stack keeps the path to registering interest direct and premium.',
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
            name: self::BRAND . ' Developments',
            title: 'Developments — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A directory of developments built to be scanned',
                'Structured development cards keep the portfolio editorial and legible without the theme owning property records.',
            ),
            renderData: [
                'summary' => 'Browse current and forthcoming developments across the region, each with floorplans, availability, and a location guide.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Developments',
                        'heading' => 'A directory of developments built to be scanned',
                        'summary' => 'Structured development cards keep the portfolio editorial and legible. Filter by location and release status, or open a single scheme to see plots and plans.',
                        'actions' => [
                            ['label' => 'Register your interest', 'url' => '#viewing', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Marldon & Vale developments',
                    ],
                    $this->developmentGridSection(
                        heading: 'Current developments',
                        summary: 'Schemes now releasing plots and apartments across the region.',
                    ),
                    $this->contentListingSection(
                        heading: 'Forthcoming and recently completed',
                        summary: 'Schemes in planning, under construction, or now fully sold.',
                    ),
                    $this->ctaSection(
                        heading: 'See a development that fits?',
                        summary: 'Register your interest and we will send releases, price guides, and viewing dates for the schemes you choose.',
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
            name: self::BRAND . ' Development',
            title: 'Vale Gardens — Development — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A development profile that reads with confidence',
                'A single development view pairs floorplans and availability so prospective buyers can register with confidence.',
            ),
            renderData: [
                'summary' => 'Vale Gardens — a collection of two, three, and four-bedroom homes set around landscaped greens, minutes from the station.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Development',
                        'heading' => 'A development profile that reads with confidence',
                        'summary' => 'A single development view pairs floorplans and availability so prospective buyers can register with confidence. Vale Gardens sets two, three, and four-bedroom homes around landscaped greens, minutes from the station.',
                        'actions' => [
                            ['label' => 'View all developments', 'url' => '#developments', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Vale Gardens development',
                    ],
                    $this->floorplansSection(
                        heading: 'Floorplans across the collection',
                        summary: 'Each house type shown with its layout, internal area, and headline specification.',
                    ),
                    $this->availabilitySection(
                        heading: 'Current availability at Vale Gardens',
                        summary: 'Plot status updated as homes are reserved, with guide prices and expected completion.',
                    ),
                    $this->locationGuideSection(
                        heading: 'Living at Vale Gardens',
                        summary: 'Connectivity, schools, and green space within easy reach of the development.',
                    ),
                    $this->ctaSection(
                        heading: 'Register interest in Vale Gardens',
                        summary: 'Tell us the home type that suits you and we will be in touch with releases and viewing dates.',
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
            name: self::BRAND . ' Register Interest',
            title: 'Register your interest — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the developer through one confident path',
                'A non-submitting viewing panel proves the enquiry journey feels like part of the development experience.',
            ),
            renderData: [
                'summary' => 'Register your interest to hear about new releases first, book a viewing, or speak with the sales team.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Register interest',
                        'heading' => 'Reach the developer through one confident path',
                        'summary' => 'A non-submitting viewing panel proves the enquiry journey feels like part of the development experience. Call the sales team, email the office, or visit the marketing suite — we reply within one working day.',
                        'actions' => [
                            ['label' => 'Email the sales team', 'url' => 'mailto:sales@marldonvale.example', 'style' => 'primary'],
                            ['label' => 'View developments', 'url' => '#developments', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Marldon & Vale marketing suite',
                    ],
                    $this->viewingPanelSection(
                        heading: 'Ways to register and visit',
                        summary: 'Pick the path that suits you. Every enquiry reaches a named member of the sales team.',
                    ),
                    $this->featuresSection(
                        heading: 'What to expect from a viewing',
                        summary: 'How an appointment with the sales team runs from first enquiry to reservation.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to be called back?',
                        summary: 'Leave your details and a member of the team will arrange a time that works for you.',
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
            title: 'Nothing published here yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing published here yet',
                'An empty listing state stays premium and editorial while the developer prepares its content.',
            ),
            renderData: [
                'summary' => 'No developments match that filter yet — but the sales team can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Developments',
                        'heading' => 'Nothing published here yet',
                        'summary' => 'We have not released developments in this area. Clear the filter to see everything, or register your interest and we will tell you the moment something opens.',
                        'actions' => [
                            ['label' => 'View all developments', 'url' => '#developments', 'style' => 'primary'],
                            ['label' => 'Register your interest', 'url' => '#viewing', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing to show here',
                        'summary' => 'When developments are released in this area they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->developmentGridSection(
                        heading: 'While you are here',
                        summary: 'A few of the schemes releasing across the rest of the region.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a particular area?',
                        summary: 'Tell us where you want to live and we will send releases as they open.',
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
            title: 'That page could not be found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That page could not be found',
                'A 404 state keeps the developer editorial and routes visitors back into the development journey.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the developments.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page could not be found',
                        'summary' => 'The link is broken or the page has moved. Head back to the developments, or register your interest with the sales team.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View developments', 'url' => '#developments', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and the sales team will point you to the right development.',
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
            name: self::BRAND . ' Register',
            title: 'Turn interest into a booked viewing — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn interest into a booked viewing',
                'A conversion-focused CTA stack keeps the path to registering interest direct and premium.',
            ),
            renderData: [
                'summary' => 'Ready to see your next home? Register your interest and book a viewing with the sales team.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Register interest',
                        'heading' => 'Turn interest into a booked viewing',
                        'summary' => 'Whether you are buying your first home or your forever home, the same considered specification and the same sales team see you through.',
                        'actions' => [
                            ['label' => 'Register your interest', 'url' => '#viewing', 'style' => 'primary'],
                            ['label' => 'Email the sales team', 'url' => 'mailto:sales@marldonvale.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Marldon & Vale new home',
                    ],
                    $this->viewingPanelSection(
                        heading: 'Book a viewing in three steps',
                        summary: 'A direct path from first interest to an appointment in the marketing suite.',
                    ),
                    $this->proofSection(
                        heading: 'Why buyers choose Marldon & Vale',
                        summary: 'The numbers behind the developments.',
                    ),
                    $this->ctaSection(
                        heading: 'One enquiry away',
                        summary: 'Register your interest and we will come back within one working day with releases and viewing dates.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function developmentGridSection(string $heading, string $summary): array
    {
        return [
            'type' => 'development-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Vale Gardens', 'summary' => 'Two, three, and four-bedroom homes set around landscaped greens, minutes from the station. Now releasing.'],
                ['title' => 'Marldon Quarter', 'summary' => 'A canalside collection of one and two-bedroom apartments with shared roof gardens. Off-plan reservations open.'],
                ['title' => 'Old Foundry Mews', 'summary' => 'Characterful townhouses on the site of a restored Victorian foundry, walkable to the high street. Final phase.'],
                ['title' => 'Hartfield Rise', 'summary' => 'Three and four-bedroom family homes with generous gardens on the edge of open countryside. Coming soon.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function floorplansSection(string $heading, string $summary): array
    {
        return [
            'type' => 'floorplans',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'The Ashby — 2 bed', 'summary' => 'A 743 sq ft two-bedroom home with an open-plan kitchen-diner opening to the garden and an en-suite to the principal bedroom.'],
                ['title' => 'The Brunel — 3 bed', 'summary' => 'A 1,094 sq ft three-bedroom home with a separate utility, downstairs cloakroom, and a south-facing rear aspect.'],
                ['title' => 'The Clifton — 4 bed', 'summary' => 'A 1,486 sq ft four-bedroom home with a study, double garage, and a galleried landing over the entrance hall.'],
                ['title' => 'The Foundry — 1 bed apartment', 'summary' => 'A 561 sq ft one-bedroom apartment with a Juliet balcony, integrated appliances, and access to the shared roof garden.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function availabilitySection(string $heading, string $summary): array
    {
        return [
            'type' => 'availability-table',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Plot 14 — The Ashby', 'summary' => 'Two bedrooms, guide £289,000. Available, expected completion this autumn.'],
                ['title' => 'Plot 21 — The Brunel', 'summary' => 'Three bedrooms, guide £374,000. Reserved, expected completion this winter.'],
                ['title' => 'Plot 06 — The Clifton', 'summary' => 'Four bedrooms, guide £459,000. Available, ready to move into now.'],
                ['title' => 'Plot 32 — The Foundry', 'summary' => 'One-bedroom apartment, guide £214,000. Coming soon, register for the next release.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function locationGuideSection(string $heading, string $summary): array
    {
        return [
            'type' => 'location-guide',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Connections', 'summary' => 'A nine-minute walk to the station, with direct trains to the city in under half an hour and easy access to the motorway.'],
                ['title' => 'Schools', 'summary' => 'Two primary schools rated good within a mile, and a well-regarded secondary school on the regular bus route.'],
                ['title' => 'Green space', 'summary' => 'Landscaped greens within the development, the river path on the doorstep, and country walks from the northern edge.'],
                ['title' => 'Everyday life', 'summary' => 'A high street of independent shops, a weekly market, and a supermarket and health centre within a short drive.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function viewingPanelSection(string $heading, string $summary): array
    {
        return [
            'type' => 'viewing-panel',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Register your interest', 'summary' => 'Tell us the development and home type that suit you and hear about releases, price guides, and viewing dates first.'],
                ['title' => 'Book a viewing', 'summary' => 'Visit the marketing suite and show home with a named member of the sales team, weekdays and weekends.'],
                ['title' => 'Speak to the team', 'summary' => 'Call or email the sales office for guidance on reservations, incentives, and expected completion dates.'],
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
                ['title' => 'Specification that lasts', 'summary' => 'Integrated appliances, engineered timber floors, and fitted wardrobes specified as standard, not as upgrades.'],
                ['title' => 'Energy efficient by design', 'summary' => 'Improved insulation, efficient heating, and solar-ready roofs that keep running costs down from day one.'],
                ['title' => 'Considered landscaping', 'summary' => 'Streets planned around greens and planting rather than parking, with mature trees retained wherever possible.'],
                ['title' => 'A ten-year warranty', 'summary' => 'Every home is covered by a structural warranty and a two-year developer aftercare commitment.'],
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
                ['title' => '1,200+ homes built', 'summary' => 'New homes and apartments delivered across the region over the last fifteen years.'],
                ['title' => '4.8 / 5 buyer rating', 'summary' => 'Independent satisfaction scores from buyers in their first year of ownership.'],
                ['title' => '1 working day', 'summary' => 'Every registration of interest answered by a named member of the sales team.'],
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
                ['title' => 'Hartfield Rise — coming soon', 'summary' => 'Three and four-bedroom family homes on the edge of open countryside. Register for the first release.'],
                ['title' => 'Saltford Wharf — in construction', 'summary' => 'A waterside scheme of apartments and townhouses now under construction, first completions next year.'],
                ['title' => 'Beech Lane — fully sold', 'summary' => 'A completed collection of forty homes, now an established neighbourhood with its own residents’ green.'],
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
                ['label' => 'Register your interest', 'url' => '#viewing', 'style' => 'primary'],
                ['label' => 'View developments', 'url' => '#developments', 'style' => 'secondary'],
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
                ['label' => 'Developments', 'url' => '#developments'],
                ['label' => 'Floorplans', 'url' => '#floorplans'],
                ['label' => 'Location', 'url' => '#location'],
                ['label' => 'Register interest', 'url' => '#viewing'],
            ],
            'ctaLabel' => 'Register your interest',
            'ctaUrl' => '#viewing',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'Characterful new homes and apartments in well-connected places, with the specification right and the detail considered.',
            'columns' => [
                [
                    'heading' => 'Developments',
                    'links' => [
                        ['label' => 'Vale Gardens', 'url' => '#developments'],
                        ['label' => 'Marldon Quarter', 'url' => '#developments'],
                        ['label' => 'Old Foundry Mews', 'url' => '#developments'],
                        ['label' => 'Hartfield Rise', 'url' => '#developments'],
                    ],
                ],
                [
                    'heading' => 'Buying',
                    'links' => [
                        ['label' => 'Floorplans', 'url' => '#floorplans'],
                        ['label' => 'Availability', 'url' => '#developments'],
                        ['label' => 'Location guide', 'url' => '#location'],
                        ['label' => 'Register interest', 'url' => '#viewing'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Book a viewing', 'url' => '#viewing'],
                        ['label' => 'sales@marldonvale.example', 'url' => 'mailto:sales@marldonvale.example'],
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
