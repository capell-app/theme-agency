<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\TravelTourism\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class TravelTourismScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-travel-tourism::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (TravelTourismScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-travel-tourism::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#0d9488',
                accentColor: '#f59e0b',
                neutralColor: '#0f1f1c',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'elevated',
                navigationStyle: 'prominent',
                layoutPresentation: 'immersive',
                mediaTreatment: 'framed',
                radius: 'lg',
                surfaceColor: '#f8faf9',
                foregroundColor: '#0f1f1c',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'travel-tourism',
        ])->render();

        return view('capell-theme-travel-tourism::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, TravelTourismScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'travel-tourism-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('destination-grid'),
                $this->section('itinerary-builder'),
                $this->section('guide-profiles'),
                $this->section('trip-inclusions'),
                $this->section('features'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'travel-tourism-directory' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Browse every journey in one scannable directory',
                    'summary' => 'Trips, regions, and seasons stay legible and premium without the theme owning travel records.',
                ]),
                $this->section('destination-grid'),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'travel-tourism-detail' => [
                $this->navigation(),
                $this->section('itinerary-builder', [
                    'heading' => 'A single journey told day by day',
                    'summary' => 'Pacing, inclusions, and local guides come together so a traveller can picture the whole trip.',
                ]),
                $this->section('trip-inclusions'),
                $this->section('guide-profiles'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'travel-tourism-contact' => [
                $this->navigation(),
                $this->section('enquiry-panel', [
                    'heading' => 'Start a conversation about your trip',
                    'summary' => 'A non-submitting enquiry CTA proves the contact journey feels like part of the brand experience.',
                ]),
                $this->section('proof'),
                $this->footer(),
            ],
            'travel-tourism-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'No journeys match yet',
                    'summary' => 'An empty directory state stays on-brand and points travellers toward an enquiry instead of a dead end.',
                ]),
                $this->section('cta'),
                $this->footer(),
            ],
            'travel-tourism-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'This page wandered off the map',
                    'summary' => 'A branded 404 keeps the traveller moving back toward destinations and itineraries.',
                ]),
                $this->footer(),
            ],
            'travel-tourism-cta' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Ready to start planning your journey?',
                    'summary' => 'A confident conversion moment pairs proof and a single enquiry path.',
                ]),
                $this->section('proof'),
                $this->footer(),
            ],
            'travel-tourism-destinations' => [
                $this->navigation(),
                $this->section('destination-grid', [
                    'heading' => 'Explore destinations worth the journey',
                    'summary' => 'A premium destination grid keeps regions scannable without owning destination records.',
                ]),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'travel-tourism-itineraries' => [
                $this->navigation(),
                $this->section('itinerary-builder', [
                    'heading' => 'Build an itinerary that breathes',
                    'summary' => 'Day-by-day pacing and inclusions stay editorial and premium across every trip.',
                ]),
                $this->section('trip-inclusions'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'travel-tourism-enquiry' => [
                $this->navigation(),
                $this->section('enquiry-panel', [
                    'heading' => 'Tell us where you\'re dreaming of',
                    'summary' => 'A single enquiry path proves the booking journey feels like part of the venue experience.',
                ]),
                $this->section('trip-inclusions'),
                $this->section('proof'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): TravelTourismScreenshotSection
    {
        return new TravelTourismScreenshotSection($sectionKey, $data);
    }

    private function navigation(): TravelTourismScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Wayfarer Journeys',
            'items' => [
                ['label' => 'Destinations', 'url' => '#destinations'],
                ['label' => 'Itineraries', 'url' => '#itineraries'],
                ['label' => 'Guides', 'url' => '#guides'],
                ['label' => 'Enquiry', 'url' => '#enquiry'],
            ],
            'enquiryUrl' => '#enquiry',
        ]);
    }

    private function hero(): TravelTourismScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Small-group and tailor-made journeys worth the time',
            'eyebrow' => 'Travel & Tourism',
            'summary' => 'A premium travel homepage for destinations, itineraries, local guides, inclusions, and enquiry-led journeys.',
            'actions' => [
                ['label' => 'Start an enquiry', 'url' => '#enquiry'],
                ['label' => 'Explore destinations', 'url' => '#destinations'],
            ],
        ]);
    }

    private function footer(): TravelTourismScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Wayfarer Journeys',
            'items' => [
                ['label' => 'Destinations', 'url' => '#destinations'],
                ['label' => 'Itineraries', 'url' => '#itineraries'],
                ['label' => 'Guides', 'url' => '#guides'],
                ['label' => 'Enquiry', 'url' => '#enquiry'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'travel-tourism-directory' => 'Theme Travel & Tourism directory',
            'travel-tourism-detail' => 'Theme Travel & Tourism detail',
            'travel-tourism-contact' => 'Theme Travel & Tourism contact',
            'travel-tourism-empty' => 'Theme Travel & Tourism empty state',
            'travel-tourism-not-found' => 'Theme Travel & Tourism 404 state',
            'travel-tourism-cta' => 'Theme Travel & Tourism conversion CTA',
            'travel-tourism-destinations' => 'Theme Travel & Tourism destinations',
            'travel-tourism-itineraries' => 'Theme Travel & Tourism itineraries',
            'travel-tourism-enquiry' => 'Theme Travel & Tourism enquiry',
            default => 'Theme Travel & Tourism homepage',
        };
    }
}
