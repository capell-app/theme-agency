<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OnePageShowcase\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class OnePageShowcaseScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-one-page-showcase::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (OnePageShowcaseScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-one-page-showcase::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#2f1d12',
                accentColor: '#ff7a3d',
                neutralColor: '#3a271c',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#fff8ef',
                foregroundColor: '#2f1d12',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'one-page-showcase',
        ])->render();

        return view('capell-theme-one-page-showcase::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, OnePageShowcaseScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'one-page-showcase-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('showcase-hero'),
                $this->section('category-tabs'),
                $this->section('one-page-grid'),
                $this->section('templates-sections'),
                $this->section('tools-sponsors'),
                $this->section('build-resources'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'one-page-showcase-landing-page' => [
                $this->navigation(),
                $this->section('showcase-hero', [
                    'heading' => 'A landing page built to be screenshotted',
                    'summary' => 'A focused one-page landing composition keeps the showcase warm and spacious while proving the template journey end to end.',
                ]),
                $this->section('templates-sections'),
                $this->section('cta'),
                $this->footer(),
            ],
            'one-page-showcase-list-page' => [
                $this->navigation(),
                $this->section('category-tabs', [
                    'heading' => 'An archive of one-pagers built to be scanned',
                    'summary' => 'Spacious cards and compact tags keep the gallery legible without the theme owning catalogue records.',
                ]),
                $this->section('one-page-grid'),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'one-page-showcase-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results that stay warm and spacious',
                    'summary' => 'A structured results listing keeps discovery premium and legible while the showcase stays content-agnostic.',
                ]),
                $this->section('one-page-grid'),
                $this->footer(),
            ],
            'one-page-showcase-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Reach the showcase through one warm path',
                    'summary' => 'A non-submitting conversion form proves the submit and newsletter journey feels like part of the showcase experience.',
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
    private function section(string $sectionKey, array $data = []): OnePageShowcaseScreenshotSection
    {
        return new OnePageShowcaseScreenshotSection($sectionKey, $data);
    }

    private function navigation(): OnePageShowcaseScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'OnePage Gallery',
            'items' => [
                ['label' => 'One-pagers', 'url' => '#one-page-grid'],
                ['label' => 'Templates', 'url' => '#templates-sections'],
                ['label' => 'Tools', 'url' => '#tools-sponsors'],
                ['label' => 'Resources', 'url' => '#build-resources'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): OnePageShowcaseScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A showcase of one-pagers worth shipping',
            'eyebrow' => 'One Page Showcase',
            'summary' => 'A warm, spacious gallery homepage for curated one-pagers, templates, sections, tools, sponsors, and build resources.',
            'actions' => [
                ['label' => 'Browse the gallery', 'url' => '#one-page-grid'],
                ['label' => 'Submit a one-pager', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function footer(): OnePageShowcaseScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'OnePage Gallery',
            'items' => [
                ['label' => 'One-pagers', 'url' => '#one-page-grid'],
                ['label' => 'Templates', 'url' => '#templates-sections'],
                ['label' => 'Tools', 'url' => '#tools-sponsors'],
                ['label' => 'Resources', 'url' => '#build-resources'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'one-page-showcase-landing-page' => 'Theme One Page Showcase landing page',
            'one-page-showcase-list-page' => 'Theme One Page Showcase list page',
            'one-page-showcase-search-results' => 'Theme One Page Showcase search results',
            'one-page-showcase-contact-form' => 'Theme One Page Showcase contact form',
            default => 'Theme One Page Showcase homepage',
        };
    }
}
