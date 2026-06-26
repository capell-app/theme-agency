<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\NewsroomMagazine\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class NewsroomMagazineScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-newsroom-magazine::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (NewsroomMagazineScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-newsroom-magazine::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#b91c1c',
                accentColor: '#1d4ed8',
                neutralColor: '#171717',
                headingFont: 'fraunces',
                bodyFont: 'inter',
                spacing: 'airy',
                cardStyle: 'flat',
                navigationStyle: 'prominent',
                layoutPresentation: 'editorial',
                mediaTreatment: 'flat',
                radius: 'none',
                surfaceColor: '#fbfaf8',
                foregroundColor: '#171717',
                headingScale: 'dramatic',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'newsroom-magazine',
        ])->render();

        return view('capell-theme-newsroom-magazine::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, NewsroomMagazineScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'newsroom-magazine-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('featured-story'),
                $this->section('category-nav'),
                $this->section('story-grid'),
                $this->section('most-read'),
                $this->section('contributors'),
                $this->section('newsletter-signup'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'newsroom-magazine-directory' => [
                $this->navigation(),
                $this->section('story-grid', [
                    'heading' => 'A story index built to be scanned',
                    'summary' => 'Structured story cards keep the publication editorial and legible without the theme owning article records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'newsroom-magazine-detail' => [
                $this->navigation(),
                $this->section('featured-story', [
                    'heading' => 'A story page that reads with authority',
                    'summary' => 'A single article view pairs lead reporting and context so readers can engage with confidence.',
                ]),
                $this->section('most-read'),
                $this->section('contributors'),
                $this->section('cta'),
                $this->footer(),
            ],
            'newsroom-magazine-contact' => [
                $this->navigation(),
                $this->section('newsletter-signup', [
                    'heading' => 'Reach the newsroom through one clear path',
                    'summary' => 'A non-submitting newsletter CTA proves the contact journey feels like part of the publication experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'newsroom-magazine-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays editorial and structured while the newsroom prepares its coverage.',
                ]),
                $this->footer(),
            ],
            'newsroom-magazine-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the publication editorial and routes readers back into the coverage journey.',
                ]),
                $this->footer(),
            ],
            'newsroom-magazine-cta' => [
                $this->navigation(),
                $this->section('newsletter-signup'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn a reader into a subscriber',
                    'summary' => 'A conversion-focused CTA stack keeps the path to subscribing direct and editorial.',
                ]),
                $this->footer(),
            ],
            'newsroom-magazine-front-page' => [
                $this->navigation(),
                $this->section('featured-story', [
                    'heading' => 'A front page that leads with the story',
                    'summary' => 'A dramatic lead story keeps the publication authoritative and legible without owning article records.',
                ]),
                $this->section('category-nav'),
                $this->section('story-grid'),
                $this->section('most-read'),
                $this->section('cta'),
                $this->footer(),
            ],
            'newsroom-magazine-contributors' => [
                $this->navigation(),
                $this->section('contributors', [
                    'heading' => 'Meet the contributors behind the coverage',
                    'summary' => 'Editorial contributor cards keep the masthead authoritative and legible without the theme owning people records.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'newsroom-magazine-newsletter' => [
                $this->navigation(),
                $this->section('newsletter-signup', [
                    'heading' => 'Subscribe to the newsroom in one clear path',
                    'summary' => 'A non-submitting newsletter CTA proves the subscription journey feels like part of the publication experience.',
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
    private function section(string $sectionKey, array $data = []): NewsroomMagazineScreenshotSection
    {
        return new NewsroomMagazineScreenshotSection($sectionKey, $data);
    }

    private function navigation(): NewsroomMagazineScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'The Meridian Review',
            'items' => [
                ['label' => 'Front page', 'url' => '#front-page'],
                ['label' => 'Stories', 'url' => '#stories'],
                ['label' => 'Contributors', 'url' => '#contributors'],
                ['label' => 'Newsletter', 'url' => '#newsletter'],
            ],
            'consultationUrl' => '#newsletter',
        ]);
    }

    private function hero(): NewsroomMagazineScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Journalism a readership can stand behind',
            'eyebrow' => 'Newsroom Magazine',
            'summary' => 'An editorial homepage for featured stories, category navigation, story grids, contributors, and newsletter-led journeys.',
            'actions' => [
                ['label' => 'Read the front page', 'url' => '#front-page'],
                ['label' => 'Browse stories', 'url' => '#stories'],
            ],
        ]);
    }

    private function footer(): NewsroomMagazineScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'The Meridian Review',
            'items' => [
                ['label' => 'Front page', 'url' => '#front-page'],
                ['label' => 'Stories', 'url' => '#stories'],
                ['label' => 'Contributors', 'url' => '#contributors'],
                ['label' => 'Newsletter', 'url' => '#newsletter'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'newsroom-magazine-directory' => 'Theme Newsroom Magazine directory',
            'newsroom-magazine-detail' => 'Theme Newsroom Magazine detail',
            'newsroom-magazine-contact' => 'Theme Newsroom Magazine contact',
            'newsroom-magazine-empty' => 'Theme Newsroom Magazine empty state',
            'newsroom-magazine-not-found' => 'Theme Newsroom Magazine 404 state',
            'newsroom-magazine-cta' => 'Theme Newsroom Magazine conversion CTA',
            'newsroom-magazine-front-page' => 'Theme Newsroom Magazine front page',
            'newsroom-magazine-contributors' => 'Theme Newsroom Magazine contributors',
            'newsroom-magazine-newsletter' => 'Theme Newsroom Magazine newsletter',
            default => 'Theme Newsroom Magazine homepage',
        };
    }
}
