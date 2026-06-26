<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LandingGallery\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class LandingGalleryScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-landing-gallery::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (LandingGalleryScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-landing-gallery::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#171717',
                accentColor: '#e15b2d',
                neutralColor: '#57534e',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'elevated',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'rounded-screenshot',
                radius: 'md',
                surfaceColor: '#fffaf3',
                foregroundColor: '#171717',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'landing-gallery',
        ])->render();

        return view('capell-theme-landing-gallery::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, LandingGalleryScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'landing-gallery-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('category-navigation'),
                $this->section('website-examples'),
                $this->section('paid-templates'),
                $this->section('partner-blocks'),
                $this->section('gallery-system'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'landing-gallery-landing-page' => [
                $this->navigation(),
                $this->section('utility-hero', [
                    'heading' => 'A landing-page gallery built to be browsed',
                    'summary' => 'A curated landing inspiration view keeps SaaS, ecommerce, and startup references premium and scannable without owning content records.',
                ]),
                $this->section('website-examples'),
                $this->section('gallery-system'),
                $this->section('cta'),
                $this->footer(),
            ],
            'landing-gallery-list-page' => [
                $this->navigation(),
                $this->section('utility-hero', [
                    'heading' => 'An archive of references kept easy to scan',
                    'summary' => 'A structured archive of websites, templates, and components stays premium and legible while the gallery stays decoupled from data.',
                ]),
                $this->section('content-listing'),
                $this->section('category-navigation'),
                $this->section('cta'),
                $this->footer(),
            ],
            'landing-gallery-search-results' => [
                $this->navigation(),
                $this->section('category-navigation', [
                    'heading' => 'Search across the gallery in one confident path',
                    'summary' => 'Category-led search results keep landing pages, templates, and components discoverable without the theme owning search state.',
                ]),
                $this->section('content-listing'),
                $this->section('website-examples'),
                $this->section('cta'),
                $this->footer(),
            ],
            'landing-gallery-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'One confident path to submit, pitch, or subscribe',
                    'summary' => 'A non-submitting conversion block proves the signup and submission journey feels like part of the gallery experience.',
                ]),
                $this->section('proof'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): LandingGalleryScreenshotSection
    {
        return new LandingGalleryScreenshotSection($sectionKey, $data);
    }

    private function navigation(): LandingGalleryScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Galleria',
            'items' => [
                ['label' => 'Websites', 'url' => '#website-examples'],
                ['label' => 'Templates', 'url' => '#paid-templates'],
                ['label' => 'Categories', 'url' => '#category-navigation'],
                ['label' => 'Partners', 'url' => '#partner-blocks'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): LandingGalleryScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A landing-page gallery worth saving',
            'eyebrow' => 'Landing Gallery',
            'summary' => 'A warm, search-led gallery homepage for SaaS, ecommerce, and startup landing pages with categories, templates, partners, and curated rows.',
            'actions' => [
                ['label' => 'Browse the gallery', 'url' => '#website-examples'],
                ['label' => 'Explore templates', 'url' => '#paid-templates'],
            ],
        ]);
    }

    private function footer(): LandingGalleryScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Galleria',
            'items' => [
                ['label' => 'Websites', 'url' => '#website-examples'],
                ['label' => 'Templates', 'url' => '#paid-templates'],
                ['label' => 'Categories', 'url' => '#category-navigation'],
                ['label' => 'Partners', 'url' => '#partner-blocks'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'landing-gallery-landing-page' => 'Theme Landing Gallery landing page',
            'landing-gallery-list-page' => 'Theme Landing Gallery archive',
            'landing-gallery-search-results' => 'Theme Landing Gallery search results',
            'landing-gallery-contact-form' => 'Theme Landing Gallery contact form',
            default => 'Theme Landing Gallery homepage',
        };
    }
}
