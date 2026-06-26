<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Manufacturing\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class ManufacturingScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-manufacturing::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (ManufacturingScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-manufacturing::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#1d4ed8',
                accentColor: '#f59e0b',
                neutralColor: '#0f172a',
                headingFont: 'inter',
                bodyFont: 'inter',
                spacing: 'compact',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'flat',
                radius: 'sm',
                surfaceColor: '#f8fafc',
                foregroundColor: '#0f172a',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'manufacturing',
        ])->render();

        return view('capell-theme-manufacturing::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, ManufacturingScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'manufacturing-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('capabilities-grid'),
                $this->section('certifications'),
                $this->section('facility-stats'),
                $this->section('case-studies'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'manufacturing-directory' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'A capability directory built to be scanned',
                    'summary' => 'Structured capability cards keep the production catalogue legible without the theme owning part records.',
                ]),
                $this->section('case-studies'),
                $this->section('cta'),
                $this->footer(),
            ],
            'manufacturing-detail' => [
                $this->navigation(),
                $this->section('case-studies', [
                    'heading' => 'A production case study that reads with authority',
                    'summary' => 'A single case study view pairs certifications and facility metrics so buyers can engage with confidence.',
                ]),
                $this->section('certifications'),
                $this->section('facility-stats'),
                $this->section('cta'),
                $this->footer(),
            ],
            'manufacturing-contact' => [
                $this->navigation(),
                $this->section('rfq-form', [
                    'heading' => 'Reach the plant through one confident path',
                    'summary' => 'A non-submitting RFQ form proves the contact journey feels like part of the production experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'manufacturing-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays precise and structured while the plant prepares its content.',
                ]),
                $this->footer(),
            ],
            'manufacturing-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the manufacturer credible and routes visitors back into the production journey.',
                ]),
                $this->footer(),
            ],
            'manufacturing-cta' => [
                $this->navigation(),
                $this->section('rfq-form'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a submitted RFQ',
                    'summary' => 'A conversion-focused CTA stack keeps the path to requesting a quote direct and precise.',
                ]),
                $this->footer(),
            ],
            'manufacturing-capabilities' => [
                $this->navigation(),
                $this->section('capabilities-grid', [
                    'heading' => 'Capabilities built to be scanned and trusted',
                    'summary' => 'Structured capability groupings keep production processes legible without owning part records.',
                ]),
                $this->section('certifications'),
                $this->section('cta'),
                $this->footer(),
            ],
            'manufacturing-case-studies' => [
                $this->navigation(),
                $this->section('case-studies', [
                    'heading' => 'Production case studies buyers can trust',
                    'summary' => 'Editorial case study cards keep delivered work credible without the theme owning project records.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'manufacturing-rfq' => [
                $this->navigation(),
                $this->section('rfq-form', [
                    'heading' => 'Request a quote in one confident path',
                    'summary' => 'A non-submitting RFQ form proves the quote journey feels like part of the production experience.',
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
    private function section(string $sectionKey, array $data = []): ManufacturingScreenshotSection
    {
        return new ManufacturingScreenshotSection($sectionKey, $data);
    }

    private function navigation(): ManufacturingScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Axion Precision Works',
            'items' => [
                ['label' => 'Capabilities', 'url' => '#capabilities'],
                ['label' => 'Certifications', 'url' => '#certifications'],
                ['label' => 'Case studies', 'url' => '#case-studies'],
                ['label' => 'Request a quote', 'url' => '#rfq'],
            ],
            'consultationUrl' => '#rfq',
        ]);
    }

    private function hero(): ManufacturingScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Precision manufacturing buyers can stand behind',
            'eyebrow' => 'Manufacturing',
            'summary' => 'An industrial homepage for capabilities, certifications, facility metrics, case studies, and RFQ-led journeys.',
            'actions' => [
                ['label' => 'Request a quote', 'url' => '#rfq'],
                ['label' => 'View capabilities', 'url' => '#capabilities'],
            ],
        ]);
    }

    private function footer(): ManufacturingScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Axion Precision Works',
            'items' => [
                ['label' => 'Capabilities', 'url' => '#capabilities'],
                ['label' => 'Certifications', 'url' => '#certifications'],
                ['label' => 'Case studies', 'url' => '#case-studies'],
                ['label' => 'Request a quote', 'url' => '#rfq'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'manufacturing-directory' => 'Theme Manufacturing directory',
            'manufacturing-detail' => 'Theme Manufacturing detail',
            'manufacturing-contact' => 'Theme Manufacturing contact',
            'manufacturing-empty' => 'Theme Manufacturing empty state',
            'manufacturing-not-found' => 'Theme Manufacturing 404 state',
            'manufacturing-cta' => 'Theme Manufacturing conversion CTA',
            'manufacturing-capabilities' => 'Theme Manufacturing capabilities',
            'manufacturing-case-studies' => 'Theme Manufacturing case studies',
            'manufacturing-rfq' => 'Theme Manufacturing RFQ',
            default => 'Theme Manufacturing homepage',
        };
    }
}
