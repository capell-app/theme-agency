<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AeoAnalytics\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class AeoAnalyticsScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-aeo-analytics::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (AeoAnalyticsScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-aeo-analytics::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#7c3aed',
                accentColor: '#84cc16',
                neutralColor: '#1e1b2e',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'elevated',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#ffffff',
                foregroundColor: '#1e1b2e',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'aeo-analytics',
        ])->render();

        return view('capell-theme-aeo-analytics::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, AeoAnalyticsScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'aeo-analytics-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('dashboard-preview'),
                $this->section('metric-cards'),
                $this->section('features'),
                $this->section('coverage-map'),
                $this->section('integrations-grid'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'aeo-analytics-directory' => [
                $this->navigation(),
                $this->section('report-gallery', [
                    'heading' => 'A directory of reports built to be scanned',
                    'summary' => 'Structured report cards keep the analytics surface legible without the theme owning report records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'aeo-analytics-detail' => [
                $this->navigation(),
                $this->section('dashboard-preview', [
                    'heading' => 'A report view that reads with clarity',
                    'summary' => 'A single report view pairs metrics and proof so buyers can assess visibility with confidence.',
                ]),
                $this->section('metric-cards'),
                $this->section('coverage-map'),
                $this->section('cta'),
                $this->footer(),
            ],
            'aeo-analytics-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Reach the team through one confident path',
                    'summary' => 'A non-submitting contact CTA proves the journey feels like part of the analytics experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'aeo-analytics-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing tracked here yet',
                    'summary' => 'An empty listing state stays premium and structured while the workspace gathers visibility data.',
                ]),
                $this->footer(),
            ],
            'aeo-analytics-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the analytics surface focused and routes visitors back into the product journey.',
                ]),
                $this->footer(),
            ],
            'aeo-analytics-cta' => [
                $this->navigation(),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a tracked workspace',
                    'summary' => 'A conversion-focused CTA stack keeps the path to monitoring AI visibility direct and premium.',
                ]),
                $this->footer(),
            ],
            'aeo-analytics-dashboard' => [
                $this->navigation(),
                $this->section('dashboard-preview', [
                    'heading' => 'A dashboard built to be scanned and trusted',
                    'summary' => 'Structured dashboard panels keep AI visibility legible without the theme owning analytics records.',
                ]),
                $this->section('metric-cards'),
                $this->section('coverage-map'),
                $this->section('cta'),
                $this->footer(),
            ],
            'aeo-analytics-reports' => [
                $this->navigation(),
                $this->section('report-gallery', [
                    'heading' => 'Reports that read with editorial clarity',
                    'summary' => 'Editorial report cards keep the gallery legible without the theme owning report records.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'aeo-analytics-integrations' => [
                $this->navigation(),
                $this->section('integrations-grid', [
                    'heading' => 'Integrations built to be scanned and trusted',
                    'summary' => 'A structured integrations grid keeps connected sources legible without the theme owning integration records.',
                ]),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): AeoAnalyticsScreenshotSection
    {
        return new AeoAnalyticsScreenshotSection($sectionKey, $data);
    }

    private function navigation(): AeoAnalyticsScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Citebase',
            'items' => [
                ['label' => 'Dashboard', 'url' => '#dashboard'],
                ['label' => 'Reports', 'url' => '#reports'],
                ['label' => 'Coverage', 'url' => '#coverage'],
                ['label' => 'Integrations', 'url' => '#integrations'],
            ],
            'consultationUrl' => '#cta',
        ]);
    }

    private function hero(): AeoAnalyticsScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'See where AI answers mention you',
            'eyebrow' => 'AEO Analytics',
            'summary' => 'An analytics homepage for AI search visibility, dashboards, coverage maps, reports, and integration-led journeys.',
            'actions' => [
                ['label' => 'Start tracking', 'url' => '#cta'],
                ['label' => 'View dashboard', 'url' => '#dashboard'],
            ],
        ]);
    }

    private function footer(): AeoAnalyticsScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Citebase',
            'items' => [
                ['label' => 'Dashboard', 'url' => '#dashboard'],
                ['label' => 'Reports', 'url' => '#reports'],
                ['label' => 'Coverage', 'url' => '#coverage'],
                ['label' => 'Integrations', 'url' => '#integrations'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'aeo-analytics-directory' => 'Theme AEO Analytics directory',
            'aeo-analytics-detail' => 'Theme AEO Analytics detail',
            'aeo-analytics-contact' => 'Theme AEO Analytics contact',
            'aeo-analytics-empty' => 'Theme AEO Analytics empty state',
            'aeo-analytics-not-found' => 'Theme AEO Analytics 404 state',
            'aeo-analytics-cta' => 'Theme AEO Analytics conversion CTA',
            'aeo-analytics-dashboard' => 'Theme AEO Analytics dashboard',
            'aeo-analytics-reports' => 'Theme AEO Analytics reports',
            'aeo-analytics-integrations' => 'Theme AEO Analytics integrations',
            default => 'Theme AEO Analytics homepage',
        };
    }
}
