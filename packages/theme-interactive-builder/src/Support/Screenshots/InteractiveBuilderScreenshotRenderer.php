<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InteractiveBuilder\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class InteractiveBuilderScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-interactive-builder::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (InteractiveBuilderScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-interactive-builder::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#0b0b12',
                accentColor: '#46f0ff',
                neutralColor: '#11111b',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#0f1018',
                foregroundColor: '#f7f8ff',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'interactive-builder',
        ])->render();

        return view('capell-theme-interactive-builder::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, InteractiveBuilderScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'interactive-builder-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('canvas-workspace'),
                $this->section('builder-capabilities'),
                $this->section('templates'),
                $this->section('collaboration-comments'),
                $this->section('community-showcase'),
                $this->section('publishing-controls'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'interactive-builder-landing-page' => [
                $this->navigation(),
                $this->hero(),
                $this->section('canvas-workspace', [
                    'heading' => 'A canvas workspace built to be shown, not explained',
                    'summary' => 'A high-contrast builder surface proves the feature landing page feels like part of the product without owning live tool state.',
                ]),
                $this->section('builder-capabilities'),
                $this->section('publishing-controls'),
                $this->section('cta'),
                $this->footer(),
            ],
            'interactive-builder-list-page' => [
                $this->navigation(),
                $this->section('templates', [
                    'heading' => 'A template library built to be scanned',
                    'summary' => 'Structured template cards keep the gallery legible and premium without the theme owning catalogue records.',
                ]),
                $this->section('community-showcase'),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'interactive-builder-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search across templates, assets, and showcase builds',
                    'summary' => 'A structured results listing keeps discovery fast and legible while the theme stays free of search state.',
                ]),
                $this->section('templates'),
                $this->footer(),
            ],
            'interactive-builder-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Start a conversation through one confident path',
                    'summary' => 'A non-submitting conversion form proves the contact journey feels like part of the product experience.',
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
    private function section(string $sectionKey, array $data = []): InteractiveBuilderScreenshotSection
    {
        return new InteractiveBuilderScreenshotSection($sectionKey, $data);
    }

    private function navigation(): InteractiveBuilderScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Canvasflow',
            'items' => [
                ['label' => 'Canvas', 'url' => '#canvas-workspace'],
                ['label' => 'Templates', 'url' => '#templates'],
                ['label' => 'Community', 'url' => '#community-showcase'],
                ['label' => 'Publishing', 'url' => '#publishing-controls'],
            ],
            'consultationUrl' => '#cta',
        ]);
    }

    private function hero(): InteractiveBuilderScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Build, collaborate, and publish on one interactive canvas',
            'eyebrow' => 'Interactive Builder',
            'summary' => 'A dark, high-contrast builder homepage for canvas workspaces, templates, collaboration, publishing controls, and community showcases.',
            'actions' => [
                ['label' => 'Open the canvas', 'url' => '#canvas-workspace'],
                ['label' => 'Browse templates', 'url' => '#templates'],
            ],
        ]);
    }

    private function footer(): InteractiveBuilderScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Canvasflow',
            'items' => [
                ['label' => 'Canvas', 'url' => '#canvas-workspace'],
                ['label' => 'Templates', 'url' => '#templates'],
                ['label' => 'Community', 'url' => '#community-showcase'],
                ['label' => 'Publishing', 'url' => '#publishing-controls'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'interactive-builder-landing-page' => 'Theme Interactive Builder landing page',
            'interactive-builder-list-page' => 'Theme Interactive Builder list page',
            'interactive-builder-search-results' => 'Theme Interactive Builder search results',
            'interactive-builder-contact-form' => 'Theme Interactive Builder contact form',
            default => 'Theme Interactive Builder homepage',
        };
    }
}
