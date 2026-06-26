<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ConferenceEvent\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class ConferenceEventScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-conference-event::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (ConferenceEventScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-conference-event::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#6d28d9',
                accentColor: '#f472b6',
                neutralColor: '#1e1b2e',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'elevated',
                navigationStyle: 'prominent',
                layoutPresentation: 'immersive',
                mediaTreatment: 'framed',
                radius: 'lg',
                surfaceColor: '#faf5ff',
                foregroundColor: '#1e1b2e',
                headingScale: 'dramatic',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'conference-event',
        ])->render();

        return view('capell-theme-conference-event::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, ConferenceEventScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'conference-event-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('agenda'),
                $this->section('speakers'),
                $this->section('ticket-tiers'),
                $this->section('features'),
                $this->section('sponsors'),
                $this->section('venue'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'conference-event-directory' => [
                $this->navigation(),
                $this->section('agenda', [
                    'heading' => 'A multi-track agenda built to be scanned',
                    'summary' => 'Structured session cards keep the programme legible and navigable without the theme owning schedule records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'conference-event-detail' => [
                $this->navigation(),
                $this->section('speakers', [
                    'heading' => 'A speaker profile that reads with authority',
                    'summary' => 'A single speaker view pairs sessions and credentials so attendees can plan their day with confidence.',
                ]),
                $this->section('agenda'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'conference-event-contact' => [
                $this->navigation(),
                $this->section('venue', [
                    'heading' => 'Find the venue through one confident path',
                    'summary' => 'A non-submitting venue and contact panel proves the journey feels like part of the event experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'conference-event-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays immersive and structured while the event prepares its programme.',
                ]),
                $this->footer(),
            ],
            'conference-event-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the event vivid and routes visitors back into the conference journey.',
                ]),
                $this->footer(),
            ],
            'conference-event-cta' => [
                $this->navigation(),
                $this->section('ticket-tiers'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a booked ticket',
                    'summary' => 'A conversion-focused CTA stack keeps the path to securing a place direct and immersive.',
                ]),
                $this->footer(),
            ],
            'conference-event-agenda' => [
                $this->navigation(),
                $this->section('agenda', [
                    'heading' => 'A multi-track agenda built to be scanned and trusted',
                    'summary' => 'Structured session groupings keep tracks legible and navigable without owning schedule records.',
                ]),
                $this->section('speakers'),
                $this->section('cta'),
                $this->footer(),
            ],
            'conference-event-tickets' => [
                $this->navigation(),
                $this->section('ticket-tiers', [
                    'heading' => 'Ticket tiers built to convert with confidence',
                    'summary' => 'Structured pricing tiers keep the path to securing a place legible and immersive.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'conference-event-speakers' => [
                $this->navigation(),
                $this->section('speakers', [
                    'heading' => 'Meet the speakers behind the programme',
                    'summary' => 'Editorial speaker cards keep the line-up vivid and legible without the theme owning people records.',
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
    private function section(string $sectionKey, array $data = []): ConferenceEventScreenshotSection
    {
        return new ConferenceEventScreenshotSection($sectionKey, $data);
    }

    private function navigation(): ConferenceEventScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Horizon Summit',
            'items' => [
                ['label' => 'Agenda', 'url' => '#agenda'],
                ['label' => 'Speakers', 'url' => '#speakers'],
                ['label' => 'Tickets', 'url' => '#tickets'],
                ['label' => 'Venue', 'url' => '#venue'],
            ],
            'consultationUrl' => '#tickets',
        ]);
    }

    private function hero(): ConferenceEventScreenshotSection
    {
        return $this->section('event-hero', [
            'heading' => 'A summit your industry can rally behind',
            'eyebrow' => 'Conference Event',
            'summary' => 'An immersive conference homepage for agendas, speakers, ticket tiers, sponsors, venues, and ticket-led journeys.',
            'actions' => [
                ['label' => 'Get tickets', 'url' => '#tickets'],
                ['label' => 'View agenda', 'url' => '#agenda'],
            ],
        ]);
    }

    private function footer(): ConferenceEventScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Horizon Summit',
            'items' => [
                ['label' => 'Agenda', 'url' => '#agenda'],
                ['label' => 'Speakers', 'url' => '#speakers'],
                ['label' => 'Tickets', 'url' => '#tickets'],
                ['label' => 'Venue', 'url' => '#venue'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'conference-event-directory' => 'Theme Conference Event directory',
            'conference-event-detail' => 'Theme Conference Event detail',
            'conference-event-contact' => 'Theme Conference Event contact',
            'conference-event-empty' => 'Theme Conference Event empty state',
            'conference-event-not-found' => 'Theme Conference Event 404 state',
            'conference-event-cta' => 'Theme Conference Event conversion CTA',
            'conference-event-agenda' => 'Theme Conference Event agenda',
            'conference-event-tickets' => 'Theme Conference Event tickets',
            'conference-event-speakers' => 'Theme Conference Event speakers',
            default => 'Theme Conference Event homepage',
        };
    }
}
