<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Healthcare\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class HealthcareScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-healthcare::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (HealthcareScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-healthcare::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#0f766e',
                accentColor: '#f59e0b',
                neutralColor: '#14323a',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#f6fbfd',
                foregroundColor: '#14323a',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'healthcare',
        ])->render();

        return view('capell-theme-healthcare::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, HealthcareScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'frontend-page-rendered-with-healthcare-theme',
            'healthcare-homepage-desktop',
            'healthcare-homepage-mobile' => [
                $this->navigation(),
                $this->hero(),
                $this->section('service-finder'),
                $this->section('services'),
                $this->section('care-pathway'),
                $this->section('clinicians'),
                $this->section('booking'),
                $this->section('events'),
                $this->section('proof'),
                $this->section('blog-teaser'),
                $this->section('contact'),
                $this->section('cta'),
                $this->footer(),
            ],
            'healthcare-services-listing' => [
                $this->navigation(),
                $this->section('service-finder', [
                    'heading' => 'A service finder built to route patients to the right care',
                    'summary' => 'Structured care-route cards keep clinical options legible and appointment-led without the theme owning service records.',
                ]),
                $this->section('services'),
                $this->section('care-pathway'),
                $this->section('cta'),
                $this->footer(),
            ],
            'healthcare-clinician-detail' => [
                $this->navigation(),
                $this->section('clinician-profile', [
                    'heading' => 'A clinician profile that reads with clinical confidence',
                    'summary' => 'A single clinician view pairs expertise and proof so patients can book the right appointment with confidence.',
                ]),
                $this->section('insurance-trust'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'healthcare-contact-page' => [
                $this->navigation(),
                $this->section('contact', [
                    'heading' => 'Reach the clinic through one clear appointment path',
                    'summary' => 'A non-submitting contact panel proves the enquiry journey feels like part of the clinical experience.',
                ]),
                $this->section('locations'),
                $this->section('booking'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): HealthcareScreenshotSection
    {
        return new HealthcareScreenshotSection($sectionKey, $data);
    }

    private function navigation(): HealthcareScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Meridian Clinics',
            'items' => [
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Clinicians', 'url' => '#clinicians'],
                ['label' => 'Locations', 'url' => '#locations'],
                ['label' => 'Booking', 'url' => '#booking'],
            ],
            'consultationUrl' => '#booking',
        ]);
    }

    private function hero(): HealthcareScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Appointment-led care a clinic can stand behind',
            'eyebrow' => 'Healthcare',
            'summary' => 'A trust-building healthcare homepage for service discovery, clinicians, care pathways, locations, and booking-led journeys.',
            'actions' => [
                ['label' => 'Book an appointment', 'url' => '#booking'],
                ['label' => 'Find a service', 'url' => '#services'],
            ],
        ]);
    }

    private function footer(): HealthcareScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Meridian Clinics',
            'items' => [
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Clinicians', 'url' => '#clinicians'],
                ['label' => 'Locations', 'url' => '#locations'],
                ['label' => 'Booking', 'url' => '#booking'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'healthcare-homepage-desktop' => 'Theme Healthcare homepage desktop',
            'healthcare-homepage-mobile' => 'Theme Healthcare homepage mobile',
            'healthcare-services-listing' => 'Theme Healthcare services listing',
            'healthcare-clinician-detail' => 'Theme Healthcare clinician detail',
            'healthcare-contact-page' => 'Theme Healthcare contact',
            default => 'Theme Healthcare homepage',
        };
    }
}
