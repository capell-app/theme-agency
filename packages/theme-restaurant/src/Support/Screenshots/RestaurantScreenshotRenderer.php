<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Restaurant\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class RestaurantScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-restaurant::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (RestaurantScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-restaurant::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#0f3d2e',
                accentColor: '#b45309',
                neutralColor: '#171312',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'generous',
                cardStyle: 'editorial',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'full-bleed',
                radius: 'sm',
                surfaceColor: '#f7fbf7',
                foregroundColor: '#171312',
                headingScale: 'confident',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'restaurant',
        ])->render();

        return view('capell-theme-restaurant::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, RestaurantScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'restaurant-homepage-layout' => [
                $this->navigation(),
                $this->hero(),
                $this->section('menu-highlights'),
                $this->section('reservation-panel'),
                $this->section('private-dining'),
                $this->section('events-calendar'),
                $this->section('chef-story'),
                $this->section('features'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'restaurant-menu-layout' => [
                $this->navigation(),
                $this->section('menu-highlights', [
                    'heading' => 'A menu built to be scanned and savoured',
                    'summary' => 'Editorial dish groupings keep seasonal plates premium and legible without the theme owning menu records.',
                ]),
                $this->section('features'),
                $this->section('opening-hours'),
                $this->section('cta'),
                $this->footer(),
            ],
            'restaurant-reservation-layout' => [
                $this->navigation(),
                $this->section('reservation-panel', [
                    'heading' => 'Book a table in one confident path',
                    'summary' => 'A non-submitting reservation CTA proves the booking journey feels like part of the venue experience.',
                ]),
                $this->section('opening-hours'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'restaurant-private-dining-layout' => [
                $this->navigation(),
                $this->section('private-dining', [
                    'heading' => 'Host private dining without losing the venue story',
                    'summary' => 'Private events pair capacity, set menus, and proof so planners can enquire with confidence.',
                ]),
                $this->section('proof'),
                $this->section('location-guide'),
                $this->section('cta'),
                $this->footer(),
            ],
            'restaurant-events-layout' => [
                $this->navigation(),
                $this->section('events-calendar', [
                    'heading' => 'Turn the events calendar into a booking asset',
                    'summary' => 'Upcoming evenings, tastings, and residencies stay scannable and premium without owning event records.',
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
    private function section(string $sectionKey, array $data = []): RestaurantScreenshotSection
    {
        return new RestaurantScreenshotSection($sectionKey, $data);
    }

    private function navigation(): RestaurantScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'The Greenhouse Table',
            'items' => [
                ['label' => 'Menu', 'url' => '#menu'],
                ['label' => 'Reservations', 'url' => '#reservations'],
                ['label' => 'Private dining', 'url' => '#private-dining'],
                ['label' => 'Events', 'url' => '#events'],
            ],
            'reservationUrl' => '#reservations',
        ]);
    }

    private function hero(): RestaurantScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A neighbourhood table worth booking ahead',
            'eyebrow' => 'Restaurant',
            'summary' => 'A premium hospitality homepage for menus, reservations, private dining, events, hours, and location-led journeys.',
            'actions' => [
                ['label' => 'Reserve a table', 'url' => '#reservations'],
                ['label' => 'View the menu', 'url' => '#menu'],
            ],
        ]);
    }

    private function footer(): RestaurantScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'The Greenhouse Table',
            'items' => [
                ['label' => 'Menu', 'url' => '#menu'],
                ['label' => 'Reservations', 'url' => '#reservations'],
                ['label' => 'Private dining', 'url' => '#private-dining'],
                ['label' => 'Events', 'url' => '#events'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'restaurant-menu-layout' => 'Theme Restaurant menu highlights',
            'restaurant-reservation-layout' => 'Theme Restaurant reservation',
            'restaurant-private-dining-layout' => 'Theme Restaurant private dining',
            'restaurant-events-layout' => 'Theme Restaurant events',
            default => 'Theme Restaurant homepage',
        };
    }
}
