<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\OutdoorMission\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class OutdoorMissionScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-outdoor-mission::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (OutdoorMissionScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-outdoor-mission::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#2f5d3a',
                accentColor: '#b4512a',
                neutralColor: '#172018',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'immersive',
                mediaTreatment: 'framed',
                radius: 'sm',
                surfaceColor: '#f4f1e8',
                foregroundColor: '#172018',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'outdoor-mission',
        ])->render();

        return view('capell-theme-outdoor-mission::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, OutdoorMissionScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'outdoor-mission-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('seasonal-essentials'),
                $this->section('sport-categories'),
                $this->section('environmental-campaign'),
                $this->section('repair-reuse'),
                $this->section('field-stories'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'outdoor-mission-landing-page' => [
                $this->navigation(),
                $this->hero(),
                $this->section('environmental-campaign', [
                    'heading' => 'A seasonal campaign built to mobilise the field',
                    'summary' => 'A focused collection landing pairs action storytelling with gear so the campaign feels like part of the mission.',
                ]),
                $this->section('seasonal-essentials'),
                $this->section('cta'),
                $this->footer(),
            ],
            'outdoor-mission-list-page' => [
                $this->navigation(),
                $this->section('field-stories', [
                    'heading' => 'A field archive built to be scanned',
                    'summary' => 'Structured story and guide cards keep the archive rugged and legible without the theme owning content records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'outdoor-mission-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Gear, stories, and guides in one result set',
                    'summary' => 'A discovery view keeps mixed results structured and field-ready while the theme stays content-agnostic.',
                ]),
                $this->section('sport-categories'),
                $this->footer(),
            ],
            'outdoor-mission-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Join the mission through one confident path',
                    'summary' => 'A non-submitting conversion form proves the newsletter and repair-request journey feels part of the field experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): OutdoorMissionScreenshotSection
    {
        return new OutdoorMissionScreenshotSection($sectionKey, $data);
    }

    private function navigation(): OutdoorMissionScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Summit & Spruce',
            'items' => [
                ['label' => 'Gear', 'url' => '#seasonal-essentials'],
                ['label' => 'Sports', 'url' => '#sport-categories'],
                ['label' => 'Campaigns', 'url' => '#environmental-campaign'],
                ['label' => 'Field stories', 'url' => '#field-stories'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): OutdoorMissionScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Gear a mission can stand behind',
            'eyebrow' => 'Outdoor Mission',
            'summary' => 'A rugged mission-commerce homepage for gear discovery, sport categories, repair-led product language, field stories, and environmental action.',
            'actions' => [
                ['label' => 'Shop the field kit', 'url' => '#seasonal-essentials'],
                ['label' => 'Join a campaign', 'url' => '#environmental-campaign'],
            ],
        ]);
    }

    private function footer(): OutdoorMissionScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Summit & Spruce',
            'items' => [
                ['label' => 'Gear', 'url' => '#seasonal-essentials'],
                ['label' => 'Sports', 'url' => '#sport-categories'],
                ['label' => 'Campaigns', 'url' => '#environmental-campaign'],
                ['label' => 'Field stories', 'url' => '#field-stories'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'outdoor-mission-landing-page' => 'Theme Outdoor Mission landing page',
            'outdoor-mission-list-page' => 'Theme Outdoor Mission listing',
            'outdoor-mission-search-results' => 'Theme Outdoor Mission search results',
            'outdoor-mission-contact-form' => 'Theme Outdoor Mission contact form',
            default => 'Theme Outdoor Mission homepage',
        };
    }
}
