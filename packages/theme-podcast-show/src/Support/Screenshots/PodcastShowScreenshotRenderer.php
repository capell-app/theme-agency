<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PodcastShow\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class PodcastShowScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-podcast-show::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (PodcastShowScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-podcast-show::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#9333ea',
                accentColor: '#fb923c',
                neutralColor: '#2a1e2e',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'elevated',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'framed',
                radius: 'lg',
                surfaceColor: '#fdf7f3',
                foregroundColor: '#2a1e2e',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'podcast-show',
        ])->render();

        return view('capell-theme-podcast-show::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, PodcastShowScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'podcast-show-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('latest-episode'),
                $this->section('episode-list'),
                $this->section('subscribe-platforms'),
                $this->section('features'),
                $this->section('hosts'),
                $this->section('guests'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'podcast-show-directory' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Browse every show in one editorial directory',
                    'summary' => 'A scannable listing of shows and series keeps discovery premium without the theme owning content records.',
                ]),
                $this->section('episode-list'),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'podcast-show-detail' => [
                $this->navigation(),
                $this->section('latest-episode', [
                    'heading' => 'A show detail page built around the latest episode',
                    'summary' => 'The featured episode leads, with supporting episodes and subscribe paths kept legible and premium.',
                ]),
                $this->section('episode-list'),
                $this->section('subscribe-platforms'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'podcast-show-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Get in touch with the show',
                    'summary' => 'A confident, non-submitting contact path proves enquiries feel like part of the show experience.',
                ]),
                $this->section('hosts'),
                $this->section('proof'),
                $this->footer(),
            ],
            'podcast-show-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'No episodes published yet',
                    'summary' => 'The empty state stays on brand and invites visitors to subscribe before the first episode lands.',
                ]),
                $this->section('subscribe-platforms'),
                $this->footer(),
            ],
            'podcast-show-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That episode could not be found',
                    'summary' => 'A premium 404 keeps listeners moving back into the catalogue instead of leaving the show.',
                ]),
                $this->section('episode-list'),
                $this->footer(),
            ],
            'podcast-show-cta' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Subscribe and never miss an episode',
                    'summary' => 'A focused conversion CTA pairs subscribe platforms with proof so listeners commit with confidence.',
                ]),
                $this->section('subscribe-platforms'),
                $this->section('proof'),
                $this->footer(),
            ],
            'podcast-show-episodes' => [
                $this->navigation(),
                $this->section('episode-list', [
                    'heading' => 'The full episode archive, built to be scanned',
                    'summary' => 'Every episode stays scannable and premium without the theme owning episode records.',
                ]),
                $this->section('latest-episode'),
                $this->section('subscribe-platforms'),
                $this->section('cta'),
                $this->footer(),
            ],
            'podcast-show-guests' => [
                $this->navigation(),
                $this->section('guests', [
                    'heading' => 'A guest roster worth showcasing',
                    'summary' => 'Featured guests stay editorial and premium, reinforcing the calibre of the show.',
                ]),
                $this->section('hosts'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'podcast-show-sponsors' => [
                $this->navigation(),
                $this->section('proof', [
                    'heading' => 'Sponsors and partners who back the show',
                    'summary' => 'Social proof and partner logos build trust without the theme owning sponsor records.',
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
    private function section(string $sectionKey, array $data = []): PodcastShowScreenshotSection
    {
        return new PodcastShowScreenshotSection($sectionKey, $data);
    }

    private function navigation(): PodcastShowScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'The Long Game',
            'items' => [
                ['label' => 'Episodes', 'url' => '#episodes'],
                ['label' => 'Guests', 'url' => '#guests'],
                ['label' => 'Hosts', 'url' => '#hosts'],
                ['label' => 'Subscribe', 'url' => '#subscribe'],
            ],
            'subscribeUrl' => '#subscribe',
        ]);
    }

    private function hero(): PodcastShowScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A show worth subscribing to before the next episode drops',
            'eyebrow' => 'Podcast',
            'summary' => 'A premium audio-show homepage for episodes, guests, hosts, subscribe platforms, and conversion-led journeys.',
            'actions' => [
                ['label' => 'Subscribe', 'url' => '#subscribe'],
                ['label' => 'Browse episodes', 'url' => '#episodes'],
            ],
        ]);
    }

    private function footer(): PodcastShowScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'The Long Game',
            'items' => [
                ['label' => 'Episodes', 'url' => '#episodes'],
                ['label' => 'Guests', 'url' => '#guests'],
                ['label' => 'Hosts', 'url' => '#hosts'],
                ['label' => 'Subscribe', 'url' => '#subscribe'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'podcast-show-directory' => 'Theme Podcast Show directory',
            'podcast-show-detail' => 'Theme Podcast Show detail',
            'podcast-show-contact' => 'Theme Podcast Show contact',
            'podcast-show-empty' => 'Theme Podcast Show empty state',
            'podcast-show-not-found' => 'Theme Podcast Show 404 state',
            'podcast-show-cta' => 'Theme Podcast Show conversion CTA',
            'podcast-show-episodes' => 'Theme Podcast Show episodes',
            'podcast-show-guests' => 'Theme Podcast Show guests',
            'podcast-show-sponsors' => 'Theme Podcast Show sponsors',
            default => 'Theme Podcast Show homepage',
        };
    }
}
