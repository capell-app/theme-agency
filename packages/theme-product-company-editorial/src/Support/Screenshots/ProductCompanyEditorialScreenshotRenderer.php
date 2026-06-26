<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ProductCompanyEditorial\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class ProductCompanyEditorialScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-product-company-editorial::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (ProductCompanyEditorialScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-product-company-editorial::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#7c5cff',
                neutralColor: '#18151f',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#fbfaf7',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'product-company-editorial',
        ])->render();

        return view('capell-theme-product-company-editorial::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, ProductCompanyEditorialScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'product-company-editorial-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('featured-posts'),
                $this->section('topic-areas'),
                $this->section('story-cards'),
                $this->section('product-updates'),
                $this->section('template-stories'),
                $this->section('author-callouts'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'product-company-editorial-landing-page' => [
                $this->navigation(),
                $this->section('featured-posts', [
                    'heading' => 'A featured story that leads the whole publication',
                    'summary' => 'A single editorial landing pairs a product update, design essay, or engineering post with supporting context so readers commit to the read.',
                ]),
                $this->section('story-cards'),
                $this->section('author-callouts'),
                $this->section('newsletter'),
                $this->footer(),
            ],
            'product-company-editorial-list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'An archive built to be scanned and trusted',
                    'summary' => 'Structured story cards keep product updates, essays, and templates legible without the theme owning content records.',
                ]),
                $this->section('topic-areas'),
                $this->section('cta'),
                $this->footer(),
            ],
            'product-company-editorial-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results across topics, authors, and formats',
                    'summary' => 'A discovery view keeps editorial results structured and premium while readers explore the publication.',
                ]),
                $this->section('topic-areas'),
                $this->footer(),
            ],
            'product-company-editorial-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Subscribe to the publication in one confident path',
                    'summary' => 'A non-submitting newsletter conversion proves the signup journey feels like part of the editorial experience.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): ProductCompanyEditorialScreenshotSection
    {
        return new ProductCompanyEditorialScreenshotSection($sectionKey, $data);
    }

    private function navigation(): ProductCompanyEditorialScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Foundry Journal',
            'items' => [
                ['label' => 'Featured', 'url' => '#featured-posts'],
                ['label' => 'Topics', 'url' => '#topic-areas'],
                ['label' => 'Product updates', 'url' => '#product-updates'],
                ['label' => 'Templates', 'url' => '#template-stories'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): ProductCompanyEditorialScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Editorial a product company can stand behind',
            'eyebrow' => 'Product Company Editorial',
            'summary' => 'A dark-masthead editorial homepage for featured posts, product updates, design essays, engineering stories, templates, and newsletter conversion.',
            'actions' => [
                ['label' => 'Read featured stories', 'url' => '#featured-posts'],
                ['label' => 'Subscribe', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function footer(): ProductCompanyEditorialScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Foundry Journal',
            'items' => [
                ['label' => 'Featured', 'url' => '#featured-posts'],
                ['label' => 'Topics', 'url' => '#topic-areas'],
                ['label' => 'Product updates', 'url' => '#product-updates'],
                ['label' => 'Templates', 'url' => '#template-stories'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'product-company-editorial-landing-page' => 'Theme Product Company Editorial landing page',
            'product-company-editorial-list-page' => 'Theme Product Company Editorial list page',
            'product-company-editorial-search-results' => 'Theme Product Company Editorial search results',
            'product-company-editorial-contact-form' => 'Theme Product Company Editorial contact form',
            default => 'Theme Product Company Editorial homepage',
        };
    }
}
