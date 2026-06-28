<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PackagingSupplier\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Packaging Supplier theme.
 *
 * The copy, section ordering, and brand tokens mirror the package's own
 * screenshot renderer (the theme marketing preview spec) so the installed
 * demo and the marketing screenshots read as the same B2B packaging supplier
 * site. Each surface is seeded as an ordered `render_data['sections']` list so
 * the page adapter emits the theme's product-range / materials / sustainability
 * / industries / sample-request renderers alongside the shared chrome.
 */
final class PackagingSupplierDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Verdant Pack Co.';

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
            title: self::BRAND . ' — Sustainable Packaging Supplier',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Packaging a brand can stand behind',
                'Verdant Pack Co. supplies sustainable packaging for food, produce, and consumer brands — with product ranges, materials proof, and sample-led journeys.',
            ),
            renderData: [
                'summary' => 'A credible B2B packaging supplier for product ranges, materials, sustainability proof, industries served, and sample-led journeys.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Packaging Supplier',
                        'heading' => 'Packaging a brand can stand behind',
                        'summary' => 'A credible B2B packaging homepage for product ranges, materials, sustainability proof, industries served, and sample-led journeys.',
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Sustainable packaging on the production line',
                    ],
                    $this->productRangeSection(
                        heading: 'A product range built to be scanned',
                        summary: 'Structured product cards keep the catalogue legible and trustworthy without the theme owning inventory records.',
                    ),
                    $this->materialsSection(),
                    $this->sustainabilitySection(),
                    $this->featuresSection(),
                    $this->industriesSection(),
                    $this->sampleRequestSection(
                        heading: 'Request a sample in one confident path',
                        summary: 'A non-submitting sample-request CTA proves the request journey feels like part of the supplier experience.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Turn intent into a requested sample',
                        summary: 'A conversion-focused CTA stack keeps the path to specifying packaging direct and premium.',
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
            name: self::BRAND . ' Product Range',
            title: 'Product range — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A product range built to be scanned',
                'A directory of packaging formats across food, produce, and consumer goods.',
            ),
            renderData: [
                'summary' => 'Structured product cards keep the catalogue legible and trustworthy without the theme owning inventory records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Product range',
                        'heading' => 'A product range built to be scanned',
                        'summary' => 'Browse the catalogue by format and material. Every product card carries the same materials and recyclability proof.',
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Packaging product range',
                    ],
                    $this->productRangeSection(
                        heading: 'A product range built to be scanned',
                        summary: 'Structured product cards keep the catalogue legible and trustworthy without the theme owning inventory records.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the catalogue',
                        summary: 'Smaller formats, seasonal lines, and made-to-order packaging.',
                    ),
                    $this->ctaSection(
                        heading: 'Turn intent into a requested sample',
                        summary: 'A conversion-focused CTA stack keeps the path to specifying packaging direct and premium.',
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
            title: 'Kraft produce punnet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A product profile that reads with confidence',
                'A single product view pairs materials and proof so buyers can specify with confidence.',
            ),
            renderData: [
                'summary' => 'A single product view pairs materials and proof so buyers can specify with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Product profile',
                        'heading' => 'Kraft produce punnet — recyclable, food-safe',
                        'summary' => 'A compostable produce punnet specified for soft fruit and salad lines, with materials and recyclability proof in one view.',
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Kraft produce punnet packaging',
                    ],
                    $this->productRangeSection(
                        heading: 'A product profile that reads with confidence',
                        summary: 'A single product view pairs materials and proof so buyers can specify with confidence.',
                    ),
                    $this->materialsSection(),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Turn intent into a requested sample',
                        summary: 'A conversion-focused CTA stack keeps the path to specifying packaging direct and premium.',
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
            name: self::BRAND . ' Samples',
            title: 'Request a sample — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the supplier through one confident path',
                'A non-submitting sample-request CTA proves the contact journey feels like part of the supplier experience.',
            ),
            renderData: [
                'summary' => 'A non-submitting sample-request CTA proves the contact journey feels like part of the supplier experience.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Samples',
                        'heading' => 'Reach the supplier through one confident path',
                        'summary' => 'Tell us the format and volume you need. We send physical samples and a materials spec within two working days.',
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Packaging samples ready to ship',
                    ],
                    $this->sampleRequestSection(
                        heading: 'Reach the supplier through one confident path',
                        summary: 'A non-submitting sample-request CTA proves the contact journey feels like part of the supplier experience.',
                    ),
                    $this->featuresSection(),
                    $this->ctaSection(
                        heading: 'Turn intent into a requested sample',
                        summary: 'A conversion-focused CTA stack keeps the path to specifying packaging direct and premium.',
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
                'An empty listing state stays premium and structured while the supplier prepares its catalogue.',
            ),
            renderData: [
                'summary' => 'An empty listing state stays premium and structured while the supplier prepares its catalogue.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Product range',
                        'heading' => 'Nothing published here yet',
                        'summary' => 'No products match that filter yet. Clear the filter to see the full catalogue, or request a sample of the format you need.',
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing published here yet',
                        'summary' => 'An empty listing state stays premium and structured while the supplier prepares its catalogue.',
                        'items' => [],
                    ],
                    $this->featuresSection(),
                    $this->ctaSection(
                        heading: 'Turn intent into a requested sample',
                        summary: 'A conversion-focused CTA stack keeps the path to specifying packaging direct and premium.',
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
                'A 404 state keeps the supplier credible and routes visitors back into the catalogue journey.',
            ),
            renderData: [
                'summary' => 'A 404 state keeps the supplier credible and routes visitors back into the catalogue journey.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page could not be found',
                        'summary' => 'The link is broken or the page has moved. Head back to the product range, or request a sample of the format you need.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View product range', 'url' => '#product-range', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'That page could not be found',
                        summary: 'A 404 state keeps the supplier credible and routes visitors back into the catalogue journey.',
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
            name: self::BRAND . ' Enquire',
            title: 'Turn intent into a requested sample — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a requested sample',
                'A conversion-focused CTA stack keeps the path to specifying packaging direct and premium.',
            ),
            renderData: [
                'summary' => 'A conversion-focused CTA stack keeps the path to specifying packaging direct and premium.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Request a sample',
                        'heading' => 'Turn intent into a requested sample',
                        'summary' => 'Whether you are specifying a single line or a full range, the path to a physical sample stays direct.',
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Verdant Pack Co. packaging samples',
                    ],
                    $this->sampleRequestSection(
                        heading: 'Request a sample in one confident path',
                        summary: 'A non-submitting sample-request CTA proves the request journey feels like part of the supplier experience.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Turn intent into a requested sample',
                        summary: 'A conversion-focused CTA stack keeps the path to specifying packaging direct and premium.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function productRangeSection(string $heading, string $summary): array
    {
        return [
            'type' => 'product-range',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Produce punnets', 'summary' => 'Recyclable and compostable punnets for soft fruit, salad, and tomatoes, sized to retail trays.'],
                ['title' => 'Kraft pouches', 'summary' => 'Resealable paper-based pouches for dry goods, with a home-compostable barrier liner.'],
                ['title' => 'Folding cartons', 'summary' => 'FSC-certified board cartons for chilled and ambient food, printed in food-safe inks.'],
                ['title' => 'Moulded fibre trays', 'summary' => 'Plastic-free trays moulded from recycled fibre for eggs, produce, and ready meals.'],
                ['title' => 'Shipping mailers', 'summary' => 'Curbside-recyclable e-commerce mailers with a paper tape closure and no plastic film.'],
                ['title' => 'Glass & jar closures', 'summary' => 'Refill-ready jars and metal closures for preserves, sauces, and dry pantry lines.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function materialsSection(): array
    {
        return [
            'type' => 'materials',
            'heading' => 'Materials chosen for the bin they end up in',
            'summary' => 'Every format is specified against a real end-of-life route, with the recyclability claim documented up front.',
            'items' => [
                ['title' => 'Recycled kraft paper', 'summary' => 'Minimum 70% post-consumer fibre, curbside recyclable, FSC chain-of-custody certified.'],
                ['title' => 'Moulded recycled fibre', 'summary' => 'Plastic-free, home-compostable, made from recovered paper and card.'],
                ['title' => 'Compostable bio-film', 'summary' => 'Certified to EN 13432 for industrial composting, used only where a barrier is unavoidable.'],
                ['title' => 'Mono-material rPET', 'summary' => 'Single-polymer recycled PET for clarity-critical lines, fully recyclable in PET streams.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sustainabilitySection(): array
    {
        return [
            'type' => 'sustainability',
            'heading' => 'Sustainability claims built to be trusted',
            'summary' => 'Structured recyclability and materials proof keeps environmental claims legible and credible.',
            'items' => [
                ['title' => '92% recyclable range', 'summary' => 'Nine in ten lines across the catalogue carry a documented curbside or store-return route.'],
                ['title' => 'Plastic reduced by 64%', 'summary' => 'Average plastic content per unit cut by nearly two thirds since 2021 across like-for-like formats.'],
                ['title' => 'FSC-certified board', 'summary' => 'All paper and board carries FSC chain-of-custody certification with traceable sourcing.'],
                ['title' => 'EN 13432 compostables', 'summary' => 'Every compostable format is independently certified for industrial composting.'],
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
            'heading' => 'A supplier that behaves like a partner',
            'summary' => 'The capabilities that keep brand and procurement teams specifying with confidence.',
            'items' => [
                ['title' => 'Free physical samples', 'summary' => 'Real packaging in your hands within two working days, with a full materials spec sheet.'],
                ['title' => 'Custom print & dieline', 'summary' => 'In-house structural and print origination, from dieline to food-safe artwork.'],
                ['title' => 'Low minimum orders', 'summary' => 'Pilot runs from 500 units so new lines can be tested before scaling.'],
                ['title' => 'Compliance documentation', 'summary' => 'Food-contact, recyclability, and migration certificates supplied with every order.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function industriesSection(): array
    {
        return [
            'type' => 'industries',
            'heading' => 'Trusted across food, produce, and retail',
            'summary' => 'Packaging specified for the constraints of each category we serve.',
            'items' => [
                ['title' => 'Fresh produce', 'summary' => 'Punnets and trays that protect soft fruit and salad while staying curbside recyclable.'],
                ['title' => 'Food & beverage', 'summary' => 'Barrier-ready cartons and pouches for chilled, ambient, and dry food lines.'],
                ['title' => 'Health & beauty', 'summary' => 'Refill-ready jars and cartons for brands moving away from single-use plastic.'],
                ['title' => 'E-commerce & retail', 'summary' => 'Right-sized mailers and gift packaging that protect product and brand in transit.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function sampleRequestSection(string $heading, string $summary): array
    {
        return [
            'type' => 'sample-request',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Pick your formats', 'summary' => 'Choose the punnets, pouches, or cartons you want to evaluate from the product range.'],
                ['title' => 'Tell us your volumes', 'summary' => 'Share your annual volumes and lines so we can match materials and minimum orders.'],
                ['title' => 'Receive physical samples', 'summary' => 'Real packaging and a materials spec arrive within two working days, no commitment.'],
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
            'heading' => 'Proof, not promises',
            'summary' => 'Outcomes from suppliers and brands across the catalogue.',
            'items' => [
                ['title' => '2 days', 'quote' => 'Average time from sample request to physical packaging in a buyer\'s hands.'],
                ['title' => '400+ brands', 'quote' => 'Food, produce, and retail brands specifying Verdant Pack formats since 2019.'],
                ['title' => '92% recyclable', 'quote' => 'Share of the catalogue with a documented curbside or store-return route.'],
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
                ['title' => 'Seasonal gift packaging', 'summary' => 'Limited-run recyclable cartons and sleeves for festive and seasonal lines.'],
                ['title' => 'Made-to-order trays', 'summary' => 'Custom moulded fibre trays tooled to your product dimensions.'],
                ['title' => 'Refill & returnable', 'summary' => 'Durable jars and crates for refill schemes and closed-loop retail.'],
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
                ['label' => 'Request a sample', 'url' => '#samples', 'style' => 'primary'],
                ['label' => 'View product range', 'url' => '#product-range', 'style' => 'secondary'],
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
                ['label' => 'Product range', 'url' => '#product-range'],
                ['label' => 'Materials', 'url' => '#materials'],
                ['label' => 'Sustainability', 'url' => '#sustainability'],
                ['label' => 'Samples', 'url' => '#samples'],
            ],
            'ctaLabel' => 'Request a sample',
            'ctaUrl' => '#samples',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A sustainable packaging supplier for food, produce, and consumer brands.',
            'columns' => [
                [
                    'heading' => 'Catalogue',
                    'links' => [
                        ['label' => 'Product range', 'url' => '#product-range'],
                        ['label' => 'Materials', 'url' => '#materials'],
                        ['label' => 'Sustainability', 'url' => '#sustainability'],
                        ['label' => 'Industries', 'url' => '#industries'],
                    ],
                ],
                [
                    'heading' => 'Supplier',
                    'links' => [
                        ['label' => 'About', 'url' => '#about'],
                        ['label' => 'Certifications', 'url' => '#sustainability'],
                        ['label' => 'Capabilities', 'url' => '#features'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Request a sample', 'url' => '#samples'],
                        ['label' => 'sales@verdantpack.example', 'url' => 'mailto:sales@verdantpack.example'],
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
