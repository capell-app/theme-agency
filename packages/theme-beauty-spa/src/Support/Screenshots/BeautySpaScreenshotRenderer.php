<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\BeautySpa\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class BeautySpaScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-beauty-spa::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (BeautySpaScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-beauty-spa::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#9d8567',
                accentColor: '#b9a0b4',
                neutralColor: '#2b2420',
                headingFont: 'fraunces',
                bodyFont: 'inter',
                spacing: 'airy',
                cardStyle: 'flat',
                navigationStyle: 'minimal',
                layoutPresentation: 'editorial',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#f6f1ea',
                foregroundColor: '#2b2420',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'beauty-spa',
        ])->render();

        return view('capell-theme-beauty-spa::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, BeautySpaScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'beauty-spa-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('treatment-menu'),
                $this->section('therapist-profiles'),
                $this->section('package-grid'),
                $this->section('before-after-proof'),
                $this->section('booking-panel'),
                $this->section('features'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'beauty-spa-directory' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Browse the spa directory',
                    'summary' => 'A scannable listing of treatments and studios keeps the directory premium without the theme owning the records.',
                ]),
                $this->section('treatment-menu'),
                $this->section('cta'),
                $this->footer(),
            ],
            'beauty-spa-detail' => [
                $this->navigation(),
                $this->section('treatment-menu', [
                    'heading' => 'A single treatment, told beautifully',
                    'summary' => 'Detail pages pair the ritual story with therapist proof so guests can book with confidence.',
                ]),
                $this->section('therapist-profiles'),
                $this->section('before-after-proof'),
                $this->section('booking-panel'),
                $this->section('cta'),
                $this->footer(),
            ],
            'beauty-spa-contact' => [
                $this->navigation(),
                $this->section('booking-panel', [
                    'heading' => 'Reach the spa in one confident path',
                    'summary' => 'A non-submitting contact panel proves the enquiry journey feels like part of the spa experience.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'beauty-spa-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'No treatments match yet',
                    'summary' => 'An empty state stays calm and on-brand, guiding guests back to the full treatment menu.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            'beauty-spa-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page has drifted off',
                    'summary' => 'A reassuring 404 keeps the spa story intact and points guests back to booking.',
                ]),
                $this->footer(),
            ],
            'beauty-spa-cta' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Treat yourself to an hour that resets everything',
                    'summary' => 'A confident conversion moment turns a relaxed browse into a booked treatment.',
                ]),
                $this->section('proof'),
                $this->footer(),
            ],
            'beauty-spa-treatments' => [
                $this->navigation(),
                $this->section('treatment-menu', [
                    'heading' => 'A treatment menu built to be scanned and savoured',
                    'summary' => 'Editorial treatment groupings keep seasonal rituals premium and legible without owning treatment records.',
                ]),
                $this->section('therapist-profiles'),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'beauty-spa-packages' => [
                $this->navigation(),
                $this->section('package-grid', [
                    'heading' => 'Spa packages that bundle the perfect afternoon',
                    'summary' => 'Curated packages pair treatments, duration, and proof so guests can choose with ease.',
                ]),
                $this->section('before-after-proof'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'beauty-spa-booking' => [
                $this->navigation(),
                $this->section('booking-panel', [
                    'heading' => 'Book a treatment in one confident path',
                    'summary' => 'A non-submitting booking CTA proves the reservation journey feels like part of the spa experience.',
                ]),
                $this->section('treatment-menu'),
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
    private function section(string $sectionKey, array $data = []): BeautySpaScreenshotSection
    {
        return new BeautySpaScreenshotSection($sectionKey, $data);
    }

    private function navigation(): BeautySpaScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'The Old Town Spa',
            'items' => [
                ['label' => 'Treatments', 'url' => '#treatments'],
                ['label' => 'Packages', 'url' => '#packages'],
                ['label' => 'Therapists', 'url' => '#therapists'],
                ['label' => 'Booking', 'url' => '#booking'],
            ],
            'bookingUrl' => '#booking',
        ]);
    }

    private function hero(): BeautySpaScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'An hour that resets everything',
            'eyebrow' => 'Beauty & Spa',
            'summary' => 'A boutique wellness homepage for treatments, therapists, packages, proof, and booking-led journeys.',
            'actions' => [
                ['label' => 'Book a treatment', 'url' => '#booking'],
                ['label' => 'View treatments', 'url' => '#treatments'],
            ],
        ]);
    }

    private function footer(): BeautySpaScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'The Old Town Spa',
            'items' => [
                ['label' => 'Treatments', 'url' => '#treatments'],
                ['label' => 'Packages', 'url' => '#packages'],
                ['label' => 'Therapists', 'url' => '#therapists'],
                ['label' => 'Booking', 'url' => '#booking'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'beauty-spa-directory' => 'Theme Beauty & Spa directory',
            'beauty-spa-detail' => 'Theme Beauty & Spa detail',
            'beauty-spa-contact' => 'Theme Beauty & Spa contact',
            'beauty-spa-empty' => 'Theme Beauty & Spa empty state',
            'beauty-spa-not-found' => 'Theme Beauty & Spa 404 state',
            'beauty-spa-cta' => 'Theme Beauty & Spa conversion CTA',
            'beauty-spa-treatments' => 'Theme Beauty & Spa treatments',
            'beauty-spa-packages' => 'Theme Beauty & Spa packages',
            'beauty-spa-booking' => 'Theme Beauty & Spa booking',
            default => 'Theme Beauty & Spa homepage',
        };
    }
}
