<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ResourceHub\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class ResourceHubScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-resource-hub::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (ResourceHubScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-resource-hub::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#101828',
                accentColor: '#2563eb',
                neutralColor: '#475467',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'screenshot',
                radius: 'md',
                surfaceColor: '#ffffff',
                foregroundColor: '#101828',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'resource-hub',
        ])->render();

        return view('capell-theme-resource-hub::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, ResourceHubScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'resource-hub-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('category-navigation'),
                $this->section('website-examples'),
                $this->section('social-templates'),
                $this->section('courses-books'),
                $this->section('learning-resources'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'resource-hub-landing-page' => [
                $this->navigation(),
                $this->section('utility-hero', [
                    'heading' => 'A landing resource page built to convert',
                    'summary' => 'A focused resource landing pairs examples, templates, courses, and books so visitors can act without the theme owning records.',
                ]),
                $this->section('website-examples'),
                $this->section('courses-books'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'resource-hub-list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'An archive built to be scanned',
                    'summary' => 'Structured archive cards keep examples, templates, courses, and books legible without the theme owning content records.',
                ]),
                $this->section('category-navigation'),
                $this->section('cta'),
                $this->footer(),
            ],
            'resource-hub-search-results' => [
                $this->navigation(),
                $this->section('category-navigation', [
                    'heading' => 'Search across every resource in one path',
                    'summary' => 'A search-led discovery view keeps examples, templates, courses, books, and learning resources fast to filter and trust.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'resource-hub-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'One confident path to subscribe or submit',
                    'summary' => 'A non-submitting newsletter and submission CTA proves the conversion journey feels like part of the hub experience.',
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
    private function section(string $sectionKey, array $data = []): ResourceHubScreenshotSection
    {
        return new ResourceHubScreenshotSection($sectionKey, $data);
    }

    private function navigation(): ResourceHubScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Resource Hub',
            'items' => [
                ['label' => 'Examples', 'url' => '#website-examples'],
                ['label' => 'Templates', 'url' => '#social-templates'],
                ['label' => 'Courses', 'url' => '#courses-books'],
                ['label' => 'Learning', 'url' => '#learning-resources'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): ResourceHubScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Inspiration and education in one search-led hub',
            'eyebrow' => 'Resource Hub',
            'summary' => 'A bright education homepage for website examples, social templates, courses, books, and learning resources discovered through fast search.',
            'actions' => [
                ['label' => 'Browse examples', 'url' => '#website-examples'],
                ['label' => 'Explore templates', 'url' => '#social-templates'],
            ],
        ]);
    }

    private function footer(): ResourceHubScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Resource Hub',
            'items' => [
                ['label' => 'Examples', 'url' => '#website-examples'],
                ['label' => 'Templates', 'url' => '#social-templates'],
                ['label' => 'Courses', 'url' => '#courses-books'],
                ['label' => 'Learning', 'url' => '#learning-resources'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'resource-hub-landing-page' => 'Theme Resource Hub landing page',
            'resource-hub-list-page' => 'Theme Resource Hub archive',
            'resource-hub-search-results' => 'Theme Resource Hub search results',
            'resource-hub-contact-form' => 'Theme Resource Hub contact form',
            default => 'Theme Resource Hub homepage',
        };
    }
}
