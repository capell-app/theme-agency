<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuietLuxuryRetail\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Quiet Luxury Retail theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (ritual-guide /
 * product-families / ingredient-notes / store-consultation / usage-guidance /
 * newsletter) alongside the shared hero/proof/features/content-listing/cta —
 * giving every surface a full, individual luxury-retail house site rather than
 * the generic five-section skeleton.
 *
 * Section payloads follow the keys the theme's own section blades read
 * (`title`/`summary` cards, `meta` ingredient notes, `value`/`label` proof,
 * `items` store actions) so the live render and a real screenshot read as a
 * finished storefront.
 */
final class QuietLuxuryRetailDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Maison Lessard';

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
            title: self::BRAND . ' — Skincare, Fragrance & Considered Living',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Retail a house can stand behind',
                'Maison Lessard is a quiet-luxury house for skincare, fragrance, and the home — guided rituals, named ingredients, and store consultation, without the noise.',
            ),
            renderData: [
                'summary' => 'Maison Lessard is a quiet-luxury house for skincare, fragrance, and the home. Guided rituals, named ingredients, and unhurried store consultation.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'A considered house',
                        'heading' => 'Retail a house can stand behind',
                        'summary' => 'Skincare, fragrance, and objects for the home, made in small batches and sold the slow way — with guidance, named ingredients, and the option to be advised in person.',
                        'primary_label' => 'Book a consultation',
                        'primary_url' => '#store-consultation',
                        'secondary_label' => 'Explore the collection',
                        'secondary_url' => '#product-families',
                        'notes' => [
                            'Free consultation in every boutique and by video',
                            'Refillable glass across the full skincare range',
                            'Every formula listed by its named active ingredients',
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Maison Lessard skincare and fragrance still life',
                    ],
                    $this->ritualGuideSection(
                        heading: 'A ritual, not a routine',
                        summary: 'Three unhurried steps the house has refined over a decade — cleanse, treat, and scent — each with a single product to start.',
                    ),
                    $this->productFamiliesSection(
                        heading: 'Four families, made with restraint',
                        summary: 'A short catalogue by design. Each family is small, complete, and built to be lived with.',
                    ),
                    $this->ingredientNotesSection(
                        heading: 'Ingredients, named and explained',
                        summary: 'No proprietary blends or marketing names. The actives that do the work, and why we chose them.',
                    ),
                    $this->storeConsultationSection(
                        heading: 'Be advised, not sold to',
                        summary: 'Sit with a consultant in the boutique or over video. We will build a regimen around your skin and leave the upsell at the door.',
                    ),
                    $this->usageGuidanceSection(
                        heading: 'How to use what you bring home',
                        summary: 'Application, layering, and the small choices that make a formula last. Guidance the house would give in person, written down.',
                    ),
                    $this->proofSection(
                        heading: 'The standards behind the label',
                        summary: 'What the house holds itself to, in numbers.',
                    ),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Begin with a single ritual',
                        summary: 'Book a consultation or order one considered product. The house will guide the rest.',
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
            name: self::BRAND . ' Collection',
            title: 'The collection — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A collection built to be browsed slowly',
                'The full Maison Lessard catalogue across skincare, fragrance, body, and the home — small, complete, and editorial.',
            ),
            renderData: [
                'summary' => 'The full catalogue across skincare, fragrance, body, and the home. Browse by family, read the notes, and let a consultant narrow it down.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'The collection',
                        'heading' => 'A collection built to be browsed slowly',
                        'summary' => 'Four families, a handful of products in each. Structured so the catalogue reads as editorial — never a wall of skus.',
                        'primary_label' => 'Book a consultation',
                        'primary_url' => '#store-consultation',
                        'secondary_label' => 'Read the ingredient notes',
                        'secondary_url' => '#ingredient-notes',
                        'notes' => [
                            'Skincare, fragrance, body, and the home',
                            'Every product listed by its named actives',
                            'Refillable across the skincare range',
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Maison Lessard collection laid out by family',
                    ],
                    $this->productFamiliesSection(
                        heading: 'Browse by family',
                        summary: 'Start with the family, not the product. Each one is small enough to know completely.',
                    ),
                    $this->contentListingSection(
                        heading: 'Featured pieces this season',
                        summary: 'The products the house is recommending now, with a note on who each one suits.',
                    ),
                    $this->ingredientNotesSection(
                        heading: 'The actives behind the collection',
                        summary: 'The named ingredients that recur across the families, and what each one does.',
                    ),
                    $this->ctaSection(
                        heading: 'Not sure where to start?',
                        summary: 'A consultant will narrow the collection to three products built around your skin and your scent.',
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
            title: 'The Restorative Serum — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The Restorative Serum',
                'A single-ingredient-led serum, explained the way the house would explain it in the boutique — what is in it, how to use it, and what pairs with it.',
            ),
            renderData: [
                'summary' => 'The Restorative Serum, explained in full: the named actives, how to apply it, and the rituals and pieces that pair with it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Skincare — the serum',
                        'heading' => 'The Restorative Serum',
                        'summary' => 'A nightly treatment built around niacinamide and a low-dose retinaldehyde. Considered, well-tolerated, and refillable — explained the way a consultant would.',
                        'primary_label' => 'Book a consultation',
                        'primary_url' => '#store-consultation',
                        'secondary_label' => 'Back to the collection',
                        'secondary_url' => '#product-families',
                        'notes' => [
                            '30ml refillable glass, made in small batches',
                            'Listed by its named active ingredients',
                            'Advised in person before you commit',
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'The Restorative Serum in refillable glass',
                    ],
                    $this->featuresSection(
                        heading: 'What is in the bottle',
                        summary: 'The named actives that do the work, at the doses the house is willing to print.',
                    ),
                    $this->ingredientNotesSection(
                        heading: 'The notes behind the formula',
                        summary: 'Why each active is here, and what it asks of your skin in return.',
                    ),
                    $this->usageGuidanceSection(
                        heading: 'How to use the serum',
                        summary: 'When to apply it, what to layer over it, and how to introduce it without overwhelming your skin.',
                    ),
                    $this->ctaSection(
                        heading: 'Make it part of your ritual',
                        summary: 'Book a consultation and we will place the serum into a regimen that suits your skin.',
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
            name: self::BRAND . ' Consultation',
            title: 'Book a consultation — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Be advised in person',
                'Reach the house through one considered path — book a boutique appointment or a video consultation, and we will build a regimen around your skin.',
            ),
            renderData: [
                'summary' => 'Reach the house through one considered path. Book a boutique appointment or a video consultation, and a consultant will build a regimen around your skin.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Consultation',
                        'heading' => 'Be advised in person',
                        'summary' => 'Write to the house at studio@maisonlessard.example, or book below for a boutique or video appointment. We reply within one working day and never with a script.',
                        'primary_label' => 'Email the studio',
                        'primary_url' => 'mailto:studio@maisonlessard.example',
                        'secondary_label' => 'Explore the collection',
                        'secondary_url' => '#product-families',
                        'notes' => [
                            'Boutiques in Paris, London, and Copenhagen',
                            'Video consultation anywhere, by appointment',
                            'No upsell — guidance is the service',
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'A Maison Lessard consultation room',
                    ],
                    $this->storeConsultationSection(
                        heading: 'Two ways to be advised',
                        summary: 'Choose the boutique or your own home. Either way you sit with a consultant who builds the regimen, not a salesperson who closes it.',
                    ),
                    $this->ritualGuideSection(
                        heading: 'What a consultation covers',
                        summary: 'We work through the same three steps the house lives by, and leave you with a regimen you can actually keep.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to begin by email?',
                        summary: 'Tell us about your skin and what you are looking for. A consultant will reply within one working day.',
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
            name: self::BRAND . ' Nothing Found',
            title: 'Nothing in this edit — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing in this edit yet',
                'A graceful empty state for a filtered collection with no matching pieces, routing visitors back to the families and the consultation.',
            ),
            renderData: [
                'summary' => 'Nothing in this edit matches that filter yet — but the house can still point you toward a family or a consultation.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'The collection',
                        'heading' => 'Nothing in this edit yet',
                        'summary' => 'No pieces match that filter. Clear it to see the full collection, or let a consultant narrow it down for you instead.',
                        'primary_label' => 'View the full collection',
                        'primary_url' => '#product-families',
                        'secondary_label' => 'Book a consultation',
                        'secondary_url' => '#store-consultation',
                        'notes' => [
                            'Four families, refreshed each season',
                            'Refillable across the skincare range',
                            'Guidance available in person or by video',
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing to show in this edit',
                        'summary' => 'When pieces enter this edit they will appear here, with a note on who each one suits.',
                        'items' => [],
                    ],
                    $this->productFamiliesSection(
                        heading: 'While you are here',
                        summary: 'The four families the house is built on. Start with one and the rest will follow.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell a consultant what you had in mind and we will find it, or make a recommendation in its place.',
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
                'This page has been put away',
                'A not-found page that routes visitors back into the collection and the consultation.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back to the collection.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'This page has been put away',
                        'summary' => 'The link is broken or the piece has moved on. Return to the collection, or sit down with a consultant instead.',
                        'primary_label' => 'Back to home',
                        'primary_url' => '/',
                        'secondary_label' => 'Explore the collection',
                        'secondary_url' => '#product-families',
                        'notes' => [
                            'Skincare, fragrance, body, and the home',
                            'Guidance in person or by video',
                            'Every formula listed by its named actives',
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell the house what you needed and a consultant will point you to the right place.',
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
            name: self::BRAND . ' Begin',
            title: 'Begin with the house — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Begin the slow way',
                'A focused conversion page inviting a first consultation or a first considered purchase.',
            ),
            renderData: [
                'summary' => 'Begin the slow way. Book a consultation or order a single considered product, and let the house guide the rest.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Begin',
                        'heading' => 'Begin the slow way',
                        'summary' => 'Whether you start with one serum or a full regimen, you get the same guidance and the same standard. There is no rush, and no script.',
                        'primary_label' => 'Book a consultation',
                        'primary_url' => '#store-consultation',
                        'secondary_label' => 'Email the studio',
                        'secondary_url' => 'mailto:studio@maisonlessard.example',
                        'notes' => [
                            'Free consultation, in person or by video',
                            'Refillable glass across the skincare range',
                            'A reply within one working day',
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'A Maison Lessard boutique interior',
                    ],
                    $this->proofSection(
                        heading: 'Why people stay with the house',
                        summary: 'The standards that keep customers coming back.',
                    ),
                    $this->usageGuidanceSection(
                        heading: 'What guidance looks like',
                        summary: 'The practical advice the house gives every customer, before and after the sale.',
                    ),
                    $this->ctaSection(
                        heading: 'One ritual away',
                        summary: 'Book a consultation or send a note, and we will reply within one working day with a place to begin.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function ritualGuideSection(string $heading, string $summary): array
    {
        return [
            'type' => 'ritual-guide',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Cleanse', 'summary' => 'A gentle gel-to-oil that lifts the day without stripping the barrier. The one step the house never skips.'],
                ['title' => 'Treat', 'summary' => 'A single active-led serum, chosen for your skin in consultation. Less, used consistently, beats more used once.'],
                ['title' => 'Scent', 'summary' => 'A close, skin-like fragrance applied last — the finishing note of the ritual, not a cover for it.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productFamiliesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'product-families',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Skincare', 'summary' => 'Cleansers, serums, and balms built around named actives, in refillable glass and small batches.'],
                ['title' => 'Fragrance', 'summary' => 'Close, skin-like scents composed in low concentration, designed to be worn rather than announced.'],
                ['title' => 'Body', 'summary' => 'Washes, oils, and creams that carry the same restraint and the same ingredient honesty as the face range.'],
                ['title' => 'The home', 'summary' => 'Candles, room scents, and objects chosen to live alongside the rest, made by hands the house trusts.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ingredientNotesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'ingredient-notes',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['meta' => 'Active', 'title' => 'Niacinamide', 'summary' => 'A 5% dose to even tone and support the barrier — high enough to work, low enough to keep skin calm.'],
                ['meta' => 'Active', 'title' => 'Retinaldehyde', 'summary' => 'A gentler relative of retinol at 0.05%, for renewal without the sting most actives demand.'],
                ['meta' => 'Material', 'title' => 'Refillable glass', 'summary' => 'Every primary vessel is glass and returnable, so a regimen costs the earth a little less each time.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function storeConsultationSection(string $heading, string $summary): array
    {
        return [
            'type' => 'store-consultation',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Book an appointment',
            'url' => '#store-consultation',
            'items' => [
                ['title' => 'In the boutique', 'summary' => 'Forty minutes with a consultant in Paris, London, or Copenhagen. Patch tests, samples, and no pressure to buy.'],
                ['title' => 'By video', 'summary' => 'The same consultation from your own bathroom, with samples sent ahead so you can follow along.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function usageGuidanceSection(string $heading, string $summary): array
    {
        return [
            'type' => 'usage-guidance',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Apply', 'summary' => 'A few drops to clean, slightly damp skin at night. More is not better — the dose is the point.'],
                ['title' => 'Layer', 'summary' => 'Seal with the balm, and leave actives a night apart while your skin learns them.'],
                ['title' => 'Be advised', 'summary' => 'If anything stings or flakes, message the house. We will adjust the regimen, not just sell you the next thing.'],
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
            'heading' => 'Notes from the house',
            'summary' => 'A short letter each season — new pieces, a ritual to try, and the thinking behind an ingredient. No more than that.',
            'action' => '#newsletter',
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
                ['meta' => '5% active', 'title' => 'Niacinamide', 'summary' => 'Evens tone and steadies the barrier, at a dose the house is willing to print on the label.', 'care_note' => 'Suits most skin'],
                ['meta' => '0.05% active', 'title' => 'Retinaldehyde', 'summary' => 'Renewal without the harshness of stronger retinoids, introduced slowly under guidance.', 'care_note' => 'Nightly, build up'],
                ['meta' => 'Refillable', 'title' => '30ml glass', 'summary' => 'A small-batch vessel made to be returned and refilled, not thrown away.', 'care_note' => 'Returnable in store'],
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
                ['value' => '100%', 'label' => 'Refillable glass across the skincare range'],
                ['value' => '3', 'label' => 'Boutiques, each with free consultation'],
                ['value' => '1 day', 'label' => 'To a reply from a real consultant'],
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
                ['category' => 'Skincare', 'title' => 'The Restorative Serum', 'summary' => 'A nightly active-led treatment for skin that wants renewal without the sting.'],
                ['category' => 'Fragrance', 'title' => 'Eau de Lin', 'summary' => 'A close, linen-soft scent built to sit on the skin rather than fill the room.'],
                ['category' => 'The home', 'title' => 'Atelier Candle', 'summary' => 'A long-burning candle in cedar and smoke, poured in small numbers each season.'],
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
            'label' => 'Book a consultation',
            'url' => '#store-consultation',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        $items = [
            ['label' => 'Rituals', 'url' => '#ritual-guide'],
            ['label' => 'Collection', 'url' => '#product-families'],
            ['label' => 'Ingredients', 'url' => '#ingredient-notes'],
            ['label' => 'Consultation', 'url' => '#store-consultation'],
            ['label' => 'Journal', 'url' => '#newsletter'],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'items' => $items,
            'consultationUrl' => '#store-consultation',
            'ctaLabel' => 'Book a consultation',
            'ctaUrl' => '#store-consultation',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'title' => 'Collection',
                'heading' => 'Collection',
                'links' => [
                    ['label' => 'Skincare', 'url' => '#product-families'],
                    ['label' => 'Fragrance', 'url' => '#product-families'],
                    ['label' => 'Body', 'url' => '#product-families'],
                    ['label' => 'The home', 'url' => '#product-families'],
                ],
            ],
            [
                'title' => 'The house',
                'heading' => 'The house',
                'links' => [
                    ['label' => 'Consultation', 'url' => '#store-consultation'],
                    ['label' => 'Refills', 'url' => '#product-families'],
                    ['label' => 'Boutiques', 'url' => '#store-consultation'],
                    ['label' => 'Ingredients', 'url' => '#ingredient-notes'],
                ],
            ],
            [
                'title' => 'Connect',
                'heading' => 'Connect',
                'links' => [
                    ['label' => 'Journal', 'url' => '#newsletter'],
                    ['label' => 'studio@maisonlessard.example', 'url' => 'mailto:studio@maisonlessard.example'],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A quiet-luxury house for skincare, fragrance, and the home. Paris, London, and Copenhagen.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
