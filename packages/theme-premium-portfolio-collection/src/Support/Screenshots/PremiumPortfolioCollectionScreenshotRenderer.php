<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PremiumPortfolioCollection\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class PremiumPortfolioCollectionScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-premium-portfolio-collection::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (PremiumPortfolioCollectionScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-premium-portfolio-collection::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#1f6feb',
                neutralColor: '#121212',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#f7f7f2',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'premium-portfolio-collection',
        ])->render();

        return view('capell-theme-premium-portfolio-collection::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, PremiumPortfolioCollectionScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'hero-base-desktop', 'hero-base-mobile' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'A curated first screen for standout work',
                    'summary' => 'A portfolio collection hero for award-led browsing, creator metadata, and focused collection notes before rich media is added.',
                ]),
                $this->section('featured-portfolios'),
                $this->footer(),
            ],
            'hero-looping-video-desktop', 'hero-looping-video-mobile' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'Preview motion-heavy portfolios without losing hierarchy',
                    'summary' => 'The hero panel can hold a muted repeat preview while the large header image gives the collection a strong visual signal.',
                    'preview_video_url' => 'data:video/mp4;base64,AAAAIGZ0eXBpc29tAAACAGlzb21pc28ybXA0MQ==',
                    'preview_image_url' => $this->imageDataUri('Looping portfolio preview', 'Autoplay poster frame', '#1f6feb', '#121212'),
                    'preview_alt' => 'Looping portfolio gallery preview',
                    'header_image_url' => $this->imageDataUri('Large portfolio header', 'Curated collection image', '#1f6feb', '#f7f7f2', 1600, 700),
                    'header_image_alt' => 'Wide portfolio collection header image',
                ]),
                $this->section('portfolio-grid'),
                $this->footer(),
            ],
            'hero-looping-gif-desktop' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'GIF-style previews for quick browsing context',
                    'summary' => 'Animated preview media can repeat in the side panel while the collection keeps useful browsing metadata nearby.',
                    'media' => [
                        'preview_gif_url' => $this->imageDataUri('Animated portfolio loop', 'GIF-style preview', '#5b8cff', '#121212'),
                        'preview_alt' => 'Animated portfolio preview loop',
                        'header_image_url' => $this->imageDataUri('Awarded portfolio header', 'Wide editorial image', '#5b8cff', '#f7f7f2', 1600, 700),
                        'header_image_alt' => 'Awarded portfolio collection header image',
                    ],
                ]),
                $this->section('awarded-profiles'),
                $this->footer(),
            ],
            'hero-image-only-desktop' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'Still-image collections keep the same rhythm',
                    'summary' => 'The hero stays stable for teams that only have static thumbnails, screenshots, or editorial stills.',
                    'media' => [
                        'preview_image_url' => $this->imageDataUri('Still portfolio preview', 'Static preview fallback', '#1f6feb', '#121212'),
                        'preview_alt' => 'Still portfolio preview image',
                        'header_image_url' => $this->imageDataUri('Still portfolio header', 'Wide static collection image', '#1f6feb', '#f7f7f2', 1600, 700),
                        'header_image_alt' => 'Still portfolio header image',
                    ],
                ]),
                $this->section('creator-directory'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): PremiumPortfolioCollectionScreenshotSection
    {
        return new PremiumPortfolioCollectionScreenshotSection($sectionKey, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hero(array $data = []): PremiumPortfolioCollectionScreenshotSection
    {
        return $this->section('hero', array_merge([
            'primary_label' => 'Browse portfolios',
            'primary_url' => '#portfolios',
            'secondary_label' => 'Explore winners',
            'secondary_url' => '#winners',
        ], $data));
    }

    private function navigation(): PremiumPortfolioCollectionScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Index Gallery',
            'items' => [
                ['label' => 'Latest', 'url' => '#latest'],
                ['label' => 'Featured', 'url' => '#featured'],
                ['label' => 'Winners', 'url' => '#winners'],
                ['label' => 'Creators', 'url' => '#creators'],
            ],
        ]);
    }

    private function footer(): PremiumPortfolioCollectionScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Index Gallery',
        ]);
    }

    private function imageDataUri(string $title, string $subtitle, string $accent, string $background, int $width = 960, int $height = 600): string
    {
        $panelWidth = $width - 96;
        $panelHeight = $height - 96;
        $surfaceWidth = $width - 164;
        $surfaceHeight = $height - 176;
        $titleY = $height - 160;
        $subtitleY = $height - 96;

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
  <rect width="{$width}" height="{$height}" fill="{$background}"/>
  <rect x="48" y="48" width="{$panelWidth}" height="{$panelHeight}" rx="24" fill="{$accent}" opacity="0.2"/>
  <rect x="82" y="88" width="{$surfaceWidth}" height="{$surfaceHeight}" rx="20" fill="#ffffff" opacity="0.94"/>
  <rect x="118" y="124" width="240" height="180" rx="18" fill="{$accent}" opacity="0.35"/>
  <rect x="390" y="124" width="240" height="180" rx="18" fill="{$accent}" opacity="0.22"/>
  <text x="96" y="{$titleY}" font-family="Arial, sans-serif" font-size="52" font-weight="700" fill="#111111">{$title}</text>
  <text x="96" y="{$subtitleY}" font-family="Arial, sans-serif" font-size="28" fill="#3f3f46">{$subtitle}</text>
</svg>
SVG;

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'hero-looping-video-desktop', 'hero-looping-video-mobile' => 'Premium Portfolio Collection looping video hero',
            'hero-looping-gif-desktop' => 'Premium Portfolio Collection GIF hero',
            'hero-image-only-desktop' => 'Premium Portfolio Collection image hero',
            default => 'Premium Portfolio Collection base hero',
        };
    }
}
