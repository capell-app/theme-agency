<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PropertyDeveloper\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class PropertyDeveloperScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-property-developer::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (PropertyDeveloperScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-property-developer::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#1f2937',
                accentColor: '#a07c4f',
                neutralColor: '#111827',
                headingFont: 'fraunces',
                bodyFont: 'inter',
                spacing: 'airy',
                cardStyle: 'flat',
                navigationStyle: 'minimal',
                layoutPresentation: 'editorial',
                mediaTreatment: 'framed',
                radius: 'sm',
                surfaceColor: '#f7f5f1',
                foregroundColor: '#1f2937',
                headingScale: 'dramatic',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'property-developer',
        ])->render();

        return view('capell-theme-property-developer::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, PropertyDeveloperScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'property-developer-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('development-grid'),
                $this->section('floorplans'),
                $this->section('availability-table'),
                $this->section('location-guide'),
                $this->section('viewing-panel'),
                $this->section('features'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'property-developer-directory' => [
                $this->navigation(),
                $this->section('development-grid', [
                    'heading' => 'A directory of developments built to be scanned',
                    'summary' => 'Structured development cards keep the portfolio editorial and legible without the theme owning property records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'property-developer-detail' => [
                $this->navigation(),
                $this->section('development-grid', [
                    'heading' => 'A development profile that reads with confidence',
                    'summary' => 'A single development view pairs floorplans and availability so prospective buyers can register with confidence.',
                ]),
                $this->section('floorplans'),
                $this->section('availability-table'),
                $this->section('cta'),
                $this->footer(),
            ],
            'property-developer-contact' => [
                $this->navigation(),
                $this->section('viewing-panel', [
                    'heading' => 'Reach the developer through one confident path',
                    'summary' => 'A non-submitting viewing panel proves the enquiry journey feels like part of the development experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'property-developer-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays premium and editorial while the developer prepares its content.',
                ]),
                $this->footer(),
            ],
            'property-developer-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the developer editorial and routes visitors back into the development journey.',
                ]),
                $this->footer(),
            ],
            'property-developer-cta' => [
                $this->navigation(),
                $this->section('viewing-panel'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn interest into a booked viewing',
                    'summary' => 'A conversion-focused CTA stack keeps the path to registering interest direct and premium.',
                ]),
                $this->footer(),
            ],
            'property-developer-developments' => [
                $this->navigation(),
                $this->section('development-grid', [
                    'heading' => 'Developments built to be scanned and trusted',
                    'summary' => 'Structured development groupings keep schemes editorial and legible without owning property records.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'property-developer-floorplans' => [
                $this->navigation(),
                $this->section('floorplans', [
                    'heading' => 'Floorplans presented with editorial clarity',
                    'summary' => 'Structured floorplan layouts keep specifications legible without the theme owning plan records.',
                ]),
                $this->section('availability-table'),
                $this->section('cta'),
                $this->footer(),
            ],
            'property-developer-location' => [
                $this->navigation(),
                $this->section('location-guide', [
                    'heading' => 'A location guide that sells the place',
                    'summary' => 'An editorial location guide keeps connectivity and context legible without owning map records.',
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
    private function section(string $sectionKey, array $data = []): PropertyDeveloperScreenshotSection
    {
        return new PropertyDeveloperScreenshotSection($sectionKey, $data);
    }

    private function navigation(): PropertyDeveloperScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Marldon & Vale',
            'items' => [
                ['label' => 'Developments', 'url' => '#developments'],
                ['label' => 'Floorplans', 'url' => '#floorplans'],
                ['label' => 'Location', 'url' => '#location'],
                ['label' => 'Register interest', 'url' => '#viewing'],
            ],
            'consultationUrl' => '#viewing',
        ]);
    }

    private function hero(): PropertyDeveloperScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'New homes a place can be proud of',
            'eyebrow' => 'Property Developer',
            'summary' => 'An editorial property homepage for developments, floorplans, availability, location guides, and viewing-led journeys.',
            'actions' => [
                ['label' => 'Register your interest', 'url' => '#viewing'],
                ['label' => 'View developments', 'url' => '#developments'],
            ],
        ]);
    }

    private function footer(): PropertyDeveloperScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Marldon & Vale',
            'items' => [
                ['label' => 'Developments', 'url' => '#developments'],
                ['label' => 'Floorplans', 'url' => '#floorplans'],
                ['label' => 'Location', 'url' => '#location'],
                ['label' => 'Register interest', 'url' => '#viewing'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'property-developer-directory' => 'Theme Property Developer directory',
            'property-developer-detail' => 'Theme Property Developer detail',
            'property-developer-contact' => 'Theme Property Developer contact',
            'property-developer-empty' => 'Theme Property Developer empty state',
            'property-developer-not-found' => 'Theme Property Developer 404 state',
            'property-developer-cta' => 'Theme Property Developer conversion CTA',
            'property-developer-developments' => 'Theme Property Developer developments',
            'property-developer-floorplans' => 'Theme Property Developer floorplans',
            'property-developer-location' => 'Theme Property Developer location',
            default => 'Theme Property Developer homepage',
        };
    }
}
