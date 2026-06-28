<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Manufacturing\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Manufacturing theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (capabilities-grid /
 * certifications / facility-stats / case-studies / rfq-form) alongside the
 * standard hero/proof/features/cta — giving every surface a full, individual
 * industrial-manufacturer site rather than the shared five-section skeleton.
 *
 * Copy, brand, and section ordering are mined verbatim from the theme's
 * ManufacturingScreenshotRenderer so the seeded demo matches the marketing
 * screenshots key-for-key.
 */
final class ManufacturingDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Axion Precision Works';

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
            title: self::BRAND . ' — Precision Manufacturing',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Precision manufacturing buyers can stand behind',
                'An industrial homepage for capabilities, certifications, facility metrics, case studies, and RFQ-led journeys.',
            ),
            renderData: [
                'summary' => 'Axion Precision Works is a contract manufacturer for capabilities, certifications, facility metrics, case studies, and RFQ-led journeys.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Manufacturing',
                        'heading' => 'Precision manufacturing buyers can stand behind',
                        'summary' => 'An industrial homepage for capabilities, certifications, facility metrics, case studies, and RFQ-led journeys.',
                        'actions' => [
                            ['label' => 'Request a quote', 'url' => '#rfq', 'style' => 'primary'],
                            ['label' => 'View capabilities', 'url' => '#capabilities', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Axion Precision Works production floor',
                    ],
                    $this->capabilitiesSection(
                        heading: 'Capabilities built to be scanned and trusted',
                        summary: 'Structured capability groupings keep production processes legible without owning part records.',
                    ),
                    $this->certificationsSection(),
                    $this->facilityStatsSection(),
                    $this->caseStudiesSection(
                        heading: 'Production case studies buyers can trust',
                        summary: 'Editorial case study cards keep delivered work credible without the theme owning project records.',
                        media: $media,
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Turn intent into a submitted RFQ',
                        summary: 'A conversion-focused CTA stack keeps the path to requesting a quote direct and precise.',
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
            name: self::BRAND . ' Capabilities',
            title: 'Capabilities — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A capability directory built to be scanned',
                'Structured capability cards keep the production catalogue legible without the theme owning part records.',
            ),
            renderData: [
                'summary' => 'Structured capability cards keep the production catalogue legible without the theme owning part records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Capabilities',
                        'heading' => 'A capability directory built to be scanned',
                        'summary' => 'Structured capability cards keep the production catalogue legible without the theme owning part records.',
                        'actions' => [
                            ['label' => 'Request a quote', 'url' => '#rfq', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Axion capability directory',
                    ],
                    $this->contentListingSection(
                        heading: 'A capability directory built to be scanned',
                        summary: 'Structured capability cards keep the production catalogue legible without the theme owning part records.',
                        media: $media,
                    ),
                    $this->caseStudiesSection(
                        heading: 'Production case studies buyers can trust',
                        summary: 'Editorial case study cards keep delivered work credible without the theme owning project records.',
                        media: $media,
                    ),
                    $this->ctaSection(
                        heading: 'Turn intent into a submitted RFQ',
                        summary: 'A conversion-focused CTA stack keeps the path to requesting a quote direct and precise.',
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
            name: self::BRAND . ' Case Study',
            title: 'Production case study — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A production case study that reads with authority',
                'A single case study view pairs certifications and facility metrics so buyers can engage with confidence.',
            ),
            renderData: [
                'summary' => 'A single case study view pairs certifications and facility metrics so buyers can engage with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Case study',
                        'heading' => 'A production case study that reads with authority',
                        'summary' => 'A single case study view pairs certifications and facility metrics so buyers can engage with confidence.',
                        'actions' => [
                            ['label' => 'View all case studies', 'url' => '#case-studies', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Axion production case study',
                    ],
                    $this->caseStudiesSection(
                        heading: 'A production case study that reads with authority',
                        summary: 'A single case study view pairs certifications and facility metrics so buyers can engage with confidence.',
                        media: $media,
                    ),
                    $this->certificationsSection(),
                    $this->facilityStatsSection(),
                    $this->ctaSection(
                        heading: 'Turn intent into a submitted RFQ',
                        summary: 'A conversion-focused CTA stack keeps the path to requesting a quote direct and precise.',
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
            name: self::BRAND . ' Request a Quote',
            title: 'Request a quote — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the plant through one confident path',
                'A non-submitting RFQ form proves the contact journey feels like part of the production experience.',
            ),
            renderData: [
                'summary' => 'A non-submitting RFQ form proves the contact journey feels like part of the production experience.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Request a quote',
                        'heading' => 'Reach the plant through one confident path',
                        'summary' => 'A non-submitting RFQ form proves the contact journey feels like part of the production experience.',
                        'actions' => [
                            ['label' => 'Request a quote', 'url' => '#rfq', 'style' => 'primary'],
                            ['label' => 'View capabilities', 'url' => '#capabilities', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'Axion Precision Works plant',
                    ],
                    $this->rfqFormSection(
                        heading: 'Reach the plant through one confident path',
                        summary: 'A non-submitting RFQ form proves the contact journey feels like part of the production experience.',
                    ),
                    $this->featuresSection(),
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
                'An empty listing state stays precise and structured while the plant prepares its content.',
            ),
            renderData: [
                'summary' => 'An empty listing state stays precise and structured while the plant prepares its content.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Capabilities',
                        'heading' => 'Nothing published here yet',
                        'summary' => 'An empty listing state stays precise and structured while the plant prepares its content.',
                        'actions' => [
                            ['label' => 'View capabilities', 'url' => '#capabilities', 'style' => 'primary'],
                            ['label' => 'Request a quote', 'url' => '#rfq', 'style' => 'secondary'],
                        ],
                    ],
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing published here yet',
                        'summary' => 'An empty listing state stays precise and structured while the plant prepares its content.',
                        'items' => [],
                    ],
                    $this->capabilitiesSection(
                        heading: 'Capabilities built to be scanned and trusted',
                        summary: 'Structured capability groupings keep production processes legible without owning part records.',
                    ),
                    $this->ctaSection(
                        heading: 'Turn intent into a submitted RFQ',
                        summary: 'A conversion-focused CTA stack keeps the path to requesting a quote direct and precise.',
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
                'A 404 state keeps the manufacturer credible and routes visitors back into the production journey.',
            ),
            renderData: [
                'summary' => 'A 404 state keeps the manufacturer credible and routes visitors back into the production journey.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page could not be found',
                        'summary' => 'A 404 state keeps the manufacturer credible and routes visitors back into the production journey.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View capabilities', 'url' => '#capabilities', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'That page could not be found',
                        summary: 'A 404 state keeps the manufacturer credible and routes visitors back into the production journey.',
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
            name: self::BRAND . ' Request a Quote',
            title: 'Turn intent into a submitted RFQ — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a submitted RFQ',
                'A conversion-focused CTA stack keeps the path to requesting a quote direct and precise.',
            ),
            renderData: [
                'summary' => 'A conversion-focused CTA stack keeps the path to requesting a quote direct and precise.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Request a quote',
                        'heading' => 'Request a quote in one confident path',
                        'summary' => 'A non-submitting RFQ form proves the quote journey feels like part of the production experience.',
                        'actions' => [
                            ['label' => 'Request a quote', 'url' => '#rfq', 'style' => 'primary'],
                            ['label' => 'View capabilities', 'url' => '#capabilities', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Axion Precision Works RFQ',
                    ],
                    $this->rfqFormSection(
                        heading: 'Request a quote in one confident path',
                        summary: 'A non-submitting RFQ form proves the quote journey feels like part of the production experience.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Turn intent into a submitted RFQ',
                        summary: 'A conversion-focused CTA stack keeps the path to requesting a quote direct and precise.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function capabilitiesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'capabilities-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'CNC machining', 'summary' => '3-, 4-, and 5-axis milling and turning held to tight tolerances across aluminium, steel, and exotics.'],
                ['title' => 'Sheet metal fabrication', 'summary' => 'Laser cutting, forming, and welding for enclosures, brackets, and structural assemblies.'],
                ['title' => 'Assembly & integration', 'summary' => 'Mechanical and electromechanical assembly with in-line inspection and traceable build records.'],
                ['title' => 'Finishing & coating', 'summary' => 'Anodising, powder coat, passivation, and plating sourced through qualified partners.'],
                ['title' => 'Quality & inspection', 'summary' => 'CMM, first-article inspection, and full dimensional reporting on every production run.'],
                ['title' => 'Prototyping', 'summary' => 'Low-volume and rapid prototype builds that move designs from drawing to part fast.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function certificationsSection(): array
    {
        return [
            'type' => 'certifications',
            'heading' => 'Certifications buyers can verify',
            'summary' => 'Audited, current, and traceable — the standards that let regulated buyers engage with confidence.',
            'items' => [
                ['title' => 'ISO 9001:2015', 'summary' => 'Quality management system audited annually by an accredited registrar.'],
                ['title' => 'AS9100D', 'summary' => 'Aerospace quality standard covering risk, configuration, and traceability.'],
                ['title' => 'ITAR registered', 'summary' => 'Registered with the directorate for defense trade controls for controlled work.'],
                ['title' => 'IATF 16949', 'summary' => 'Automotive quality standard for production and service part suppliers.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function facilityStatsSection(): array
    {
        return [
            'type' => 'facility-stats',
            'heading' => 'A facility sized for production',
            'summary' => 'The numbers behind the plant — capacity that buyers can plan a programme around.',
            'items' => [
                ['name' => 'Plant floor', 'title' => '120,000 sq ft', 'summary' => 'Climate-controlled production and assembly space under one roof.'],
                ['name' => 'Machine tools', 'title' => '48 machines', 'summary' => 'Multi-axis mills and lathes running across two shifts.'],
                ['name' => 'On-time delivery', 'title' => '99.2%', 'summary' => 'Measured across the last twelve months of production orders.'],
                ['name' => 'Annual capacity', 'title' => '2.4M parts', 'summary' => 'Sustained production volume across the active customer base.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function caseStudiesSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['detail'], $media['listing'], $media['proof'])));

        $studies = [
            ['title' => 'Aerospace bracket programme', 'summary' => 'A move to 5-axis machining cut a structural bracket from nine operations to three and held tolerance over a 40,000-part run.'],
            ['title' => 'Medical housing launch', 'summary' => 'First-article through full production in eleven weeks, with full dimensional reporting cleared on the first submission.'],
            ['title' => 'Automotive sensor assembly', 'summary' => 'In-line inspection and traceable build records took a launch supplier from 96% to 99.8% first-pass yield.'],
        ];

        $items = [];

        foreach ($studies as $index => $study) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$study,
                'url' => '#case-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
                'imageAlt' => $study['title'],
            ];
        }

        return [
            'type' => 'case-studies',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(): array
    {
        return [
            'type' => 'proof',
            'heading' => 'Proof buyers can stand behind',
            'summary' => 'Outcomes from recent production programmes.',
            'items' => [
                ['metric' => '99.2%', 'name' => 'On-time delivery', 'quote' => 'Measured across the last twelve months of production orders.'],
                ['metric' => '11 wks', 'name' => 'First-article to production', 'quote' => 'Typical lead time from kickoff to qualified production parts.'],
                ['metric' => '40+', 'name' => 'Active programmes', 'quote' => 'Across aerospace, medical, and automotive customers.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'CNC machining', 'summary' => 'Multi-axis milling and turning across aluminium, steel, and exotics.'],
            ['title' => 'Sheet metal fabrication', 'summary' => 'Laser cutting, forming, and welding for enclosures and assemblies.'],
            ['title' => 'Assembly & integration', 'summary' => 'Mechanical and electromechanical assembly with traceable build records.'],
            ['title' => 'Finishing & coating', 'summary' => 'Anodising, powder coat, passivation, and plating through qualified partners.'],
            ['title' => 'Quality & inspection', 'summary' => 'CMM, first-article inspection, and full dimensional reporting.'],
            ['title' => 'Prototyping', 'summary' => 'Low-volume and rapid prototype builds from drawing to part.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#capability-' . ($index + 1),
                'image' => $image,
                'imageUrl' => $image,
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'gallery',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(): array
    {
        return [
            'type' => 'features',
            'heading' => 'What to expect from the plant',
            'summary' => 'How an engagement runs once an RFQ lands.',
            'items' => [
                ['title' => 'One point of contact', 'summary' => 'A named engineer owns your programme from quote through delivery.'],
                ['title' => 'Quote within two days', 'summary' => 'Most RFQs come back with pricing and lead time inside two working days.'],
                ['title' => 'Traceable production', 'summary' => 'Full build records and dimensional reporting on every order.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function rfqFormSection(string $heading, string $summary): array
    {
        return [
            'type' => 'rfq-form',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Part details', 'summary' => 'Share drawings, material, tolerance, and finish so we can quote accurately.'],
                ['title' => 'Volume & timing', 'summary' => 'Tell us annual volume and target dates and we will map capacity to them.'],
                ['title' => 'Compliance needs', 'summary' => 'Flag any certification or controlled-work requirements up front.'],
            ],
            'actions' => [
                ['label' => 'Request a quote', 'url' => '#rfq', 'style' => 'primary'],
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
                ['label' => 'Request a quote', 'url' => '#rfq', 'style' => 'primary'],
                ['label' => 'View capabilities', 'url' => '#capabilities', 'style' => 'secondary'],
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
                ['label' => 'Capabilities', 'url' => '#capabilities'],
                ['label' => 'Certifications', 'url' => '#certifications'],
                ['label' => 'Case studies', 'url' => '#case-studies'],
                ['label' => 'Request a quote', 'url' => '#rfq'],
            ],
            'ctaLabel' => 'Request a quote',
            'ctaUrl' => '#rfq',
            'consultationUrl' => '#rfq',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A contract manufacturer for capabilities, certifications, facility metrics, case studies, and RFQ-led journeys.',
            'columns' => [
                [
                    'heading' => 'Capabilities',
                    'links' => [
                        ['label' => 'CNC machining', 'url' => '#capabilities'],
                        ['label' => 'Sheet metal fabrication', 'url' => '#capabilities'],
                        ['label' => 'Assembly & integration', 'url' => '#capabilities'],
                        ['label' => 'Quality & inspection', 'url' => '#capabilities'],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'links' => [
                        ['label' => 'Certifications', 'url' => '#certifications'],
                        ['label' => 'Case studies', 'url' => '#case-studies'],
                        ['label' => 'Facility', 'url' => '#facility'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Request a quote', 'url' => '#rfq'],
                        ['label' => 'rfq@axionprecision.example', 'url' => 'mailto:rfq@axionprecision.example'],
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
