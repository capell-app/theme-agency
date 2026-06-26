<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DogWalkers\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class DogWalkersScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-dog-walkers::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (DogWalkersScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-dog-walkers::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#0f5f4a',
                accentColor: '#e86f4b',
                neutralColor: '#17342c',
                headingFont: 'sora',
                bodyFont: 'ibm-plex-sans',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'editorial',
                radius: 'md',
                surfaceColor: '#f4fbf6',
                foregroundColor: '#13231f',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'dog-walkers',
        ])->render();

        return view('capell-theme-dog-walkers::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, DogWalkersScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'theme-admin-list-showing-dog-walkers',
            'frontend-page-rendered-with-dog-walkers-theme',
            'theme-preview-url-output',
            'dog-walkers-homepage-layout' => [
                $this->navigation(),
                $this->hero(),
                $this->section('walk-options'),
                $this->section('service-areas'),
                $this->section('safety-checklist'),
                $this->section('route-board'),
                $this->section('meet-the-walkers'),
                $this->section('reviews-testimonials'),
                $this->section('proof'),
                $this->section('enquiry-form'),
                $this->section('cta'),
                $this->footer(),
            ],
            'dog-walkers-services-layout' => [
                $this->navigation(),
                $this->section('walk-options', [
                    'heading' => 'Walk options local owners can compare at a glance',
                    'summary' => 'Structured walk cards keep service choice clear and conversion-ready without the page feeling like an agency grid.',
                ]),
                $this->section('opening-hours'),
                $this->section('enquiry-form'),
                $this->footer(),
            ],
            'dog-walkers-service-areas-layout' => [
                $this->navigation(),
                $this->section('service-areas', [
                    'heading' => 'The neighbourhoods we cover, proven up front',
                    'summary' => 'Postcode and route cards prove coverage before asking for an enquiry, so visitors trust the team serves their area.',
                ]),
                $this->section('route-board'),
                $this->section('cta'),
                $this->footer(),
            ],
            'dog-walkers-enquiry-form-layout' => [
                $this->navigation(),
                $this->section('enquiry-form', [
                    'heading' => 'Request a walk through one calm enquiry path',
                    'summary' => 'A static-fallback enquiry section keeps quote conversion obvious once service discovery is done.',
                ]),
                $this->section('opening-hours'),
                $this->footer(),
            ],
            'dog-walkers-route-board-layout' => [
                $this->navigation(),
                $this->section('route-board', [
                    'heading' => 'Route proof and visit checks owners can rely on',
                    'summary' => 'A route board pairs with safety checklists and local trust markers to show practical proof of every walk.',
                ]),
                $this->section('safety-checklist'),
                $this->section('proof'),
                $this->footer(),
            ],
            'dog-walkers-resources-layout' => [
                $this->navigation(),
                $this->section('resources', [
                    'heading' => 'Service advice that keeps the quote path visible',
                    'summary' => 'Resource cards answer common pet-care questions while the enquiry journey stays one tap away.',
                ]),
                $this->section('faq'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): DogWalkersScreenshotSection
    {
        return new DogWalkersScreenshotSection($sectionKey, $data);
    }

    private function navigation(): DogWalkersScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Maple Lane Walks',
            'items' => [
                ['label' => 'Walk options', 'url' => '#walk-options'],
                ['label' => 'Service areas', 'url' => '#service-areas'],
                ['label' => 'Route board', 'url' => '#route-board'],
                ['label' => 'Enquiry', 'url' => '#enquiry'],
            ],
            'consultationUrl' => '#enquiry',
        ]);
    }

    private function hero(): DogWalkersScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Reliable, friendly dog walking in your neighbourhood',
            'eyebrow' => 'Dog Walkers',
            'summary' => 'A trust-led pet-care homepage for walk options, service areas, safety proof, and enquiry-led journeys.',
            'actions' => [
                ['label' => 'Request a walk', 'url' => '#enquiry'],
                ['label' => 'See walk options', 'url' => '#walk-options'],
            ],
        ]);
    }

    private function footer(): DogWalkersScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Maple Lane Walks',
            'items' => [
                ['label' => 'Walk options', 'url' => '#walk-options'],
                ['label' => 'Service areas', 'url' => '#service-areas'],
                ['label' => 'Route board', 'url' => '#route-board'],
                ['label' => 'Enquiry', 'url' => '#enquiry'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'theme-admin-list-showing-dog-walkers' => 'Theme Dog Walkers admin list',
            'frontend-page-rendered-with-dog-walkers-theme' => 'Theme Dog Walkers frontend page',
            'dog-walkers-services-layout' => 'Theme Dog Walkers walk options',
            'dog-walkers-service-areas-layout' => 'Theme Dog Walkers service areas',
            'dog-walkers-enquiry-form-layout' => 'Theme Dog Walkers enquiry form',
            'dog-walkers-route-board-layout' => 'Theme Dog Walkers route board',
            'dog-walkers-resources-layout' => 'Theme Dog Walkers resources',
            'theme-preview-url-output' => 'Theme Dog Walkers admin preview',
            default => 'Theme Dog Walkers homepage',
        };
    }
}
