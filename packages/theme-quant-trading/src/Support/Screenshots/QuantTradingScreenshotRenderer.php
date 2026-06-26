<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\QuantTrading\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class QuantTradingScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-quant-trading::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (QuantTradingScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-quant-trading::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#2dd4bf',
                accentColor: '#f43f5e',
                neutralColor: '#161b22',
                headingFont: 'space-grotesk',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'minimal',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'sm',
                surfaceColor: '#0b0e14',
                foregroundColor: '#e6edf3',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'quant-trading',
        ])->render();

        return view('capell-theme-quant-trading::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, QuantTradingScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'quant-trading-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('performance-chart'),
                $this->section('metric-cards'),
                $this->section('strategy-cards'),
                $this->section('track-record-table'),
                $this->section('features'),
                $this->section('risk-disclosure'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quant-trading-directory' => [
                $this->navigation(),
                $this->section('strategy-cards', [
                    'heading' => 'A directory of systematic strategies built to be scanned',
                    'summary' => 'Structured strategy cards keep the book legible and transparent without the theme owning live position records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quant-trading-detail' => [
                $this->navigation(),
                $this->section('strategy-cards', [
                    'heading' => 'A strategy profile that reads with rigour',
                    'summary' => 'A single strategy view pairs performance and proof so allocators can evaluate the edge with confidence.',
                ]),
                $this->section('performance-chart'),
                $this->section('track-record-table'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quant-trading-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Reach the desk through one direct path',
                    'summary' => 'A non-submitting contact CTA proves the allocator journey feels like part of the systematic firm experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'quant-trading-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays precise and structured while the firm prepares its research.',
                ]),
                $this->footer(),
            ],
            'quant-trading-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the firm precise and routes visitors back into the systematic-strategy journey.',
                ]),
                $this->footer(),
            ],
            'quant-trading-cta' => [
                $this->navigation(),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn interest into an allocation conversation',
                    'summary' => 'A conversion-focused CTA stack keeps the path to engaging the desk direct and transparent.',
                ]),
                $this->footer(),
            ],
            'quant-trading-performance' => [
                $this->navigation(),
                $this->section('performance-chart', [
                    'heading' => 'Performance presented with transparency',
                    'summary' => 'Illustrative equity curves and metrics keep returns legible without the theme owning live accounting records.',
                ]),
                $this->section('metric-cards'),
                $this->section('track-record-table'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quant-trading-strategies' => [
                $this->navigation(),
                $this->section('strategy-cards', [
                    'heading' => 'Strategies built to be scanned and trusted',
                    'summary' => 'Structured strategy groupings keep the systematic book legible and transparent without owning position records.',
                ]),
                $this->section('features'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'quant-trading-risk' => [
                $this->navigation(),
                $this->section('risk-disclosure', [
                    'heading' => 'Risk stated plainly and up front',
                    'summary' => 'A clear risk disclosure keeps the firm transparent about hard limits and the illustrative nature of figures shown.',
                ]),
                $this->section('track-record-table'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): QuantTradingScreenshotSection
    {
        return new QuantTradingScreenshotSection($sectionKey, $data);
    }

    private function navigation(): QuantTradingScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Meridian',
            'items' => [
                ['label' => 'Strategies', 'url' => '#strategies'],
                ['label' => 'Performance', 'url' => '#performance'],
                ['label' => 'Track record', 'url' => '#track-record'],
                ['label' => 'Risk', 'url' => '#risk'],
            ],
            'consultationUrl' => '#contact',
        ]);
    }

    private function hero(): QuantTradingScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Systematic strategies a book can stand behind',
            'eyebrow' => 'Quant Trading',
            'summary' => 'A data-driven trading homepage for strategies, performance, track record, risk limits, and allocator-led journeys.',
            'actions' => [
                ['label' => 'View performance', 'url' => '#performance'],
                ['label' => 'Explore strategies', 'url' => '#strategies'],
            ],
        ]);
    }

    private function footer(): QuantTradingScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Meridian',
            'items' => [
                ['label' => 'Strategies', 'url' => '#strategies'],
                ['label' => 'Performance', 'url' => '#performance'],
                ['label' => 'Track record', 'url' => '#track-record'],
                ['label' => 'Risk', 'url' => '#risk'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'quant-trading-directory' => 'Theme Quant Trading directory',
            'quant-trading-detail' => 'Theme Quant Trading detail',
            'quant-trading-contact' => 'Theme Quant Trading contact',
            'quant-trading-empty' => 'Theme Quant Trading empty state',
            'quant-trading-not-found' => 'Theme Quant Trading 404 state',
            'quant-trading-cta' => 'Theme Quant Trading conversion CTA',
            'quant-trading-performance' => 'Theme Quant Trading performance',
            'quant-trading-strategies' => 'Theme Quant Trading strategies',
            'quant-trading-risk' => 'Theme Quant Trading risk disclosure',
            default => 'Theme Quant Trading homepage',
        };
    }
}
