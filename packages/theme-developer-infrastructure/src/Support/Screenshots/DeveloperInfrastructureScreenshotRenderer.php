<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DeveloperInfrastructure\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class DeveloperInfrastructureScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-developer-infrastructure::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (DeveloperInfrastructureScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-developer-infrastructure::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#000000',
                accentColor: '#0066ff',
                neutralColor: '#111111',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#ffffff',
                foregroundColor: '#000000',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'developer-infrastructure',
        ])->render();

        return view('capell-theme-developer-infrastructure::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, DeveloperInfrastructureScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'developer-infrastructure-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('platform-pillars'),
                $this->section('command-blocks'),
                $this->section('deploy-timeline'),
                $this->section('docs-changelog'),
                $this->section('integrations'),
                $this->section('architecture-enterprise'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'developer-infrastructure-landing-page' => [
                $this->navigation(),
                $this->section('platform-pillars', [
                    'heading' => 'A platform landing page built for technical buyers',
                    'summary' => 'Pillars, command blocks, and architecture proof keep the product credible without the theme owning product records.',
                ]),
                $this->section('command-blocks'),
                $this->section('architecture-enterprise'),
                $this->section('cta'),
                $this->footer(),
            ],
            'developer-infrastructure-list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'A docs and changelog archive built to be scanned',
                    'summary' => 'Structured listing cards keep deploy notes, docs, and changelog entries legible without the theme owning content records.',
                ]),
                $this->section('docs-changelog'),
                $this->section('cta'),
                $this->footer(),
            ],
            'developer-infrastructure-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search across docs, deploys, and changelog entries',
                    'summary' => 'A results listing keeps technical discovery fast and legible while staying entirely public-safe.',
                ]),
                $this->section('integrations'),
                $this->footer(),
            ],
            'developer-infrastructure-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Reach the platform team through one clear path',
                    'summary' => 'A non-submitting conversion block proves demo, enterprise, and docs-feedback journeys feel native to the product.',
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
    private function section(string $sectionKey, array $data = []): DeveloperInfrastructureScreenshotSection
    {
        return new DeveloperInfrastructureScreenshotSection($sectionKey, $data);
    }

    private function navigation(): DeveloperInfrastructureScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Northbound',
            'items' => [
                ['label' => 'Platform', 'url' => '#platform-pillars'],
                ['label' => 'Docs', 'url' => '#docs-changelog'],
                ['label' => 'Integrations', 'url' => '#integrations'],
                ['label' => 'Enterprise', 'url' => '#architecture-enterprise'],
            ],
            'consultationUrl' => '#cta',
        ]);
    }

    private function hero(): DeveloperInfrastructureScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Infrastructure developers can deploy with confidence',
            'eyebrow' => 'Developer Infrastructure',
            'summary' => 'A technical platform homepage for pillars, command blocks, deploy timelines, docs, changelog entries, integrations, architecture, and enterprise journeys.',
            'actions' => [
                ['label' => 'Read the docs', 'url' => '#docs-changelog'],
                ['label' => 'View the platform', 'url' => '#platform-pillars'],
            ],
        ]);
    }

    private function footer(): DeveloperInfrastructureScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Northbound',
            'items' => [
                ['label' => 'Platform', 'url' => '#platform-pillars'],
                ['label' => 'Docs', 'url' => '#docs-changelog'],
                ['label' => 'Integrations', 'url' => '#integrations'],
                ['label' => 'Enterprise', 'url' => '#architecture-enterprise'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'developer-infrastructure-landing-page' => 'Theme Developer Infrastructure landing page',
            'developer-infrastructure-list-page' => 'Theme Developer Infrastructure list page',
            'developer-infrastructure-search-results' => 'Theme Developer Infrastructure search results',
            'developer-infrastructure-contact-form' => 'Theme Developer Infrastructure contact form',
            default => 'Theme Developer Infrastructure homepage',
        };
    }
}
