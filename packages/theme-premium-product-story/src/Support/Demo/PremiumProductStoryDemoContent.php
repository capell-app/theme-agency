<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PremiumProductStory\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Premium Product Story theme.
 *
 * Every surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (product-families /
 * feature-highlights / gallery-strip / spec-comparison / ecosystem-story /
 * newsletter) alongside the hero and cta — giving each surface a full,
 * individual launch site for a single flagship product rather than the shared
 * five-section skeleton.
 */
final class PremiumProductStoryDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Aperture';

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
            title: self::BRAND . ' Studio One — Premium Product Story',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'The Aperture Studio One',
                'A premium product story for Studio One — a flagship camera built for makers who care about every frame.',
            ),
            renderData: [
                'summary' => 'Aperture Studio One is a flagship camera built for makers. One product, told properly: the range, the proof, the gallery, and the ecosystem around it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Studio One. Every frame, the way you saw it.',
                        summary: 'A full-frame camera engineered around one idea: get out of the way. Faster focus, quieter shutter, and colour science you can trust straight off the card.',
                        mediaUrl: $media['hero'][0],
                        mediaAlt: 'Aperture Studio One camera against a dark studio backdrop',
                        primaryLabel: 'Buy Studio One',
                        secondaryLabel: 'Compare the range',
                    ),
                    $this->productFamiliesSection(
                        heading: 'Three bodies. One mount.',
                        summary: 'Start where your work is today and grow without leaving the system. Every body shares the same lenses, batteries, and colour profiles.',
                    ),
                    $this->featureHighlightsSection(
                        heading: 'Built around the shot, not the spec sheet',
                    ),
                    $this->gallleryStripSection(
                        heading: 'Shot on Studio One',
                    ),
                    $this->specComparisonSection(
                        heading: 'The numbers, where they matter',
                    ),
                    $this->ecosystemStorySection(),
                    $this->ctaSection(
                        heading: 'Hold one before you decide',
                        summary: 'Order today with free returns, or book a hands-on session at an Aperture studio near you.',
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
            name: self::BRAND . ' Range',
            title: 'The Studio range — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The Studio range',
                'Compare every Studio body and find the one that fits the work you make today.',
            ),
            renderData: [
                'summary' => 'Compare every camera in the Studio range — Pro, Air, and Mini — side by side, then read the field stories behind each one.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Find the Studio that fits the work',
                        summary: 'Three bodies, one mount, one colour science. Browse the full range, compare the specs, and read how photographers actually use each one.',
                        mediaUrl: $media['listing'][0] ?? $media['hero'][0],
                        mediaAlt: 'The Aperture Studio camera range laid out together',
                        primaryLabel: 'Compare all models',
                        secondaryLabel: 'Talk to a specialist',
                    ),
                    $this->productFamiliesSection(
                        heading: 'The full Studio range',
                        summary: 'Three bodies that share everything but weight and reach. Pick the one that matches how you shoot.',
                    ),
                    $this->specComparisonSection(
                        heading: 'Specs, side by side',
                    ),
                    $this->contentListingSection(
                        heading: 'Field stories from the range',
                    ),
                    $this->ctaSection(
                        heading: 'Still deciding between bodies?',
                        summary: 'Tell us how you shoot and a specialist will recommend the body and two lenses to start with.',
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
            name: self::BRAND . ' Studio One Pro',
            title: 'Studio One Pro — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Studio One Pro',
                'The flagship body, in depth: sensor, autofocus, build, and the ecosystem that surrounds it.',
            ),
            renderData: [
                'summary' => 'The flagship body in full: a 61-megapixel full-frame sensor, dual-pixel autofocus, weather-sealed body, and the lenses and service plans built around it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Studio One Pro — the body that disappears',
                        summary: 'A 61-megapixel full-frame sensor, 8K capture, and a stacked readout fast enough that you stop thinking about the camera and start thinking about the frame.',
                        mediaUrl: $media['detail'][0],
                        mediaAlt: 'Close detail of the Studio One Pro body and dial layout',
                        primaryLabel: 'Buy Studio One Pro',
                        secondaryLabel: 'Add to compare',
                    ),
                    $this->featureHighlightsSection(
                        heading: 'What the Pro does differently',
                    ),
                    $this->gallleryStripSection(
                        heading: 'The Pro, in the field',
                    ),
                    $this->specComparisonSection(
                        heading: 'Pro specifications',
                    ),
                    $this->ecosystemStorySection(),
                    $this->ctaSection(
                        heading: 'Ready to make it yours?',
                        summary: 'Buy with a two-year care plan, or trade in your current body for instant credit at checkout.',
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
            title: 'Talk to Aperture — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Talk to Aperture',
                'Book a hands-on session, ask a specialist, or get help with an order.',
            ),
            renderData: [
                'summary' => 'Book a hands-on session at a studio, ask a product specialist which body fits your work, or get help with an existing order — a real person replies within one working day.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Talk to a Studio specialist',
                        summary: 'Email support@aperture.example or book a hands-on session at a studio near you. Specialists are makers themselves — they shoot the kit they sell.',
                        mediaUrl: $media['contact'][0],
                        mediaAlt: 'An Aperture studio counter with cameras on display',
                        primaryLabel: 'Email a specialist',
                        primaryUrl: 'mailto:support@aperture.example',
                        secondaryLabel: 'Book a studio session',
                    ),
                    $this->ecosystemStorySection(
                        heading: 'Service that stays with the kit',
                        summary: 'Every Studio body comes with a service plan, sensor cleaning, and firmware support for the life of the product.',
                    ),
                    $this->newsletterSection(),
                    $this->ctaSection(
                        heading: 'Prefer to hold one first?',
                        summary: 'Book a free 30-minute session and shoot with any body in the range before you buy.',
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
            name: self::BRAND . ' No Matches',
            title: 'No matching models — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing matches that filter yet',
                'A graceful empty state for a filtered range with no matching models.',
            ),
            renderData: [
                'summary' => 'No models match that filter yet — but the full Studio range is one tap away, and a specialist can point you to the right body.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'No models match that filter — yet',
                        summary: 'Nothing in the range fits those exact filters. Clear them to see all three bodies, or tell a specialist what you shoot and we will narrow it for you.',
                        primaryLabel: 'View the full range',
                        secondaryLabel: 'Ask a specialist',
                        secondaryUrl: 'mailto:support@aperture.example',
                    ),
                    $this->productFamiliesSection(
                        heading: 'The three bodies, while you are here',
                        summary: 'Every Studio body shares the same mount and colour science — so any of these is a safe place to start.',
                    ),
                    $this->contentListingSection(
                        heading: 'Field stories to browse instead',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us how you shoot and we will recommend a body and the first two lenses to pair with it.',
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
                'A not-found page that routes visitors back into the range and support paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back to the Studio range.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'This frame is out of focus',
                        summary: 'The link is broken or the page has moved. Head back to the range, or talk to a specialist who can find what you were after.',
                        primaryLabel: 'Back to the range',
                        primaryUrl: '/',
                        secondaryLabel: 'Ask a specialist',
                        secondaryUrl: 'mailto:support@aperture.example',
                    ),
                    $this->productFamiliesSection(
                        heading: 'Start with the range',
                        summary: 'Three bodies, one mount. Any of these is a good place to land.',
                    ),
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and a specialist will point you to the right page.',
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
            name: self::BRAND . ' Buy',
            title: 'Buy Studio One — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Make it yours',
                'A focused conversion page inviting visitors to buy or book a session.',
            ),
            renderData: [
                'summary' => 'Make Studio One yours — buy today with free returns and a two-year care plan, or book a hands-on session first.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        heading: 'Make Studio One yours',
                        summary: 'Free next-day delivery, free returns for 30 days, and a two-year care plan included on every body. Trade in your old kit for credit at checkout.',
                        mediaUrl: $media['cta'][0],
                        mediaAlt: 'Studio One camera packaged and ready to ship',
                        primaryLabel: 'Buy Studio One',
                        secondaryLabel: 'Book a session first',
                    ),
                    $this->proofSection(
                        heading: 'Why makers choose Aperture',
                    ),
                    $this->ecosystemStorySection(),
                    $this->ctaSection(
                        heading: 'One frame away',
                        summary: 'Order in the next hour for free next-day delivery, with a two-year care plan included.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function heroSection(
        string $heading,
        string $summary,
        ?string $mediaUrl = null,
        ?string $mediaAlt = null,
        string $primaryLabel = 'Buy Studio One',
        string $primaryUrl = '#buy',
        string $secondaryLabel = 'Compare models',
        string $secondaryUrl = '#compare',
    ): array {
        $section = [
            'type' => 'hero',
            'heading' => $heading,
            'summary' => $summary,
            'primary_label' => $primaryLabel,
            'primary_url' => $primaryUrl,
            'secondary_label' => $secondaryLabel,
            'secondary_url' => $secondaryUrl,
            'notes' => [
                'Full-frame Studio range, one shared lens mount.',
                'Free returns for 30 days on every body.',
                'Two-year care plan and sensor service included.',
            ],
        ];

        if ($mediaUrl !== null) {
            $section['header_image_url'] = $mediaUrl;
            $section['header_image_alt'] = $mediaAlt ?? 'Aperture Studio camera';
        }

        return $section;
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
                [
                    'title' => 'Studio One Pro',
                    'summary' => 'The flagship. A 61-megapixel full-frame sensor and stacked readout for studio, landscape, and commercial work that has to hold up at any size.',
                ],
                [
                    'title' => 'Studio One Air',
                    'summary' => 'The everyday body. A 33-megapixel sensor in a lighter frame — fast enough for events and weddings, light enough to carry all day.',
                ],
                [
                    'title' => 'Studio One Mini',
                    'summary' => 'The travel body. The same colour science and mount in a pocketable shell built for street, travel, and second-camera duty.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featureHighlightsSection(string $heading): array
    {
        return [
            'type' => 'feature-highlights',
            'heading' => $heading,
            'items' => [
                [
                    'title' => 'Trust-the-card colour',
                    'summary' => 'Aperture colour science is tuned for skin tones and foliage out of the box, so the files you import already look like the scene you remembered.',
                ],
                [
                    'title' => 'Focus that stays locked',
                    'summary' => 'Subject-aware autofocus tracks eyes, animals, and vehicles across 759 phase-detect points without hunting in low light.',
                ],
                [
                    'title' => 'A shutter you forget',
                    'summary' => 'A near-silent electronic shutter and five-axis stabilisation let you hand-hold slower than you would dare on any other body.',
                ],
                [
                    'title' => 'Built for the weather',
                    'summary' => 'A magnesium-alloy frame with full weather sealing, so rain, dust, and cold stop being a reason to leave the camera at home.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function gallleryStripSection(string $heading): array
    {
        return [
            'type' => 'gallery-strip',
            'heading' => $heading,
            'items' => [
                [
                    'meta' => 'Portrait',
                    'title' => 'Studio light, no retouching',
                    'summary' => 'Skin tones rendered with the warmth they had on set — the kind of frame that needs nothing more than an export.',
                ],
                [
                    'meta' => 'Landscape',
                    'title' => 'Dynamic range to spare',
                    'summary' => 'Fifteen stops of latitude hold the highlights in the sky and the shadows in the valley in a single exposure.',
                ],
                [
                    'meta' => 'Documentary',
                    'title' => 'Quiet enough to disappear',
                    'summary' => 'The silent shutter lets you work a room without the camera ever becoming the thing people notice.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function specComparisonSection(string $heading): array
    {
        return [
            'type' => 'spec-comparison',
            'heading' => $heading,
            'summary' => 'The specifications that change how a frame turns out — sensor, processing, and build, across the whole range.',
            'items' => [
                [
                    'title' => 'Sensor',
                    'summary' => 'Full-frame back-illuminated CMOS — 61 megapixels on the Pro, 33 on the Air, 26 on the Mini, all sharing the same colour pipeline.',
                ],
                [
                    'title' => 'Processing',
                    'summary' => 'The Aperture X2 engine handles 8K capture, real-time subject tracking, and on-device noise reduction up to ISO 102,400.',
                ],
                [
                    'title' => 'Build',
                    'summary' => 'Weather-sealed magnesium body, dual card slots, and a battery rated for 760 frames per charge on the Pro.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ecosystemStorySection(
        string $heading = 'A system, not just a camera',
        string $summary = 'The body is only the start. Lenses, accessories, and service plans all share the same mount and the same standard, so the kit grows with you.',
    ): array {
        return [
            'type' => 'ecosystem-story',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Explore the ecosystem',
            'url' => '#ecosystem',
            'items' => [
                [
                    'title' => 'Sixteen native lenses',
                    'summary' => 'From a 14mm wide to a 600mm telephoto, every lens shares the mount — no adapters, no compromises.',
                ],
                [
                    'title' => 'Care that lasts the kit',
                    'summary' => 'Sensor cleaning, firmware support, and a two-year care plan included on every body you buy.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(string $heading): array
    {
        return [
            'type' => 'proof',
            'heading' => $heading,
            'items' => [
                ['value' => '760', 'label' => 'Frames per charge on the Studio One Pro'],
                ['value' => '15 stops', 'label' => 'Dynamic range across the full range'],
                ['value' => '2 years', 'label' => 'Care plan and service included as standard'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading): array
    {
        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'items' => [
                [
                    'category' => 'Wedding',
                    'title' => 'A full day on the Studio One Air',
                    'summary' => 'How one photographer shot a twelve-hour wedding on a single body and two lenses without changing a battery.',
                ],
                [
                    'category' => 'Landscape',
                    'title' => 'Forty miles in, the Mini earns its place',
                    'summary' => 'Why the lightest body in the range is the one that actually comes on the long hikes.',
                ],
                [
                    'category' => 'Commercial',
                    'title' => 'Why the studio chose the Pro',
                    'summary' => 'A product studio on the 61-megapixel files that survive being cropped to a billboard.',
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
            'heading' => 'Studio notes, once a month',
            'summary' => 'Firmware updates, new lenses, and field stories from working photographers — no marketing filler, one email a month.',
            'action' => '#subscribe',
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
            'label' => 'Buy Studio One',
            'url' => '#buy',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'items' => [
                ['label' => 'Overview', 'url' => '#overview'],
                ['label' => 'The range', 'url' => '#models'],
                ['label' => 'Features', 'url' => '#features'],
                ['label' => 'Compare', 'url' => '#compare'],
                ['label' => 'Support', 'url' => '#support'],
            ],
            'buyUrl' => '#buy',
            'ctaLabel' => 'Buy Studio One',
            'ctaUrl' => '#buy',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A single flagship camera system, told properly. Built for makers who care about every frame.',
            'columns' => [
                [
                    'heading' => 'Shop',
                    'title' => 'Shop',
                    'links' => [
                        ['label' => 'Studio One Pro', 'url' => '#models'],
                        ['label' => 'Studio One Air', 'url' => '#models'],
                        ['label' => 'Studio One Mini', 'url' => '#models'],
                        ['label' => 'Lenses & accessories', 'url' => '#ecosystem'],
                    ],
                ],
                [
                    'heading' => 'Service',
                    'title' => 'Service',
                    'links' => [
                        ['label' => 'Support', 'url' => '#support'],
                        ['label' => 'Trade-in', 'url' => '#support'],
                        ['label' => 'Care plans', 'url' => '#support'],
                        ['label' => 'Firmware', 'url' => '#support'],
                    ],
                ],
                [
                    'heading' => 'Studio',
                    'title' => 'Studio',
                    'links' => [
                        ['label' => 'Field stories', 'url' => '#stories'],
                        ['label' => 'Book a session', 'url' => '#support'],
                        ['label' => 'support@aperture.example', 'url' => 'mailto:support@aperture.example'],
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
