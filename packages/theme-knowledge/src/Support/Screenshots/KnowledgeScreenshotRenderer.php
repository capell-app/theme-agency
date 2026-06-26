<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Knowledge\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class KnowledgeScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-knowledge::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (KnowledgeScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-knowledge::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#1d4ed8',
                accentColor: '#f59e0b',
                neutralColor: '#172033',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#f8fafc',
                foregroundColor: '#172033',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'knowledge',
        ])->render();

        return view('capell-theme-knowledge::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, KnowledgeScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'frontend-page-rendered-with-knowledge-theme', 'knowledge-homepage-layout' => [
                $this->navigation(),
                $this->hero(),
                $this->section('featured-content'),
                $this->section('topic-hubs'),
                $this->section('resource-library'),
                $this->section('reading-path'),
                $this->section('newsletter'),
                $this->section('cta'),
                $this->footer(),
            ],
            'knowledge-search-layout' => [
                $this->navigation(),
                $this->section('search-listing', [
                    'heading' => 'Search the library without it collapsing into a list',
                    'summary' => 'Faceted results keep guides, research notes, and templates legible while the theme stays a serious reading surface.',
                ]),
                $this->section('topic-index'),
                $this->section('content-listing'),
                $this->footer(),
            ],
            'knowledge-topic-hubs-layout' => [
                $this->navigation(),
                $this->section('topic-hubs', [
                    'heading' => 'Deep archives made browseable by subject',
                    'summary' => 'Structured topic hubs let an editorial team open a large library without flattening it into a blog feed.',
                ]),
                $this->section('topic-index'),
                $this->section('cta'),
                $this->footer(),
            ],
            'knowledge-featured-content-layout' => [
                $this->navigation(),
                $this->section('featured-content', [
                    'heading' => 'Promote high-value research with confidence',
                    'summary' => 'A featured research treatment elevates flagship work without looking like a standard news card.',
                ]),
                $this->section('resource-library'),
                $this->section('cta'),
                $this->footer(),
            ],
            'knowledge-newsletter-layout' => [
                $this->navigation(),
                $this->section('newsletter', [
                    'heading' => 'Turn reading intent into an owned audience',
                    'summary' => 'A research-digest signup converts engaged readers into subscribers as part of the library experience.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'knowledge-author-bench-layout' => [
                $this->navigation(),
                $this->section('authors', [
                    'heading' => 'Prove expertise without becoming a portfolio',
                    'summary' => 'An author bench shows the people behind the research while the theme stays a knowledge library, not an agency site.',
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
    private function section(string $sectionKey, array $data = []): KnowledgeScreenshotSection
    {
        return new KnowledgeScreenshotSection($sectionKey, $data);
    }

    private function navigation(): KnowledgeScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Fieldnote Library',
            'items' => [
                ['label' => 'Topics', 'url' => '#topic-hubs'],
                ['label' => 'Research', 'url' => '#featured-content'],
                ['label' => 'Library', 'url' => '#resource-library'],
                ['label' => 'Search', 'url' => '#search'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): KnowledgeScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A library a reader can actually navigate',
            'eyebrow' => 'Knowledge',
            'summary' => 'An editorial knowledge-base homepage for topic hubs, featured research, a resource library, and search-led reader journeys.',
            'actions' => [
                ['label' => 'Browse topics', 'url' => '#topic-hubs'],
                ['label' => 'Search the library', 'url' => '#search'],
            ],
        ]);
    }

    private function footer(): KnowledgeScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Fieldnote Library',
            'items' => [
                ['label' => 'Topics', 'url' => '#topic-hubs'],
                ['label' => 'Research', 'url' => '#featured-content'],
                ['label' => 'Library', 'url' => '#resource-library'],
                ['label' => 'Newsletter', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'knowledge-search-layout' => 'Theme Knowledge search',
            'knowledge-topic-hubs-layout' => 'Theme Knowledge topic hubs',
            'knowledge-featured-content-layout' => 'Theme Knowledge featured research',
            'knowledge-newsletter-layout' => 'Theme Knowledge research digest signup',
            'knowledge-author-bench-layout' => 'Theme Knowledge author bench',
            default => 'Theme Knowledge homepage',
        };
    }
}
