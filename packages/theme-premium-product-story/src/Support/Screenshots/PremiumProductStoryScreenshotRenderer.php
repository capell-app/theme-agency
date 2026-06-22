<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PremiumProductStory\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class PremiumProductStoryScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-premium-product-story::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (PremiumProductStoryScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-premium-product-story::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#0071e3',
                neutralColor: '#1d1d1f',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'product-story',
                mediaTreatment: 'immersive',
                radius: 'lg',
                surfaceColor: '#f5f5f7',
                foregroundColor: '#111111',
                headingScale: 'large',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'premium-product-story',
        ])->render();

        return view('capell-theme-premium-product-story::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, PremiumProductStoryScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'hero-base-desktop', 'hero-base-mobile' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'The launch page with no wasted motion',
                    'summary' => 'A focused product hero for premium launches where copy, CTAs, and modular buying notes stay readable before media is added.',
                ]),
                $this->section('product-families'),
                $this->footer(),
            ],
            'hero-looping-video-desktop', 'hero-looping-video-mobile' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'Show the product in motion, then land the range',
                    'summary' => 'The hero supports a muted repeat preview beside the launch copy and a wide header image directly beneath it.',
                    'preview_video_url' => 'data:video/mp4;base64,AAAAIGZ0eXBpc29tAAACAGlzb21pc28ybXA0MQ==',
                    'preview_image_url' => $this->imageDataUri('Looping product preview', 'Autoplay poster frame', '#0071e3', '#101014'),
                    'preview_alt' => 'Looping product interface preview',
                    'header_image_url' => $this->imageDataUri('Large product header image', 'Wide range photography', '#0071e3', '#f5f5f7', 1600, 700),
                    'header_image_alt' => 'Wide product range header image',
                ]),
                $this->section('feature-highlights'),
                $this->footer(),
            ],
            'hero-looping-gif-desktop' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'A GIF-style loop for fast product explanation',
                    'summary' => 'Animated preview media can repeat in the hero panel while the large header image carries the visual story below.',
                    'media' => [
                        'preview_gif_url' => $this->imageDataUri('Animated product loop', 'GIF-style preview', '#3b82f6', '#101014'),
                        'preview_alt' => 'Animated product feature loop',
                        'header_image_url' => $this->imageDataUri('Immersive product header', 'Gallery-ready header', '#3b82f6', '#f5f5f7', 1600, 700),
                        'header_image_alt' => 'Immersive product header image',
                    ],
                ]),
                $this->section('gallery-strip'),
                $this->footer(),
            ],
            'hero-image-only-desktop' => [
                $this->navigation(),
                $this->hero([
                    'heading' => 'Image-only media stays polished',
                    'summary' => 'When the product team only has still imagery, the preview panel and large header image use the same stable layout.',
                    'media' => [
                        'preview_image_url' => $this->imageDataUri('Still preview', 'Poster fallback only', '#0071e3', '#101014'),
                        'preview_alt' => 'Still product preview',
                        'header_image_url' => $this->imageDataUri('Still product header', 'Wide still image', '#0071e3', '#f5f5f7', 1600, 700),
                        'header_image_alt' => 'Still product header image',
                    ],
                ]),
                $this->section('spec-comparison'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): PremiumProductStoryScreenshotSection
    {
        return new PremiumProductStoryScreenshotSection($sectionKey, $data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function hero(array $data = []): PremiumProductStoryScreenshotSection
    {
        return $this->section('hero', array_merge([
            'primary_label' => 'Buy now',
            'primary_url' => '#buy',
            'secondary_label' => 'Compare models',
            'secondary_url' => '#compare',
        ], $data));
    }

    private function navigation(): PremiumProductStoryScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Studio Model',
            'items' => [
                ['label' => 'Overview', 'url' => '#overview'],
                ['label' => 'Models', 'url' => '#models'],
                ['label' => 'Features', 'url' => '#features'],
                ['label' => 'Compare', 'url' => '#compare'],
            ],
            'buyUrl' => '#buy',
        ]);
    }

    private function footer(): PremiumProductStoryScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Studio Model',
        ]);
    }

    private function imageDataUri(string $title, string $subtitle, string $accent, string $background, int $width = 960, int $height = 600): string
    {
        $panelWidth = $width - 96;
        $panelHeight = $height - 96;
        $surfaceWidth = $width - 168;
        $surfaceHeight = $height - 184;
        $circleX = $width - 160;
        $titleY = $height - 160;
        $subtitleY = $height - 96;

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" viewBox="0 0 {$width} {$height}">
  <rect width="{$width}" height="{$height}" fill="{$background}"/>
  <rect x="48" y="48" width="{$panelWidth}" height="{$panelHeight}" rx="32" fill="{$accent}" opacity="0.18"/>
  <rect x="84" y="92" width="{$surfaceWidth}" height="{$surfaceHeight}" rx="26" fill="#ffffff" opacity="0.92"/>
  <circle cx="{$circleX}" cy="150" r="48" fill="{$accent}" opacity="0.7"/>
  <text x="96" y="{$titleY}" font-family="Arial, sans-serif" font-size="54" font-weight="700" fill="#111111">{$title}</text>
  <text x="96" y="{$subtitleY}" font-family="Arial, sans-serif" font-size="28" fill="#3f3f46">{$subtitle}</text>
</svg>
SVG;

        return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'hero-looping-video-desktop', 'hero-looping-video-mobile' => 'Premium Product Story looping video hero',
            'hero-looping-gif-desktop' => 'Premium Product Story GIF hero',
            'hero-image-only-desktop' => 'Premium Product Story image hero',
            default => 'Premium Product Story base hero',
        };
    }
}
