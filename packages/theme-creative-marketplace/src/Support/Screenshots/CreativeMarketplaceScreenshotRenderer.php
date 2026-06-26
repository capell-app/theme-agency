<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CreativeMarketplace\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class CreativeMarketplaceScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-creative-marketplace::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (CreativeMarketplaceScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-creative-marketplace::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#ea4c89',
                neutralColor: '#1f1722',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#fff7fb',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'creative-marketplace',
        ])->render();

        return view('capell-theme-creative-marketplace::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, CreativeMarketplaceScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'creative-marketplace-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('category-chips'),
                $this->section('shot-grid'),
                $this->section('briefs-pricing'),
                $this->section('agencies-services'),
                $this->section('profile-availability'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'creative-marketplace-landing-page' => [
                $this->navigation(),
                $this->section('service-hero', [
                    'heading' => 'A service landing page that sells the talent',
                    'summary' => 'A focused service hero pairs availability and proof so hiring teams can engage a designer with confidence.',
                ]),
                $this->section('profile-availability'),
                $this->section('briefs-pricing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'creative-marketplace-list-page' => [
                $this->navigation(),
                $this->section('shot-grid', [
                    'heading' => 'A shot listing built to be browsed',
                    'summary' => 'A structured grid of visual work keeps the marketplace expressive and scannable without owning portfolio records.',
                ]),
                $this->section('category-chips'),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'creative-marketplace-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results that read clearly',
                    'summary' => 'A discovery listing for designers, agencies, and services stays expressive while the theme owns no search records.',
                ]),
                $this->section('category-chips'),
                $this->section('agencies-services'),
                $this->footer(),
            ],
            'creative-marketplace-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'One confident path to start a conversation',
                    'summary' => 'A non-submitting conversion form proves the hiring inquiry journey feels like part of the marketplace experience.',
                ]),
                $this->section('profile-availability'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): CreativeMarketplaceScreenshotSection
    {
        return new CreativeMarketplaceScreenshotSection($sectionKey, $data);
    }

    private function navigation(): CreativeMarketplaceScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Palette & Pixel',
            'items' => [
                ['label' => 'Shots', 'url' => '#shot-grid'],
                ['label' => 'Designers', 'url' => '#profile-availability'],
                ['label' => 'Services', 'url' => '#agencies-services'],
                ['label' => 'Hire', 'url' => '#cta'],
            ],
            'consultationUrl' => '#cta',
        ]);
    }

    private function hero(): CreativeMarketplaceScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Hire the designer your work deserves',
            'eyebrow' => 'Creative Marketplace',
            'summary' => 'A bright, expressive marketplace homepage for shots, service categories, designer profiles, briefs, agencies, availability, and hiring journeys.',
            'actions' => [
                ['label' => 'Hire a designer', 'url' => '#cta'],
                ['label' => 'Browse shots', 'url' => '#shot-grid'],
            ],
        ]);
    }

    private function footer(): CreativeMarketplaceScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Palette & Pixel',
            'items' => [
                ['label' => 'Shots', 'url' => '#shot-grid'],
                ['label' => 'Designers', 'url' => '#profile-availability'],
                ['label' => 'Services', 'url' => '#agencies-services'],
                ['label' => 'Hire', 'url' => '#cta'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'creative-marketplace-landing-page' => 'Theme Creative Marketplace landing page',
            'creative-marketplace-list-page' => 'Theme Creative Marketplace list page',
            'creative-marketplace-search-results' => 'Theme Creative Marketplace search results',
            'creative-marketplace-contact-form' => 'Theme Creative Marketplace contact form',
            default => 'Theme Creative Marketplace homepage',
        };
    }
}
