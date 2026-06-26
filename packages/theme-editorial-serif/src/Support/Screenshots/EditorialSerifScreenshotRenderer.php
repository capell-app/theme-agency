<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\EditorialSerif\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class EditorialSerifScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-editorial-serif::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (EditorialSerifScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-editorial-serif::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#7c2d12',
                accentColor: '#166534',
                neutralColor: '#292524',
                headingFont: 'fraunces',
                bodyFont: 'newsreader',
                spacing: 'airy',
                cardStyle: 'flat',
                navigationStyle: 'minimal',
                layoutPresentation: 'editorial',
                mediaTreatment: 'framed',
                radius: 'none',
                surfaceColor: '#faf8f4',
                foregroundColor: '#1a1a1a',
                headingScale: 'dramatic',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'editorial-serif',
        ])->render();

        return view('capell-theme-editorial-serif::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, EditorialSerifScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'editorial-serif-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('essay-index'),
                $this->section('issue-archive'),
                $this->section('author-profiles'),
                $this->section('editorial-statement'),
                $this->section('subscription-panel'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'editorial-serif-directory' => [
                $this->navigation(),
                $this->section('essay-index', [
                    'heading' => 'An index of essays built to be read',
                    'summary' => 'Structured essay cards keep the publication legible and inviting without the theme owning article records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'editorial-serif-detail' => [
                $this->navigation(),
                $this->section('essay-index', [
                    'heading' => 'A single essay that reads with care',
                    'summary' => 'One essay view pairs editorial typography and author context so readers can settle into the writing.',
                ]),
                $this->section('author-profiles'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'editorial-serif-contact' => [
                $this->navigation(),
                $this->section('subscription-panel', [
                    'heading' => 'Reach the publication through one quiet path',
                    'summary' => 'A non-submitting subscription panel proves the contact journey feels like part of the editorial experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'editorial-serif-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays editorial and considered while the publication prepares its writing.',
                ]),
                $this->footer(),
            ],
            'editorial-serif-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the publication composed and routes readers back into the editorial journey.',
                ]),
                $this->footer(),
            ],
            'editorial-serif-cta' => [
                $this->navigation(),
                $this->section('subscription-panel'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn a reader into a subscriber',
                    'summary' => 'A conversion-focused CTA stack keeps the path to following the publication direct and editorial.',
                ]),
                $this->footer(),
            ],
            'editorial-serif-essays' => [
                $this->navigation(),
                $this->section('essay-index', [
                    'heading' => 'Essays built to be scanned and savoured',
                    'summary' => 'Structured essay groupings keep the writing legible and inviting without owning article records.',
                ]),
                $this->section('author-profiles'),
                $this->section('cta'),
                $this->footer(),
            ],
            'editorial-serif-archive' => [
                $this->navigation(),
                $this->section('issue-archive', [
                    'heading' => 'An archive of issues built to be browsed',
                    'summary' => 'Structured issue cards keep the back catalogue legible and inviting without the theme owning issue records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'editorial-serif-about' => [
                $this->navigation(),
                $this->section('editorial-statement', [
                    'heading' => 'The voice and intent behind the publication',
                    'summary' => 'An editorial statement keeps the publication considered and human without the theme owning page records.',
                ]),
                $this->section('author-profiles'),
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
    private function section(string $sectionKey, array $data = []): EditorialSerifScreenshotSection
    {
        return new EditorialSerifScreenshotSection($sectionKey, $data);
    }

    private function navigation(): EditorialSerifScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'The Quire Review',
            'items' => [
                ['label' => 'Essays', 'url' => '#essays'],
                ['label' => 'Archive', 'url' => '#archive'],
                ['label' => 'Authors', 'url' => '#authors'],
                ['label' => 'Subscribe', 'url' => '#subscribe'],
            ],
            'consultationUrl' => '#subscribe',
        ]);
    }

    private function hero(): EditorialSerifScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A publication a reader can settle into',
            'eyebrow' => 'Editorial Serif',
            'summary' => 'A print-grade editorial homepage for essays, issue archives, author profiles, editorial statements, and subscription-led journeys.',
            'actions' => [
                ['label' => 'Subscribe', 'url' => '#subscribe'],
                ['label' => 'Read the essays', 'url' => '#essays'],
            ],
        ]);
    }

    private function footer(): EditorialSerifScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'The Quire Review',
            'items' => [
                ['label' => 'Essays', 'url' => '#essays'],
                ['label' => 'Archive', 'url' => '#archive'],
                ['label' => 'Authors', 'url' => '#authors'],
                ['label' => 'Subscribe', 'url' => '#subscribe'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'editorial-serif-directory' => 'Theme Editorial Serif directory',
            'editorial-serif-detail' => 'Theme Editorial Serif detail',
            'editorial-serif-contact' => 'Theme Editorial Serif contact',
            'editorial-serif-empty' => 'Theme Editorial Serif empty state',
            'editorial-serif-not-found' => 'Theme Editorial Serif 404 state',
            'editorial-serif-cta' => 'Theme Editorial Serif conversion CTA',
            'editorial-serif-essays' => 'Theme Editorial Serif essays',
            'editorial-serif-archive' => 'Theme Editorial Serif archive',
            'editorial-serif-about' => 'Theme Editorial Serif about the publication',
            default => 'Theme Editorial Serif homepage',
        };
    }
}
