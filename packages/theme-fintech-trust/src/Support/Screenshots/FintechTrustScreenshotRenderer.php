<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FintechTrust\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class FintechTrustScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-fintech-trust::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (FintechTrustScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-fintech-trust::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#1e3a8a',
                accentColor: '#14b8a6',
                neutralColor: '#0f172a',
                headingFont: 'inter',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#f8fafc',
                foregroundColor: '#0f172a',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'fintech-trust',
        ])->render();

        return view('capell-theme-fintech-trust::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, FintechTrustScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'fintech-trust-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('compliance-badges'),
                $this->section('features'),
                $this->section('security-architecture'),
                $this->section('verification-flow'),
                $this->section('metric-cards'),
                $this->section('coverage-map'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'fintech-trust-directory' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'A directory of verification coverage built to be scanned',
                    'summary' => 'Structured listing cards keep registries and jurisdictions legible without the theme owning record data.',
                ]),
                $this->section('coverage-map'),
                $this->section('cta'),
                $this->footer(),
            ],
            'fintech-trust-detail' => [
                $this->navigation(),
                $this->section('verification-flow', [
                    'heading' => 'A verification record that reads with confidence',
                    'summary' => 'A single business view pairs ownership, registration, and risk so compliance teams can act with certainty.',
                ]),
                $this->section('security-architecture'),
                $this->section('metric-cards'),
                $this->section('cta'),
                $this->footer(),
            ],
            'fintech-trust-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Reach the trust team through one confident path',
                    'summary' => 'A non-submitting contact CTA proves the journey feels like part of the verification experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'fintech-trust-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays trustworthy and structured while the platform prepares its content.',
                ]),
                $this->footer(),
            ],
            'fintech-trust-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the platform credible and routes visitors back into the verification journey.',
                ]),
                $this->footer(),
            ],
            'fintech-trust-cta' => [
                $this->navigation(),
                $this->section('proof'),
                $this->section('metric-cards'),
                $this->section('cta', [
                    'heading' => 'Turn intent into an onboarded compliance team',
                    'summary' => 'A conversion-focused CTA stack keeps the path to adopting the platform direct and credible.',
                ]),
                $this->footer(),
            ],
            'fintech-trust-verification-flow' => [
                $this->navigation(),
                $this->section('verification-flow', [
                    'heading' => 'A verification flow built to be audited and trusted',
                    'summary' => 'Structured verification steps keep ownership, registration, and risk legible without owning record data.',
                ]),
                $this->section('metric-cards'),
                $this->section('cta'),
                $this->footer(),
            ],
            'fintech-trust-security' => [
                $this->navigation(),
                $this->section('security-architecture', [
                    'heading' => 'Security architecture your auditors can stand behind',
                    'summary' => 'Layered controls and an audit trail keep the platform credible without the theme owning sensitive data.',
                ]),
                $this->section('compliance-badges'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'fintech-trust-compliance' => [
                $this->navigation(),
                $this->section('compliance-badges', [
                    'heading' => 'A compliance hub built to be scanned and trusted',
                    'summary' => 'Structured compliance badges keep certifications authoritative and legible without owning attestation records.',
                ]),
                $this->section('coverage-map'),
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
    private function section(string $sectionKey, array $data = []): FintechTrustScreenshotSection
    {
        return new FintechTrustScreenshotSection($sectionKey, $data);
    }

    private function navigation(): FintechTrustScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Veridian Trust',
            'items' => [
                ['label' => 'Verification', 'url' => '#verification-flow'],
                ['label' => 'Security', 'url' => '#security-architecture'],
                ['label' => 'Compliance', 'url' => '#compliance-badges'],
                ['label' => 'Coverage', 'url' => '#coverage-map'],
            ],
            'consultationUrl' => '#cta',
        ]);
    }

    private function hero(): FintechTrustScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Verify a business in seconds, with a trail you can audit',
            'eyebrow' => 'Fintech Trust',
            'summary' => 'A trust-led fintech homepage for identity verification, KYB, security architecture, compliance, and conversion-led journeys.',
            'actions' => [
                ['label' => 'Start a verification', 'url' => '#verification-flow'],
                ['label' => 'Explore security', 'url' => '#security-architecture'],
            ],
        ]);
    }

    private function footer(): FintechTrustScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Veridian Trust',
            'items' => [
                ['label' => 'Verification', 'url' => '#verification-flow'],
                ['label' => 'Security', 'url' => '#security-architecture'],
                ['label' => 'Compliance', 'url' => '#compliance-badges'],
                ['label' => 'Coverage', 'url' => '#coverage-map'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'fintech-trust-directory' => 'Theme Fintech Trust directory',
            'fintech-trust-detail' => 'Theme Fintech Trust detail',
            'fintech-trust-contact' => 'Theme Fintech Trust contact',
            'fintech-trust-empty' => 'Theme Fintech Trust empty state',
            'fintech-trust-not-found' => 'Theme Fintech Trust 404 state',
            'fintech-trust-cta' => 'Theme Fintech Trust conversion CTA',
            'fintech-trust-verification-flow' => 'Theme Fintech Trust verification flow',
            'fintech-trust-security' => 'Theme Fintech Trust security architecture',
            'fintech-trust-compliance' => 'Theme Fintech Trust compliance hub',
            default => 'Theme Fintech Trust homepage',
        };
    }
}
