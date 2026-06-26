<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\RawIndex\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class RawIndexScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-raw-index::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (RawIndexScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-raw-index::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#000000',
                accentColor: '#0000ee',
                neutralColor: '#4b4b4b',
                headingFont: 'mono',
                bodyFont: 'mono',
                spacing: 'balanced',
                cardStyle: 'raw',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'irregular-index',
                radius: 'none',
                surfaceColor: '#eeeeee',
                foregroundColor: '#000000',
                headingScale: 'balanced',
                cardDensity: 'dense',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'raw-index',
        ])->render();

        return view('capell-theme-raw-index::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, RawIndexScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'raw-index-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('archive-wall'),
                $this->section('rough-links'),
                $this->section('irregular-index'),
                $this->section('submission-markers'),
                $this->section('archive-dates'),
                $this->section('zine-annotations'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'raw-index-landing-page' => [
                $this->navigation(),
                $this->section('irregular-index', [
                    'heading' => 'A long index entry laid out raw',
                    'summary' => 'A feature page pairs irregular media with submission notes, interview labels, and corrections without softening the archive.',
                ]),
                $this->section('zine-annotations'),
                $this->section('submission-markers'),
                $this->section('cta'),
                $this->footer(),
            ],
            'raw-index-list-page' => [
                $this->navigation(),
                $this->section('archive-wall', [
                    'heading' => 'An archive wall built to be scanned',
                    'summary' => 'Dense rows of dates, markers, interviews, and source collections stay legible without the theme owning the records.',
                ]),
                $this->section('archive-dates'),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'raw-index-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results across the raw archive',
                    'summary' => 'Stark linked rows surface entries, dates, languages, and issue notes so discovery stays fast and unadorned.',
                ]),
                $this->section('rough-links'),
                $this->footer(),
            ],
            'raw-index-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'One raw path to reach the archive',
                    'summary' => 'A non-submitting signup, RSS prompt, and submission note prove the contact journey feels like part of the index.',
                ]),
                $this->section('submission-markers'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): RawIndexScreenshotSection
    {
        return new RawIndexScreenshotSection($sectionKey, $data);
    }

    private function navigation(): RawIndexScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'RAW INDEX',
            'items' => [
                ['label' => 'Archive', 'url' => '#archive'],
                ['label' => 'Index', 'url' => '#index'],
                ['label' => 'Submit', 'url' => '#submit'],
                ['label' => 'Newsletter', 'url' => '#newsletter'],
            ],
            'consultationUrl' => '#submit',
        ]);
    }

    private function hero(): RawIndexScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'An archive that stays deliberately raw',
            'eyebrow' => 'Raw Index',
            'summary' => 'A monospace homepage for archive walls, stark links, irregular media, submission markers, interview labels, and archive dates.',
            'actions' => [
                ['label' => 'Browse the archive', 'url' => '#archive'],
                ['label' => 'Submit an entry', 'url' => '#submit'],
            ],
        ]);
    }

    private function footer(): RawIndexScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'RAW INDEX',
            'items' => [
                ['label' => 'Archive', 'url' => '#archive'],
                ['label' => 'Index', 'url' => '#index'],
                ['label' => 'Submit', 'url' => '#submit'],
                ['label' => 'Newsletter', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'raw-index-landing-page' => 'Theme Raw Index landing page',
            'raw-index-list-page' => 'Theme Raw Index archive',
            'raw-index-search-results' => 'Theme Raw Index search results',
            'raw-index-contact-form' => 'Theme Raw Index contact form',
            default => 'Theme Raw Index homepage',
        };
    }
}
