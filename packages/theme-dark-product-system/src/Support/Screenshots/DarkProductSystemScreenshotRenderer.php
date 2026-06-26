<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DarkProductSystem\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class DarkProductSystemScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-dark-product-system::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (DarkProductSystemScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-dark-product-system::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#07080d',
                accentColor: '#8b9cff',
                neutralColor: '#11131c',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#090a10',
                foregroundColor: '#f4f6ff',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'dark-product-system',
        ])->render();

        return view('capell-theme-dark-product-system::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, DarkProductSystemScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'dark-product-system-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('system-hero'),
                $this->section('workflow-rails'),
                $this->section('agents-automation'),
                $this->section('planning-roadmap'),
                $this->section('changelog-integrations'),
                $this->section('security-proof'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'dark-product-system-landing-page' => [
                $this->navigation(),
                $this->section('system-hero', [
                    'heading' => 'A product system landing page that proves the surface',
                    'summary' => 'A focused workflow, automation, roadmap, or integration landing view keeps the system dark, detailed, and conversion-ready.',
                ]),
                $this->section('workflow-rails'),
                $this->section('changelog-integrations'),
                $this->section('cta'),
                $this->footer(),
            ],
            'dark-product-system-list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'A system archive built to be scanned',
                    'summary' => 'Workflow updates, automation notes, roadmap items, changelog entries, and customer stories stay structured without the theme owning records.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'dark-product-system-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search across the product system',
                    'summary' => 'Inbox items, issues, roadmaps, automation runs, integrations, and security notes resolve into one scannable, dark result surface.',
                ]),
                $this->footer(),
            ],
            'dark-product-system-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Start a conversation with the product system',
                    'summary' => 'A non-submitting newsletter, demo, or security-review path proves the conversion journey feels like part of the system experience.',
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
    private function section(string $sectionKey, array $data = []): DarkProductSystemScreenshotSection
    {
        return new DarkProductSystemScreenshotSection($sectionKey, $data);
    }

    private function navigation(): DarkProductSystemScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Northwind System',
            'items' => [
                ['label' => 'Workflows', 'url' => '#workflow-rails'],
                ['label' => 'Automation', 'url' => '#agents-automation'],
                ['label' => 'Roadmap', 'url' => '#planning-roadmap'],
                ['label' => 'Security', 'url' => '#security-proof'],
            ],
            'consultationUrl' => '#cta',
        ]);
    }

    private function hero(): DarkProductSystemScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A product system teams can operate in the dark',
            'eyebrow' => 'Dark Product System',
            'summary' => 'A product-led SaaS homepage for workflows, automation, roadmaps, changelog, integrations, customer proof, and security modules.',
            'actions' => [
                ['label' => 'Request a demo', 'url' => '#cta'],
                ['label' => 'Explore workflows', 'url' => '#workflow-rails'],
            ],
        ]);
    }

    private function footer(): DarkProductSystemScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Northwind System',
            'items' => [
                ['label' => 'Workflows', 'url' => '#workflow-rails'],
                ['label' => 'Automation', 'url' => '#agents-automation'],
                ['label' => 'Roadmap', 'url' => '#planning-roadmap'],
                ['label' => 'Security', 'url' => '#security-proof'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'dark-product-system-landing-page' => 'Theme Dark Product System landing page',
            'dark-product-system-list-page' => 'Theme Dark Product System list page',
            'dark-product-system-search-results' => 'Theme Dark Product System search results',
            'dark-product-system-contact-form' => 'Theme Dark Product System contact form',
            default => 'Theme Dark Product System homepage',
        };
    }
}
