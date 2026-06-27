<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PremiumInfrastructure\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class PremiumInfrastructureScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-premium-infrastructure::sections.';

    private const string SCREEN_PREFIX = 'premium-infrastructure-';

    public function render(string $screen): View
    {
        $screen = $this->normalizeScreen($screen);

        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (PremiumInfrastructureScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-premium-infrastructure::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#101828',
                accentColor: '#12b8a6',
                neutralColor: '#172033',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#f7fbff',
                foregroundColor: '#101828',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'premium-infrastructure',
        ])->render();

        return view('capell-theme-premium-infrastructure::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    private function normalizeScreen(string $screen): string
    {
        return str_starts_with($screen, self::SCREEN_PREFIX)
            ? substr($screen, strlen(self::SCREEN_PREFIX))
            : $screen;
    }

    /**
     * @return array<int, PremiumInfrastructureScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'hero-base-desktop', 'hero-base-mobile' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'Infrastructure pages for serious technical buyers',
                    'summary' => 'A restrained hero for complex platforms where copy, CTAs, and product-system notes stay scannable before media is added.',
                ]),
                $this->section('product-panels'),
                $this->footer(),
            ],
            'hero-looping-video-desktop', 'hero-looping-video-mobile' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'Show product state changing in real time',
                    'summary' => 'A muted repeat preview can sit beside the enterprise message while the large header image grounds the platform story below.',
                    'preview_video_url' => 'data:video/mp4;base64,AAAAIGZ0eXBpc29tAAACAGlzb21pc28ybXA0MQ==',
                    'preview_image_url' => $this->imageDataUri('Looping dashboard preview', 'Autoplay poster frame', '#12b8a6', '#172033'),
                    'preview_alt' => 'Looping infrastructure dashboard preview',
                    'header_image_url' => $this->imageDataUri('Large platform header', 'Operational product surface', '#12b8a6', '#f7fbff', 1600, 700),
                    'header_image_alt' => 'Wide infrastructure platform header image',
                ]),
                $this->section('developer-tools'),
                $this->footer(),
            ],
            'hero-looping-gif-desktop' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'GIF-style loops for stateful product proof',
                    'summary' => 'Animated preview media can repeat in the hero panel for logs, alerts, requests, and workflow state changes.',
                    'media' => [
                        'preview_gif_url' => $this->imageDataUri('Animated API loop', 'GIF-style preview', '#38d9c7', '#172033'),
                        'preview_alt' => 'Animated infrastructure workflow loop',
                        'header_image_url' => $this->imageDataUri('Infrastructure workflow header', 'Wide technical product image', '#38d9c7', '#f7fbff', 1600, 700),
                        'header_image_alt' => 'Infrastructure workflow header image',
                    ],
                ]),
                $this->section('global-scale'),
                $this->footer(),
            ],
            'hero-image-only-desktop' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'Static product images keep enterprise polish',
                    'summary' => 'Still product imagery can use the same media slots for dashboards, maps, logs, and compliance evidence.',
                    'media' => [
                        'preview_image_url' => $this->imageDataUri('Still dashboard preview', 'Static preview fallback', '#12b8a6', '#172033'),
                        'preview_alt' => 'Still infrastructure dashboard preview',
                        'header_image_url' => $this->imageDataUri('Still platform header', 'Wide static product image', '#12b8a6', '#f7fbff', 1600, 700),
                        'header_image_alt' => 'Still infrastructure header image',
                    ],
                ]),
                $this->section('trust-compliance'),
                $this->footer(),
            ],
            'homepage' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'Infrastructure pages for serious technical buyers',
                    'summary' => 'A complete homepage composition with product panels, solutions, global scale, developer tools, and trust signals.',
                ]),
                $this->section('product-panels'),
                $this->section('solutions'),
                $this->section('global-scale'),
                $this->section('developer-tools'),
                $this->section('case-studies-news'),
                $this->section('trust-compliance'),
                $this->section('newsletter'),
                $this->footer(),
            ],
            'landing-page' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'Ship infrastructure your enterprise can trust',
                    'summary' => 'A focused landing page that leads with the platform message and backs it with proof and feature highlights.',
                ]),
                $this->section('proof'),
                $this->section('solutions'),
                $this->section('cta'),
                $this->footer(),
            ],
            'list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Product updates and resources',
                ]),
                $this->footer(),
            ],
            'search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results',
                ]),
                $this->footer(),
            ],
            'contact-form' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Talk to our infrastructure team',
                    'summary' => 'Reach out about solutions, developer tooling, compliance reviews, or enterprise requests.',
                ]),
                $this->section('newsletter'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): PremiumInfrastructureScreenshotSection
    {
        return new PremiumInfrastructureScreenshotSection($sectionKey, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hero(array $data = []): PremiumInfrastructureScreenshotSection
    {
        return $this->section('hero', array_merge([
            'primary_label' => 'Explore platform',
            'primary_url' => '#platform',
            'secondary_label' => 'Contact sales',
            'secondary_url' => '#sales',
        ], $data));
    }

    private function navigation(): PremiumInfrastructureScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Gridline',
            'items' => [
                ['label' => 'Platform', 'url' => '#platform'],
                ['label' => 'Solutions', 'url' => '#solutions'],
                ['label' => 'Developers', 'url' => '#developers'],
                ['label' => 'Enterprise', 'url' => '#enterprise'],
            ],
        ]);
    }

    private function footer(): PremiumInfrastructureScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Gridline',
        ]);
    }

    private function imageDataUri(string $title, string $subtitle, string $accent, string $background, int $width = 960, int $height = 600): string
    {
        $panelWidth = $width - 96;
        $panelHeight = $height - 96;
        $surfaceWidth = $width - 172;
        $surfaceHeight = $height - 192;
        $wideLineWidth = $width - 248;
        $shortLineWidth = $width - 420;
        $mediumLineWidth = $width - 320;
        $titleY = $height - 160;
        $subtitleY = $height - 96;

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
  <rect width="{$width}" height="{$height}" fill="{$background}"/>
  <rect x="48" y="48" width="{$panelWidth}" height="{$panelHeight}" rx="24" fill="{$accent}" opacity="0.18"/>
  <rect x="86" y="96" width="{$surfaceWidth}" height="{$surfaceHeight}" rx="18" fill="#f7fbff" opacity="0.95"/>
  <rect x="124" y="138" width="{$wideLineWidth}" height="48" rx="12" fill="{$accent}" opacity="0.32"/>
  <rect x="124" y="216" width="{$shortLineWidth}" height="38" rx="10" fill="#172033" opacity="0.22"/>
  <rect x="124" y="284" width="{$mediumLineWidth}" height="38" rx="10" fill="#172033" opacity="0.16"/>
  <text x="96" y="{$titleY}" font-family="Arial, sans-serif" font-size="52" font-weight="700" fill="#101828">{$title}</text>
  <text x="96" y="{$subtitleY}" font-family="Arial, sans-serif" font-size="28" fill="#475467">{$subtitle}</text>
</svg>
SVG;

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    private function titleFor(string $screen): string
    {
        return match ($this->normalizeScreen($screen)) {
            'hero-looping-video-desktop', 'hero-looping-video-mobile' => 'Premium Infrastructure looping video hero',
            'hero-looping-gif-desktop' => 'Premium Infrastructure GIF hero',
            'hero-image-only-desktop' => 'Premium Infrastructure image hero',
            'homepage' => 'Premium Infrastructure homepage',
            'landing-page' => 'Premium Infrastructure landing page',
            'list-page' => 'Premium Infrastructure list page',
            'search-results' => 'Premium Infrastructure search results',
            'contact-form' => 'Premium Infrastructure contact form',
            default => 'Premium Infrastructure base hero',
        };
    }
}
