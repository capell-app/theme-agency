<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CreativeMarketplace\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Creative Marketplace theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature marketplace renderers (category-chips
 * / shot-grid / briefs-pricing / agencies-services / profile-availability /
 * service-hero) alongside the standard hero/proof/content-listing/cta — giving
 * every surface a full creative-hiring marketplace rather than the shared
 * five-section skeleton.
 */
final class CreativeMarketplaceDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Palette & Pixel';

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
            title: self::BRAND . ' — Hire the designer your work deserves',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A bright marketplace for hiring creative talent',
                'Palette & Pixel connects teams with vetted product designers, illustrators, motion artists, and branding studios — browse the work, then hire.',
            ),
            renderData: [
                'summary' => 'Palette & Pixel is a bright creative marketplace. Browse shots, compare designers and agencies, post a brief, and hire the talent your work deserves.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Hire the designer your work deserves',
                        summary: 'A bright, expressive marketplace for shots, service categories, designer profiles, briefs, agencies, availability, and hiring journeys — all in one place.',
                        primaryLabel: 'Hire a designer',
                        primaryUrl: '#hire',
                        secondaryLabel: 'Browse shots',
                        secondaryUrl: '#shots',
                        notes: [
                            '12,400 designers and 380 studios available to hire this week',
                            'Avg. first reply in under 4 hours, every brief',
                            'Saved shots, likes, and views power smarter matches',
                        ],
                    ),
                    $this->categoryChipsSection(),
                    $this->shotGridSection(
                        heading: 'Fresh shots from the community',
                        summary: 'A scannable grid of recent visual work — product, branding, illustration, and motion — curated by saves, likes, and views.',
                        media: $media,
                    ),
                    $this->briefsPricingSection(),
                    $this->agenciesServicesSection(
                        heading: 'Agencies and service teams ready to start',
                        media: $media,
                    ),
                    $this->profileAvailabilitySection(
                        heading: 'Designers available to hire now',
                        summary: 'Profiles with live availability, day rates, and recent saves — engage the right person while their calendar is open.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Ready to hire creative talent?',
                        summary: 'Post a brief in two minutes and start hearing from designers and studios today.',
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
            name: self::BRAND . ' Browse',
            title: 'Browse shots & designers — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Browse shots, designers, and studios',
                'A scannable directory of visual work and the creative talent behind it, filtered by discipline, availability, and rate.',
            ),
            renderData: [
                'summary' => 'Browse a structured grid of shots, designers, and studios across product, branding, illustration, and motion.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'A shot listing built to be browsed',
                        summary: 'A structured grid of visual work keeps the marketplace expressive and scannable. Filter by discipline, then open a profile to check availability.',
                        primaryLabel: 'Filter by discipline',
                        primaryUrl: '#categories',
                        secondaryLabel: 'Hire a designer',
                        secondaryUrl: '#hire',
                        notes: [
                            'Sort by saves, likes, views, or newest',
                            'Live availability badges on every profile',
                            'Day rates shown before you reach out',
                        ],
                    ),
                    $this->shotGridSection(
                        heading: 'Recent shots, newest first',
                        summary: 'Visual work from designers and studios across the marketplace.',
                        media: $media,
                    ),
                    $this->categoryChipsSection(),
                    $this->contentListingSection(
                        heading: 'More designers and studios to browse',
                        summary: 'Profiles and service teams matched to the disciplines you filtered.',
                    ),
                    $this->ctaSection(
                        heading: 'See work that fits your brief?',
                        summary: 'Open a profile, check availability, and start a hiring conversation.',
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
            name: self::BRAND . ' Designer Profile',
            title: 'Mara Ellison — Product Designer — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Mara Ellison — product designer available to hire',
                'A profile detail surface pairing service offerings, availability, and pricing so hiring teams can engage with confidence.',
            ),
            renderData: [
                'summary' => 'Mara Ellison is a product and brand designer with live availability, day rates, and a portfolio of shots saved by thousands.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Mara Ellison — product designer, available to hire',
                        summary: 'A product and brand designer with live availability, transparent day rates, and a portfolio of shots saved by thousands. Eight years across fintech, health, and consumer product.',
                        primaryLabel: 'Hire Mara',
                        primaryUrl: '#hire',
                        secondaryLabel: 'See availability',
                        secondaryUrl: '#availability',
                        notes: [
                            'Open for 2 new projects this quarter',
                            '£620/day · typical scope 4–8 weeks',
                            '3,400 saves across recent shots',
                        ],
                    ),
                    $this->serviceHeroSection(
                        heading: 'How Mara works with hiring teams',
                        summary: 'A focused service profile pairs availability and proof so hiring teams can engage with confidence.',
                    ),
                    $this->profileAvailabilitySection(
                        heading: 'Availability and engagement',
                        summary: 'Open for two new projects this quarter. Day rate and typical scope shown up front.',
                    ),
                    $this->briefsPricingSection(),
                    $this->shotGridSection(
                        heading: 'Selected shots from Mara',
                        summary: 'Recent product and branding work, ranked by saves and views.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Want to work with Mara?',
                        summary: 'Send a brief and Mara typically replies within a few hours while availability is open.',
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
            name: self::BRAND . ' Post a Brief',
            title: 'Post a brief — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Post a brief and start hiring',
                'Tell the marketplace what you are building. Designers and studios with matching availability reply directly.',
            ),
            renderData: [
                'summary' => 'Post a brief and the right designers and studios reach out — no account managers, no waiting.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'One confident path to start a conversation',
                        summary: 'Describe the work, set a budget range, and matching designers and studios respond directly. Most briefs get a first reply within a few hours.',
                        primaryLabel: 'Post a brief',
                        primaryUrl: '#brief',
                        secondaryLabel: 'Browse designers',
                        secondaryUrl: '#hire',
                        notes: [
                            'Free to post — pay only when you hire',
                            'Set discipline, budget, and timeline up front',
                            'Reply rate over 90% within one working day',
                        ],
                    ),
                    $this->newsletterSection(),
                    $this->profileAvailabilitySection(
                        heading: 'Designers waiting on briefs like yours',
                        summary: 'A preview of available talent matched to common brief types.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to browse first?',
                        summary: 'Explore shots and profiles, save your favourites, then invite them to your brief.',
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
            title: 'No matching shots — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No shots match that filter yet',
                'A graceful empty state for a filtered marketplace search with no matching designers, studios, or shots.',
            ),
            renderData: [
                'summary' => 'No shots match that filter yet — but the marketplace can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'No shots match that filter — yet',
                        summary: 'Nothing in this discipline and availability window right now. Clear the filter to see every shot, or post a brief and let designers come to you.',
                        primaryLabel: 'Clear filters',
                        primaryUrl: '#shots',
                        secondaryLabel: 'Post a brief',
                        secondaryUrl: '#brief',
                        notes: [
                            'Try widening the budget or timeline',
                            'Browse adjacent disciplines and categories',
                            'Post a brief — talent applies to you',
                        ],
                    ),
                    $this->contentListingSection(
                        heading: 'Nothing to show here',
                        summary: 'When designers and studios match this filter they will appear here, ranked by saves and availability.',
                        items: [],
                    ),
                    $this->categoryChipsSection(),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Post a brief and matching designers and studios will reach out directly.',
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
                'A not-found page that routes visitors back into the marketplace shot grid and hiring paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the marketplace.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'This shot moved to another frame',
                        summary: 'The link is broken or the profile has moved. Head back to the shot grid, or post a brief and let designers find you.',
                        primaryLabel: 'Back to home',
                        primaryUrl: '/',
                        secondaryLabel: 'Browse shots',
                        secondaryUrl: '#shots',
                        notes: [
                            'Browse trending shots and designers',
                            'Search by discipline or studio',
                            'Post a brief in two minutes',
                        ],
                    ),
                    $this->categoryChipsSection(),
                    $this->ctaSection(
                        heading: 'Still looking for the right designer?',
                        summary: 'Post a brief and matching talent will reach out directly.',
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
            name: self::BRAND . ' Hire',
            title: 'Hire creative talent — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to hire the talent your work deserves?',
                'A focused conversion page inviting teams to post a brief and hire vetted creative talent.',
            ),
            renderData: [
                'summary' => 'Ready to hire? Post a brief and start working with vetted designers and studios this week.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Hire the talent your work deserves',
                        summary: 'Whether it is a single shot, a full rebrand, or an ongoing design partner, the marketplace matches you with available, vetted creative talent.',
                        primaryLabel: 'Post a brief',
                        primaryUrl: '#brief',
                        secondaryLabel: 'Browse designers',
                        secondaryUrl: '#hire',
                        notes: [
                            '12,400 designers available to hire this week',
                            'Vetted profiles with live availability and rates',
                            'First replies in hours, not days',
                        ],
                    ),
                    $this->proofSection(),
                    $this->briefsPricingSection(),
                    $this->ctaSection(
                        heading: 'One brief away from hiring',
                        summary: 'Post the work and matching designers and studios will reach out within a working day.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  list<string>  $notes
     * @return array<string, mixed>
     */
    private function heroSection(
        string $heading,
        string $summary,
        string $primaryLabel,
        string $primaryUrl,
        string $secondaryLabel,
        string $secondaryUrl,
        array $notes,
    ): array {
        return [
            'type' => 'hero',
            'heading' => $heading,
            'summary' => $summary,
            'primary_label' => $primaryLabel,
            'primary_url' => $primaryUrl,
            'secondary_label' => $secondaryLabel,
            'secondary_url' => $secondaryUrl,
            'notes' => $notes,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryChipsSection(): array
    {
        return [
            'type' => 'category-chips',
            'heading' => 'Browse by creative discipline',
            'items' => [
                ['title' => 'Product & UI design', 'summary' => 'Interfaces, design systems, and end-to-end product work from discovery to ship.'],
                ['title' => 'Branding & identity', 'summary' => 'Logos, visual systems, and brand guidelines for launches and rebrands.'],
                ['title' => 'Illustration', 'summary' => 'Custom editorial, marketing, and product illustration in every style.'],
                ['title' => 'Motion & animation', 'summary' => 'Explainer, UI motion, and launch animation that brings the brand to life.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function shotGridSection(string $heading, string $summary, array $media): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $shots = [
            ['title' => 'Onboarding flow — fintech app', 'summary' => 'A four-step account setup redesign that lifted activation for a challenger bank.', 'meta' => 'Product & UI', 'care_note' => '2,140 saves'],
            ['title' => 'Harbour Coffee identity', 'summary' => 'Naming, mark, and packaging system for an independent roaster opening three sites.', 'meta' => 'Branding', 'care_note' => '1,880 saves'],
            ['title' => 'Field guide illustrations', 'summary' => 'A set of editorial spot illustrations for a renewables platform newsletter.', 'meta' => 'Illustration', 'care_note' => '1,420 saves'],
            ['title' => 'Checkout micro-interactions', 'summary' => 'Motion studies for a payment confirmation flow, tuned for trust and speed.', 'meta' => 'Motion', 'care_note' => '1,260 saves'],
            ['title' => 'Northwind design system', 'summary' => 'Tokens, components, and docs unifying four product squads on one language.', 'meta' => 'Product & UI', 'care_note' => '3,010 saves'],
            ['title' => 'Atlas Festival key art', 'summary' => 'A bold poster and social system that took a regional arts festival national.', 'meta' => 'Branding', 'care_note' => '2,460 saves'],
        ];

        $items = [];

        foreach ($shots as $index => $shot) {
            $image = $pool[$index % max(count($pool), 1)] ?? null;
            $items[] = [
                ...$shot,
                'url' => '#shot-' . ($index + 1),
                'image' => $image,
                'imageAlt' => $shot['title'],
            ];
        }

        return [
            'type' => 'shot-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function briefsPricingSection(): array
    {
        return [
            'type' => 'briefs-pricing',
            'heading' => 'Post a brief, set a budget, get matched',
            'summary' => 'Tell the marketplace what you need and the right designers and studios respond with availability and a quote — no bidding wars, no guesswork.',
            'label' => 'Post a brief',
            'url' => '#brief',
            'items' => [
                ['title' => 'Single shot or task', 'summary' => 'From £150. One designer, one focused piece of work — a screen, an illustration, a logo refresh.'],
                ['title' => 'Project engagement', 'summary' => 'From £2,400. A scoped engagement with a designer or studio across discovery, design, and handoff.'],
                ['title' => 'Ongoing design partner', 'summary' => 'From £4,800/mo. A reserved designer or team with availability held for your roadmap.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function agenciesServicesSection(string $heading, array $media): array
    {
        return [
            'type' => 'agencies-services',
            'heading' => $heading,
            'items' => [
                ['meta' => 'Brand studio · 14 people', 'title' => 'Fieldwork', 'summary' => 'An independent brand and digital studio shipping identities and sites for founders and in-house teams.'],
                ['meta' => 'Product studio · 9 people', 'title' => 'Northwind Collective', 'summary' => 'A product design team specialising in design systems and complex B2B interfaces.'],
                ['meta' => 'Motion studio · 6 people', 'title' => 'Tempo & Co.', 'summary' => 'Launch films, UI motion, and social toolkits for consumer and culture brands.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function profileAvailabilitySection(string $heading, string $summary): array
    {
        return [
            'type' => 'profile-availability',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Mara Ellison — Product & brand', 'summary' => 'Open for 2 projects · £620/day · 8 yrs across fintech, health, and consumer. 3,400 saves.'],
                ['title' => 'Devon Carr — Illustration', 'summary' => 'Open this month · £480/day · Editorial and product illustration with a warm, hand-drawn line.'],
                ['title' => 'Aria Booth — Motion design', 'summary' => 'Booking from June · £540/day · UI motion and launch animation for app and web.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function serviceHeroSection(string $heading, string $summary): array
    {
        return [
            'type' => 'service-hero',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Product & UI design', 'summary' => 'End-to-end product work — discovery, flows, interface, and a design system your team can own.'],
                ['title' => 'Brand identity', 'summary' => 'Logo, type, colour, and a flexible visual system for launches and rebrands.'],
                ['title' => 'Design partner retainer', 'summary' => 'Reserved availability for ongoing design work across your roadmap, month to month.'],
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
            'heading' => 'A marketplace teams trust',
            'items' => [
                ['value' => '12,400+', 'label' => 'Designers and studios available to hire'],
                ['value' => '4 hrs', 'label' => 'Median first reply on a posted brief'],
                ['value' => '1.2m', 'label' => 'Shots saved, liked, and viewed each month'],
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>|null  $items
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, ?array $items = null): array
    {
        $items ??= [
            ['category' => 'Product & UI · Open now', 'title' => 'Jonah Pryce — senior product designer', 'summary' => 'Design systems and B2B interfaces. £680/day, available for two new projects.'],
            ['category' => 'Branding · Booking soon', 'title' => 'Selma Roux — brand designer', 'summary' => 'Identity and packaging for consumer brands. Booking from next month, £560/day.'],
            ['category' => 'Studio · 9 people', 'title' => 'Northwind Collective', 'summary' => 'A product studio for complex platforms, available for a Q3 engagement.'],
        ];

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
    private function newsletterSection(): array
    {
        return [
            'type' => 'newsletter',
            'heading' => 'Describe your brief',
            'summary' => 'Add your email and a short note about the work. Matching designers and studios will reach out directly — most within a few hours.',
            'action' => '#brief',
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
            'label' => 'Post a brief',
            'url' => '#brief',
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
                ['label' => 'Shots', 'url' => '#shots'],
                ['label' => 'Designers', 'url' => '#hire'],
                ['label' => 'Studios', 'url' => '#studios'],
                ['label' => 'Briefs', 'url' => '#brief'],
                ['label' => 'Pricing', 'url' => '#pricing'],
            ],
            'ctaLabel' => 'Post a brief',
            'ctaUrl' => '#brief',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A bright marketplace for hiring vetted creative talent — designers, illustrators, motion artists, and studios.',
            'columns' => [
                [
                    'heading' => 'Browse',
                    'links' => [
                        ['label' => 'Shots', 'url' => '#shots'],
                        ['label' => 'Designers', 'url' => '#hire'],
                        ['label' => 'Studios', 'url' => '#studios'],
                        ['label' => 'Categories', 'url' => '#categories'],
                    ],
                ],
                [
                    'heading' => 'Hire',
                    'links' => [
                        ['label' => 'Post a brief', 'url' => '#brief'],
                        ['label' => 'Pricing', 'url' => '#pricing'],
                        ['label' => 'How it works', 'url' => '#hire'],
                        ['label' => 'For agencies', 'url' => '#studios'],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'links' => [
                        ['label' => 'About', 'url' => '#about'],
                        ['label' => 'Contact', 'url' => '#brief'],
                        ['label' => 'hello@paletteandpixel.example', 'url' => 'mailto:hello@paletteandpixel.example'],
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
