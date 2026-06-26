<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Commerce\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class CommerceScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-commerce::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (CommerceScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-commerce::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#1f5f4a',
                accentColor: '#b94735',
                neutralColor: '#17211c',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#fffaf3',
                foregroundColor: '#17211c',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'commerce',
        ])->render();

        return view('capell-theme-commerce::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, CommerceScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'frontend-page-rendered-with-commerce-theme', 'commerce-homepage-layout' => [
                $this->navigation(),
                $this->hero(),
                $this->section('product-finder'),
                $this->section('collections'),
                $this->section('product-grid'),
                $this->section('catalog'),
                $this->section('proof'),
                $this->section('blog-teaser'),
                $this->section('cta'),
                $this->footer(),
            ],
            'commerce-collection-layout' => [
                $this->navigation(),
                $this->section('collections', [
                    'heading' => 'Collections built to be shopped, not just browsed',
                    'summary' => 'Structured range cards keep campaigns and stock signals scannable without the theme owning catalogue records.',
                ]),
                $this->section('product-grid'),
                $this->section('cta'),
                $this->footer(),
            ],
            'commerce-product-layout' => [
                $this->navigation(),
                $this->section('product-detail', [
                    'heading' => 'A product page that keeps purchase intent intact',
                    'summary' => 'A single product view pairs merchandising, proof, and comparison so shoppers can buy with confidence.',
                ]),
                $this->section('comparison'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'commerce-lookbook-layout' => [
                $this->navigation(),
                $this->section('lookbook', [
                    'heading' => 'Sell the range visually, not as a work gallery',
                    'summary' => 'An image-led lookbook keeps editorial styling tied to buyable product without becoming a portfolio.',
                ]),
                $this->section('product-grid'),
                $this->section('cta'),
                $this->footer(),
            ],
            'commerce-buying-guide-layout' => [
                $this->navigation(),
                $this->section('buying-guide', [
                    'heading' => 'Advice content that supports the buying decision',
                    'summary' => 'A structured buying guide keeps resource content tied to purchase paths instead of becoming a plain blog.',
                ]),
                $this->section('product-grid'),
                $this->section('cta'),
                $this->footer(),
            ],
            'commerce-newsletter-layout' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Turn browsing intent into owned audience',
                    'summary' => 'A non-submitting newsletter capture proves the sign-up journey feels like part of the storefront.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'commerce-store-event-layout' => [
                $this->navigation(),
                $this->section('store-event', [
                    'heading' => 'Promote launches without leaving the buying lane',
                    'summary' => 'A retail event layout keeps in-store moments and launches tied to merchandising rather than generic announcements.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'commerce-search-layout' => [
                $this->navigation(),
                $this->section('search', [
                    'heading' => 'Refine buying paths, not just filter a directory',
                    'summary' => 'A retail search layout keeps discovery tied to product without looking like a standard listing page.',
                ]),
                $this->section('product-grid'),
                $this->section('cta'),
                $this->footer(),
            ],
            'commerce-campaign-layout' => [
                $this->navigation(),
                $this->section('campaign', [
                    'heading' => 'Campaigns that stay tied to buying paths',
                    'summary' => 'A promotion campaign layout keeps seasonal pushes anchored to product instead of becoming a launch microsite.',
                ]),
                $this->section('promotion'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): CommerceScreenshotSection
    {
        return new CommerceScreenshotSection($sectionKey, $data);
    }

    private function navigation(): CommerceScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Marlowe & Field',
            'items' => [
                ['label' => 'Collections', 'url' => '#collections'],
                ['label' => 'Shop', 'url' => '#product-grid'],
                ['label' => 'Lookbook', 'url' => '#lookbook'],
                ['label' => 'Buying guides', 'url' => '#buying-guide'],
            ],
            'consultationUrl' => '#cta',
        ]);
    }

    private function hero(): CommerceScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A storefront that turns browsing into baskets',
            'eyebrow' => 'Editorial Commerce',
            'summary' => 'An image-led retail homepage for collections, product discovery, merchandising, social proof, and conversion-led journeys.',
            'actions' => [
                ['label' => 'Shop collections', 'url' => '#collections'],
                ['label' => 'Browse the catalog', 'url' => '#catalog'],
            ],
        ]);
    }

    private function footer(): CommerceScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Marlowe & Field',
            'items' => [
                ['label' => 'Collections', 'url' => '#collections'],
                ['label' => 'Shop', 'url' => '#product-grid'],
                ['label' => 'Lookbook', 'url' => '#lookbook'],
                ['label' => 'Buying guides', 'url' => '#buying-guide'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'commerce-collection-layout' => 'Theme Commerce collection',
            'commerce-product-layout' => 'Theme Commerce product detail',
            'commerce-lookbook-layout' => 'Theme Commerce lookbook',
            'commerce-buying-guide-layout' => 'Theme Commerce buying guide',
            'commerce-newsletter-layout' => 'Theme Commerce newsletter capture',
            'commerce-store-event-layout' => 'Theme Commerce retail event',
            'commerce-search-layout' => 'Theme Commerce product search',
            'commerce-campaign-layout' => 'Theme Commerce promotion campaign',
            default => 'Theme Commerce homepage',
        };
    }
}
