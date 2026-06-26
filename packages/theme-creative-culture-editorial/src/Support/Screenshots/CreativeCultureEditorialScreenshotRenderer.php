<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CreativeCultureEditorial\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class CreativeCultureEditorialScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-creative-culture-editorial::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (CreativeCultureEditorialScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-creative-culture-editorial::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#111111',
                accentColor: '#e4512f',
                neutralColor: '#201719',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'illustrated',
                radius: 'md',
                surfaceColor: '#fff8ec',
                foregroundColor: '#111111',
                headingScale: 'balanced',
                cardDensity: 'airy',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'creative-culture-editorial',
        ])->render();

        return view('capell-theme-creative-culture-editorial::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, CreativeCultureEditorialScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'creative-culture-editorial-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('must-reads'),
                $this->section('project-stories'),
                $this->section('opinion-block'),
                $this->section('advice-culture'),
                $this->section('events-tags'),
                $this->section('discipline-browsing'),
                $this->section('proof'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'creative-culture-editorial-landing-page' => [
                $this->navigation(),
                $this->section('project-stories', [
                    'heading' => 'A feature page art-directed to be read',
                    'summary' => 'A single editorial feature pairs image-led storytelling with opinion and advice so a project reads as culture, not just a record.',
                ]),
                $this->section('opinion-block'),
                $this->section('advice-culture'),
                $this->section('newsletter'),
                $this->footer(),
            ],
            'creative-culture-editorial-list-page' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'An archive built to be browsed',
                    'summary' => 'Structured listing cards keep projects, opinion, advice, and culture stories legible without the theme owning content records.',
                ]),
                $this->section('discipline-browsing'),
                $this->section('events-tags'),
                $this->footer(),
            ],
            'creative-culture-editorial-search-results' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Search results across the editorial catalogue',
                    'summary' => 'A discovery view stays art-directed while surfacing projects, disciplines, tags, events, and formats in one scannable result set.',
                ]),
                $this->section('discipline-browsing'),
                $this->footer(),
            ],
            'creative-culture-editorial-contact-form' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Turn readers into subscribers and contributors',
                    'summary' => 'A non-submitting newsletter and conversion module proves the signup journey feels like part of the editorial experience.',
                ]),
                $this->section('events-tags'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): CreativeCultureEditorialScreenshotSection
    {
        return new CreativeCultureEditorialScreenshotSection($sectionKey, $data);
    }

    private function navigation(): CreativeCultureEditorialScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Maker & Margin',
            'items' => [
                ['label' => 'Projects', 'url' => '#project-stories'],
                ['label' => 'Opinion', 'url' => '#opinion-block'],
                ['label' => 'Culture', 'url' => '#advice-culture'],
                ['label' => 'Events', 'url' => '#events-tags'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): CreativeCultureEditorialScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Culture, projects, and opinion worth your attention',
            'eyebrow' => 'Creative Culture Editorial',
            'summary' => 'An art-directed editorial homepage for must-reads, project stories, opinion, advice, culture, events, popular tags, discipline browsing, and newsletter conversion.',
            'actions' => [
                ['label' => 'Read the latest projects', 'url' => '#project-stories'],
                ['label' => 'Join the newsletter', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function footer(): CreativeCultureEditorialScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Maker & Margin',
            'items' => [
                ['label' => 'Projects', 'url' => '#project-stories'],
                ['label' => 'Opinion', 'url' => '#opinion-block'],
                ['label' => 'Culture', 'url' => '#advice-culture'],
                ['label' => 'Events', 'url' => '#events-tags'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'creative-culture-editorial-landing-page' => 'Theme Creative Culture Editorial landing page',
            'creative-culture-editorial-list-page' => 'Theme Creative Culture Editorial list page',
            'creative-culture-editorial-search-results' => 'Theme Creative Culture Editorial search results',
            'creative-culture-editorial-contact-form' => 'Theme Creative Culture Editorial contact form',
            default => 'Theme Creative Culture Editorial homepage',
        };
    }
}
