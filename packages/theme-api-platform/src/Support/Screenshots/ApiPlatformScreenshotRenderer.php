<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ApiPlatform\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class ApiPlatformScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-api-platform::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (ApiPlatformScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-api-platform::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#38bdf8',
                accentColor: '#a78bfa',
                neutralColor: '#1e293b',
                headingFont: 'inter',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'minimal',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#0b1020',
                foregroundColor: '#e2e8f0',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'api-platform',
        ])->render();

        return view('capell-theme-api-platform::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, ApiPlatformScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'api-platform-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('quickstart'),
                $this->section('features'),
                $this->section('sdk-grid'),
                $this->section('api-reference-teaser'),
                $this->section('status-uptime'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'api-platform-directory' => [
                $this->navigation(),
                $this->section('sdk-grid', [
                    'heading' => 'An SDK directory built to be scanned',
                    'summary' => 'Structured SDK and endpoint cards keep the platform legible without the theme owning catalogue records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'api-platform-detail' => [
                $this->navigation(),
                $this->section('api-reference-teaser', [
                    'heading' => 'An endpoint view that reads with precision',
                    'summary' => 'A single reference detail pairs request shape and proof so developers can integrate with confidence.',
                ]),
                $this->section('features'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'api-platform-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Reach the platform team through one clear path',
                    'summary' => 'A non-submitting contact CTA proves the support journey feels like part of the developer experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'api-platform-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays precise and structured while the platform prepares its content.',
                ]),
                $this->footer(),
            ],
            'api-platform-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the platform precise and routes developers back into the product journey.',
                ]),
                $this->footer(),
            ],
            'api-platform-cta' => [
                $this->navigation(),
                $this->section('quickstart'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a first successful request',
                    'summary' => 'A conversion-focused CTA stack keeps the path to adopting the platform direct and developer-friendly.',
                ]),
                $this->footer(),
            ],
            'api-platform-quickstart' => [
                $this->navigation(),
                $this->section('quickstart', [
                    'heading' => 'Ship your first request in minutes',
                    'summary' => 'A structured quickstart keeps onboarding legible without the theme owning runnable code state.',
                ]),
                $this->section('sdk-grid'),
                $this->section('cta'),
                $this->footer(),
            ],
            'api-platform-api-reference' => [
                $this->navigation(),
                $this->section('api-reference-teaser', [
                    'heading' => 'API reference built to be scanned and trusted',
                    'summary' => 'Structured endpoint groupings keep the reference precise and legible without owning schema records.',
                ]),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'api-platform-status' => [
                $this->navigation(),
                $this->section('status-uptime', [
                    'heading' => 'Status and trust in one confident view',
                    'summary' => 'Uptime and trust signals keep the platform credible without the theme owning live incident state.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): ApiPlatformScreenshotSection
    {
        return new ApiPlatformScreenshotSection($sectionKey, $data);
    }

    private function navigation(): ApiPlatformScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Northbound API',
            'items' => [
                ['label' => 'Quickstart', 'url' => '#quickstart'],
                ['label' => 'SDKs', 'url' => '#sdk-grid'],
                ['label' => 'API reference', 'url' => '#api-reference'],
                ['label' => 'Status', 'url' => '#status'],
            ],
            'consultationUrl' => '#cta',
        ]);
    }

    private function hero(): ApiPlatformScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A platform developers can build on',
            'eyebrow' => 'API Platform',
            'summary' => 'A precise developer homepage for quickstarts, SDKs, API reference, status, and conversion-led journeys.',
            'actions' => [
                ['label' => 'Get started', 'url' => '#quickstart'],
                ['label' => 'View API reference', 'url' => '#api-reference'],
            ],
        ]);
    }

    private function footer(): ApiPlatformScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Northbound API',
            'items' => [
                ['label' => 'Quickstart', 'url' => '#quickstart'],
                ['label' => 'SDKs', 'url' => '#sdk-grid'],
                ['label' => 'API reference', 'url' => '#api-reference'],
                ['label' => 'Status', 'url' => '#status'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'api-platform-directory' => 'Theme API Platform directory',
            'api-platform-detail' => 'Theme API Platform detail',
            'api-platform-contact' => 'Theme API Platform contact',
            'api-platform-empty' => 'Theme API Platform empty state',
            'api-platform-not-found' => 'Theme API Platform 404 state',
            'api-platform-cta' => 'Theme API Platform conversion CTA',
            'api-platform-quickstart' => 'Theme API Platform quickstart',
            'api-platform-api-reference' => 'Theme API Platform API reference',
            'api-platform-status' => 'Theme API Platform status & trust',
            default => 'Theme API Platform homepage',
        };
    }
}
