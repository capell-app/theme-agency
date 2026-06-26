<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LocalServices\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class LocalServicesScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-local-services::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (LocalServicesScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-local-services::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#0f766e',
                accentColor: '#f97316',
                neutralColor: '#13231f',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#f7fbf8',
                foregroundColor: '#13231f',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'local-services',
        ])->render();

        return view('capell-theme-local-services::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, LocalServicesScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'local-services-homepage-layout',
            'frontend-page-rendered-with-local-services-theme',
            'theme-admin-list-showing-local-services',
            'theme-preview-url-output' => [
                $this->navigation(),
                $this->hero(),
                $this->section('services'),
                $this->section('service-areas'),
                $this->section('locality-proof'),
                $this->section('quote-form'),
                $this->section('case-studies'),
                $this->section('reviews-testimonials'),
                $this->section('resources'),
                $this->section('contact'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'local-services-services-layout' => [
                $this->navigation(),
                $this->section('services', [
                    'heading' => 'Bookable service calls built to be compared',
                    'summary' => 'Service cards carry urgency markers, pricing cues, and quote actions without feeling like an agency services grid.',
                ]),
                $this->section('service-packages'),
                $this->section('quote-form'),
                $this->section('cta'),
                $this->footer(),
            ],
            'local-services-service-areas-layout' => [
                $this->navigation(),
                $this->section('service-areas', [
                    'heading' => 'Proof that we cover your area',
                    'summary' => 'Postcode and area cards make coverage obvious before the visitor is ever asked for an enquiry.',
                ]),
                $this->section('locality-proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'local-services-quote-form-layout' => [
                $this->navigation(),
                $this->section('quote-form', [
                    'heading' => 'Request a quote in one obvious path',
                    'summary' => 'A static-fallback quote form keeps conversion direct after service discovery, ready for optional form-builder state.',
                ]),
                $this->section('trust-badges'),
                $this->section('cta'),
                $this->footer(),
            ],
            'local-services-job-proof-layout' => [
                $this->navigation(),
                $this->section('case-studies', [
                    'heading' => 'Completed-job proof visitors can trust',
                    'summary' => 'Before/after-style case study cards show practical proof without overlapping a studio case-study workflow.',
                ]),
                $this->section('before-after-gallery'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'local-services-resources-layout' => [
                $this->navigation(),
                $this->section('resources', [
                    'heading' => 'Service advice that keeps the quote path visible',
                    'summary' => 'Resource cards answer common service questions using static content or the optional blog integration when available.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): LocalServicesScreenshotSection
    {
        return new LocalServicesScreenshotSection($sectionKey, $data);
    }

    private function navigation(): LocalServicesScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Meridian Local Services',
            'items' => [
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Service areas', 'url' => '#service-areas'],
                ['label' => 'Job proof', 'url' => '#case-studies'],
                ['label' => 'Quote', 'url' => '#quote-form'],
            ],
            'consultationUrl' => '#quote-form',
        ]);
    }

    private function hero(): LocalServicesScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Local service you can book the same day',
            'eyebrow' => 'Local Services',
            'summary' => 'A conversion-first homepage for local trades and service businesses, built around service-area coverage, click-to-call trust, and quote requests.',
            'actions' => [
                ['label' => 'Request a quote', 'url' => '#quote-form'],
                ['label' => 'Check your area', 'url' => '#service-areas'],
            ],
        ]);
    }

    private function footer(): LocalServicesScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Meridian Local Services',
            'items' => [
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Service areas', 'url' => '#service-areas'],
                ['label' => 'Job proof', 'url' => '#case-studies'],
                ['label' => 'Quote', 'url' => '#quote-form'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'local-services-services-layout' => 'Theme Local Services bookable services',
            'local-services-service-areas-layout' => 'Theme Local Services service areas',
            'local-services-quote-form-layout' => 'Theme Local Services quote request',
            'local-services-job-proof-layout' => 'Theme Local Services job proof',
            'local-services-resources-layout' => 'Theme Local Services resources',
            default => 'Theme Local Services homepage',
        };
    }
}
