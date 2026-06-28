<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MinimalFashion\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Minimal Fashion theme.
 *
 * Each surface seeds an ordered `render_data['sections']` list so the page
 * adapter emits the theme's signature retail renderers (seasonal-collections /
 * category-paths / lookbook-feature / material-notes / product-care / newsletter)
 * around the shared hero/proof/cta — giving every surface a full, restrained
 * boutique storefront rather than the generic five-section skeleton.
 *
 * Copy is mined from the theme's screenshot renderer and section views (brand
 * "Atelier Nord", quiet seasonal retail language) so the seeded demo matches the
 * design the theme was authored to present.
 */
final class MinimalFashionDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Atelier Nord';

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
            title: self::BRAND . ' — Considered Wardrobe Essentials',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A wardrobe edited down to what lasts',
                'Atelier Nord is a small fashion house designing seasonal collections, lookbooks, and lasting essentials made to be worn for years.',
            ),
            renderData: [
                'summary' => 'Atelier Nord designs restrained seasonal collections, lookbooks, and lasting essentials for a wardrobe edited down to what matters.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Spring / Summer 2026',
                        heading: 'A wardrobe edited down to what lasts',
                        summary: 'Quiet seasonal collections, considered materials, and pieces built to be repaired rather than replaced. No noise, no fast turnover — just clothes you reach for first.',
                        media: $media,
                        primaryLabel: 'Shop the collection',
                        secondaryLabel: 'View the lookbook',
                    ),
                    $this->seasonalCollectionsSection(),
                    $this->categoryPathsSection(),
                    $this->lookbookSection($media),
                    $this->materialNotesSection(),
                    $this->productCareSection(),
                    $this->proofSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Stay close to each collection',
                        summary: 'New pieces land a few times a year. Follow the house to hear first, and to keep the things you already own in good shape.',
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
            name: self::BRAND . ' Collections',
            title: 'Collections — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A product archive built to be browsed calmly',
                'Browse seasonal collections, lookbooks, and lasting essentials across the Atelier Nord catalogue.',
            ),
            renderData: [
                'summary' => 'A calm archive of collections, lookbooks, and lasting essentials — browse the full Atelier Nord catalogue at your own pace.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The catalogue',
                        heading: 'A product archive built to be browsed calmly',
                        summary: 'Structured listing cards keep collections, lookbooks, and essentials legible. Filter by category, or read the story behind each material.',
                        media: $media,
                        primaryLabel: 'Browse essentials',
                        secondaryLabel: 'View the lookbook',
                    ),
                    $this->contentListingSection(
                        heading: 'Across the collection',
                        media: $media,
                    ),
                    $this->categoryPathsSection(),
                    $this->seasonalCollectionsSection(),
                    $this->ctaSection(
                        heading: 'Not sure where to start?',
                        summary: 'Begin with the essentials, then build outward by season. Our store team can help you find the right pieces.',
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
            name: self::BRAND . ' The Nord Coat',
            title: 'The Nord Coat — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The Nord Coat — a seasonless overcoat',
                'A double-faced wool overcoat cut for everyday wear, made to be tailored, repaired, and kept for a decade.',
            ),
            renderData: [
                'summary' => 'The Nord Coat — a double-faced wool overcoat cut for everyday wear, made to be tailored, repaired, and kept for years.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Outerwear',
                        heading: 'The Nord Coat — a seasonless overcoat',
                        summary: 'Double-faced Italian wool, a relaxed shoulder, and a single horn button. Cut to layer over knitwear in winter and wear open through spring.',
                        media: $media,
                        primaryLabel: 'Add to bag',
                        secondaryLabel: 'Book a fitting',
                    ),
                    $this->materialNotesSection(),
                    $this->productCareSection(),
                    $this->contentListingSection(
                        heading: 'Wears well with',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Made to be kept',
                        summary: 'Every coat comes with a repair guarantee and free alterations in the first year. Buy once, wear for years.',
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
            name: self::BRAND . ' Stores & Assistance',
            title: 'Stores & assistance — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'One quiet path to stay in touch',
                'Visit a store, book a fitting, or join the newsletter to hear about each collection before it lands.',
            ),
            renderData: [
                'summary' => 'Visit a store, book a fitting, or join the newsletter — one quiet path to stay close to the house and the people who make it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Stores & assistance',
                        heading: 'One quiet path to stay in touch',
                        summary: 'Three boutiques and a small client team. Book a fitting, ask about a material, or arrange an alteration — we reply within a working day.',
                        media: $media,
                        primaryLabel: 'Find a store',
                        secondaryLabel: 'Book a fitting',
                    ),
                    $this->featuresSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Prefer to be measured in person?',
                        summary: 'Book a private fitting at any of our boutiques. We will set aside the season and walk you through fit, fabric, and care.',
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
            title: 'Nothing in this edit yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing in this edit yet',
                'A calm empty state for a filtered collection view with no matching pieces.',
            ),
            renderData: [
                'summary' => 'Nothing matches that filter in the current season — but there is still plenty to see across the house.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'The catalogue',
                        heading: 'Nothing in this edit yet',
                        summary: 'No pieces match that filter this season. Clear it to see the full collection, or start from the essentials everyone reaches for.',
                        media: $media,
                        primaryLabel: 'View all collections',
                        secondaryLabel: 'Shop essentials',
                    ),
                    $this->categoryPathsSection(),
                    $this->seasonalCollectionsSection(),
                    $this->ctaSection(
                        heading: 'Looking for a particular piece?',
                        summary: 'Tell our store team what you have in mind and we will point you to it — or let you know when it returns.',
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
                'This page has been retired',
                'A not-found page that routes visitors back into the collections and store paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or been retired with last season — here is the way back into the collection.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'This page has been retired',
                        summary: 'The link is broken or the piece sold through with last season. Head back to the current collection, or speak with a store.',
                        media: $media,
                        primaryLabel: 'Back to home',
                        secondaryLabel: 'View collections',
                    ),
                    $this->categoryPathsSection(),
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell our store team what you needed and we will point you to the right collection.',
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
            name: self::BRAND . ' Join the House',
            title: 'Join the house — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Dress with a little more intention',
                'A focused conversion page inviting visitors to follow the house and shop the season.',
            ),
            renderData: [
                'summary' => 'Dress with a little more intention. Follow the house, shop the season, and keep the pieces you own in good shape.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Join the house',
                        heading: 'Dress with a little more intention',
                        summary: 'Fewer pieces, chosen well, cared for properly. Follow Atelier Nord for each collection, or come in for a fitting.',
                        media: $media,
                        primaryLabel: 'Shop the season',
                        secondaryLabel: 'Join the newsletter',
                    ),
                    $this->proofSection(),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'One season away',
                        summary: 'Join the newsletter and we will write the moment the next collection lands — never more than a few times a year.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function heroSection(
        string $eyebrow,
        string $heading,
        string $summary,
        array $media,
        string $primaryLabel,
        string $secondaryLabel,
    ): array {
        return [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'primary_label' => $primaryLabel,
            'primary_url' => '#seasonal-collections',
            'secondary_label' => $secondaryLabel,
            'secondary_url' => '#lookbook',
            'notes' => [
                'Cut for fit, not trend — sizing that stays consistent across seasons.',
                'Natural materials, traceable mills, and honest fibre content.',
                'A measured release pace: a few considered collections each year.',
            ],
            'actions' => [
                ['label' => $primaryLabel, 'url' => '#seasonal-collections', 'style' => 'primary'],
                ['label' => $secondaryLabel, 'url' => '#lookbook', 'style' => 'secondary'],
            ],
            'mediaUrl' => $media['hero'][0] ?? null,
            'mediaAlt' => self::BRAND . ' seasonal collection',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function seasonalCollectionsSection(): array
    {
        return [
            'type' => 'seasonal-collections',
            'heading' => 'A seasonal collection that unfolds quietly',
            'summary' => 'Each season builds on the last rather than replacing it. Three threads run through the current edit.',
            'items' => [
                ['title' => 'Outerwear', 'summary' => 'Double-faced wool coats and unlined jackets in stone, ink, and oat — cut to layer and built to last.'],
                ['title' => 'Knitwear', 'summary' => 'Lambswool and merino in close, honest gauges. Crews, cardigans, and a single roll-neck, dyed to wear with everything.'],
                ['title' => 'Essentials', 'summary' => 'The shirts, trousers, and tees you reach for first — refined a little each season, never reinvented.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function categoryPathsSection(): array
    {
        return [
            'type' => 'category-paths',
            'heading' => 'Find your way in',
            'summary' => 'Four sparse paths through the house — start wherever suits how you shop.',
            'items' => [
                ['title' => 'Women', 'summary' => 'Tailoring, knitwear, and dresses cut for an easy, considered line.'],
                ['title' => 'Men', 'summary' => 'Coats, shirting, and trousers in a quiet, hard-wearing palette.'],
                ['title' => 'Objects', 'summary' => 'Bags, scarves, and small leather goods made by the same mills and makers.'],
                ['title' => 'Stores', 'summary' => 'Three boutiques for fittings, alterations, and seeing the cloth in person.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function lookbookSection(array $media): array
    {
        return [
            'type' => 'lookbook-feature',
            'heading' => 'The Spring lookbook',
            'summary' => 'Shot on film across a single grey morning — the way the clothes actually move, not the way they sit on a hanger.',
            'label' => 'View the full lookbook',
            'url' => '#lookbook',
            'mediaUrl' => $media['detail'][0] ?? null,
            'mediaAlt' => self::BRAND . ' spring lookbook',
            'items' => [
                ['title' => 'On silhouette', 'summary' => 'Relaxed shoulders, longer lines, and a palette that holds together head to toe.'],
                ['title' => 'On detail', 'summary' => 'Horn buttons, French seams, and the small finishes you only notice on the second wear.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function materialNotesSection(): array
    {
        return [
            'type' => 'material-notes',
            'heading' => 'Notes on the cloth',
            'summary' => 'Where the fibres come from, and why we chose them.',
            'items' => [
                ['meta' => 'Linen', 'title' => 'Belgian wet-spun linen', 'summary' => 'Spun in Kortrijk from European flax. Cool, hard-wearing, and better with every wash.'],
                ['meta' => 'Wool', 'title' => 'Double-faced Italian wool', 'summary' => 'Woven in Biella with no lining and no glue, so a coat can be opened up and re-tailored.'],
                ['meta' => 'Scent', 'title' => 'Cedar & cold air', 'summary' => 'A single house fragrance built around cedar, vetiver, and the clean edge of a winter morning.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function productCareSection(): array
    {
        return [
            'type' => 'product-care',
            'heading' => 'Made to be kept',
            'summary' => 'A piece lasts longer when it is cared for well. Here is how we help.',
            'items' => [
                ['title' => 'Fabric care', 'summary' => 'Wash cool, dry flat, and steam rather than press. Each piece ships with care notes specific to its cloth.'],
                ['title' => 'Alterations', 'summary' => 'Free alterations in the first year, and a fair rate after. A coat should fit you, not the other way round.'],
                ['title' => 'Storage', 'summary' => 'Cedar blocks, breathable garment bags, and a quiet word on hanging knitwear (don\'t — fold it).'],
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
            'heading' => 'The pieces people come back for',
            'summary' => 'A short list of the essentials the house is known for.',
            'items' => [
                ['title' => 'The Nord Coat', 'summary' => 'Double-faced wool overcoat with an unlined body and a relaxed shoulder.', 'meta' => 'Outerwear', 'care_note' => 'Steam, do not press'],
                ['title' => 'The Everyday Shirt', 'summary' => 'Washed Belgian linen with a soft collar that softens further with wear.', 'meta' => 'Shirting', 'care_note' => 'Wash cool, dry flat'],
                ['title' => 'The Set Trouser', 'summary' => 'A straight wool trouser cut to sit at the waist and break clean over the shoe.', 'meta' => 'Trousers', 'care_note' => 'Hang to rest between wears'],
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
            'heading' => 'Quiet by design',
            'summary' => 'A few of the standards the house holds itself to.',
            'items' => [
                ['value' => 'Free first year', 'label' => 'Alterations on every garment'],
                ['value' => 'Traceable mills', 'label' => 'Named European mills for every fibre'],
                ['value' => 'Three stores', 'label' => 'Boutiques for fittings and care'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['category' => 'Outerwear', 'title' => 'The Nord Coat', 'summary' => 'Double-faced wool overcoat, unlined, cut to wear over knitwear or open through spring.'],
            ['category' => 'Knitwear', 'title' => 'The Roll-Neck', 'summary' => 'A close-gauge merino roll-neck in ink, oat, and moss — the quiet anchor of the season.'],
            ['category' => 'Shirting', 'title' => 'The Everyday Shirt', 'summary' => 'Washed Belgian linen with a soft, lived-in collar that only improves with wear.'],
            ['category' => 'Objects', 'title' => 'The Day Bag', 'summary' => 'Vegetable-tanned leather, hand-finished, sized for a notebook and a day out.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#piece-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => 'Pieces from across the current collection, newest first.',
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
            'heading' => 'Hear about each collection first',
            'summary' => 'A short note a few times a year when a new collection lands, plus the occasional word on care and fittings. Nothing more.',
            'action' => '#newsletter',
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
            'label' => 'Shop the collection',
            'url' => '#seasonal-collections',
            'actions' => [
                ['label' => 'Shop the collection', 'url' => '#seasonal-collections', 'style' => 'primary'],
                ['label' => 'View the lookbook', 'url' => '#lookbook', 'style' => 'secondary'],
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
                ['label' => 'Collections', 'url' => '#seasonal-collections'],
                ['label' => 'Lookbook', 'url' => '#lookbook'],
                ['label' => 'Materials', 'url' => '#materials'],
                ['label' => 'Product care', 'url' => '#product-care'],
                ['label' => 'Stores', 'url' => '#stores'],
            ],
            'ctaLabel' => 'Join the newsletter',
            'ctaUrl' => '#newsletter',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'A small fashion house designing quiet seasonal collections, lasting essentials, and clothes made to be kept.',
            'columns' => [
                [
                    'heading' => 'Shop',
                    'links' => [
                        ['label' => 'Women', 'url' => '#women'],
                        ['label' => 'Men', 'url' => '#men'],
                        ['label' => 'Accessories', 'url' => '#objects'],
                    ],
                ],
                [
                    'heading' => 'Service',
                    'links' => [
                        ['label' => 'Product care', 'url' => '#product-care'],
                        ['label' => 'Alterations', 'url' => '#alterations'],
                        ['label' => 'Stores', 'url' => '#stores'],
                    ],
                ],
                [
                    'heading' => 'Editorial',
                    'links' => [
                        ['label' => 'Lookbook', 'url' => '#lookbook'],
                        ['label' => 'Materials', 'url' => '#materials'],
                        ['label' => 'Newsletter', 'url' => '#newsletter'],
                    ],
                ],
            ],
            'items' => [
                [
                    'title' => 'Shop',
                    'links' => [
                        ['label' => 'Women', 'url' => '#women'],
                        ['label' => 'Men', 'url' => '#men'],
                        ['label' => 'Accessories', 'url' => '#objects'],
                    ],
                ],
                [
                    'title' => 'Service',
                    'links' => [
                        ['label' => 'Product care', 'url' => '#product-care'],
                        ['label' => 'Alterations', 'url' => '#alterations'],
                        ['label' => 'Stores', 'url' => '#stores'],
                    ],
                ],
                [
                    'title' => 'Editorial',
                    'links' => [
                        ['label' => 'Lookbook', 'url' => '#lookbook'],
                        ['label' => 'Materials', 'url' => '#materials'],
                        ['label' => 'Newsletter', 'url' => '#newsletter'],
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
