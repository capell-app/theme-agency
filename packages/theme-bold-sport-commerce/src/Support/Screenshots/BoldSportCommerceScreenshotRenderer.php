<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\BoldSportCommerce\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class BoldSportCommerceScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-bold-sport-commerce::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (BoldSportCommerceScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-bold-sport-commerce::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#e23824',
                neutralColor: '#111111',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'campaign',
                mediaTreatment: 'framed',
                radius: 'sm',
                surfaceColor: '#f7f5ef',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'bold-sport-commerce',
        ])->render();

        return view('capell-theme-bold-sport-commerce::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, BoldSportCommerceScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'bold-sport-commerce-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('campaign-launch'),
                $this->section('category-jumps'),
                $this->section('features'),
                $this->section('community-offer'),
                $this->section('training-stories'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'bold-sport-commerce-landing-page' => [
                $this->navigation(),
                $this->section('campaign-launch', [
                    'heading' => 'A campaign drop built to convert at speed',
                    'summary' => 'A focused landing composition keeps the seasonal drop high-energy and direct without the theme owning catalogue records.',
                ]),
                $this->section('features'),
                $this->section('community-offer'),
                $this->section('cta'),
                $this->footer(),
            ],
            'bold-sport-commerce-list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'A product wall built to be scanned',
                    'summary' => 'Structured product cards keep the collection bold and legible without the theme owning catalogue records.',
                ]),
                $this->section('category-jumps'),
                $this->section('cta'),
                $this->footer(),
            ],
            'bold-sport-commerce-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results that stay fast and bold',
                    'summary' => 'A structured results wall keeps product discovery quick and confident across products, campaigns, and teamwear.',
                ]),
                $this->section('category-jumps'),
                $this->footer(),
            ],
            'bold-sport-commerce-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Join the drop-alert in one confident path',
                    'summary' => 'A non-submitting drop-alert CTA proves the conversion journey feels like part of the commerce experience.',
                ]),
                $this->section('community-offer'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): BoldSportCommerceScreenshotSection
    {
        return new BoldSportCommerceScreenshotSection($sectionKey, $data);
    }

    private function navigation(): BoldSportCommerceScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Vanta Athletics',
            'items' => [
                ['label' => 'Campaigns', 'url' => '#campaign-launch'],
                ['label' => 'Categories', 'url' => '#category-jumps'],
                ['label' => 'Training', 'url' => '#training-stories'],
                ['label' => 'Community', 'url' => '#community-offer'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): BoldSportCommerceScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Sport commerce a team can move fast behind',
            'eyebrow' => 'Bold Sport Commerce',
            'summary' => 'A high-energy commerce homepage for campaign drops, fast category jumps, product walls, training stories, and community offers.',
            'actions' => [
                ['label' => 'Shop the drop', 'url' => '#campaign-launch'],
                ['label' => 'Browse categories', 'url' => '#category-jumps'],
            ],
        ]);
    }

    private function footer(): BoldSportCommerceScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Vanta Athletics',
            'items' => [
                ['label' => 'Campaigns', 'url' => '#campaign-launch'],
                ['label' => 'Categories', 'url' => '#category-jumps'],
                ['label' => 'Training', 'url' => '#training-stories'],
                ['label' => 'Community', 'url' => '#community-offer'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'bold-sport-commerce-landing-page' => 'Theme Bold Sport Commerce landing page',
            'bold-sport-commerce-list-page' => 'Theme Bold Sport Commerce list page',
            'bold-sport-commerce-search-results' => 'Theme Bold Sport Commerce search results',
            'bold-sport-commerce-contact-form' => 'Theme Bold Sport Commerce contact form',
            default => 'Theme Bold Sport Commerce homepage',
        };
    }
}
