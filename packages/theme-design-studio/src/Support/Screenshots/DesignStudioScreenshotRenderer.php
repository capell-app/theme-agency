<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DesignStudio\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class DesignStudioScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-design-studio::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (DesignStudioScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-design-studio::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#1c1917',
                accentColor: '#c2683f',
                neutralColor: '#292524',
                headingFont: 'fraunces',
                bodyFont: 'inter',
                spacing: 'airy',
                cardStyle: 'flat',
                navigationStyle: 'minimal',
                layoutPresentation: 'editorial',
                mediaTreatment: 'framed',
                radius: 'none',
                surfaceColor: '#faf7f2',
                foregroundColor: '#1c1917',
                headingScale: 'dramatic',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'design-studio',
        ])->render();

        return view('capell-theme-design-studio::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, DesignStudioScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'design-studio-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('project-gallery'),
                $this->section('lookbook'),
                $this->section('studio-services'),
                $this->section('awards'),
                $this->section('studio-statement'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'design-studio-directory' => [
                $this->navigation(),
                $this->section('project-gallery', [
                    'heading' => 'A directory of projects built to be scanned',
                    'summary' => 'Structured project cards keep the studio editorial and legible without the theme owning portfolio records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'design-studio-detail' => [
                $this->navigation(),
                $this->section('project-gallery', [
                    'heading' => 'A project profile that reads with intent',
                    'summary' => 'A single project view pairs imagery and proof so prospective clients can engage with confidence.',
                ]),
                $this->section('studio-statement'),
                $this->section('awards'),
                $this->section('cta'),
                $this->footer(),
            ],
            'design-studio-contact' => [
                $this->navigation(),
                $this->section('studio-statement', [
                    'heading' => 'Reach the studio through one considered path',
                    'summary' => 'A non-submitting contact statement proves the enquiry journey feels like part of the studio experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'design-studio-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays editorial and structured while the studio prepares its work.',
                ]),
                $this->footer(),
            ],
            'design-studio-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the studio editorial and routes visitors back into the portfolio journey.',
                ]),
                $this->footer(),
            ],
            'design-studio-cta' => [
                $this->navigation(),
                $this->section('studio-statement'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn interest into a booked studio conversation',
                    'summary' => 'A conversion-focused CTA stack keeps the path to commissioning the studio direct and editorial.',
                ]),
                $this->footer(),
            ],
            'design-studio-projects' => [
                $this->navigation(),
                $this->section('project-gallery', [
                    'heading' => 'Projects built to be scanned and admired',
                    'summary' => 'Structured project groupings keep the portfolio editorial and legible without owning project records.',
                ]),
                $this->section('lookbook'),
                $this->section('cta'),
                $this->footer(),
            ],
            'design-studio-studio' => [
                $this->navigation(),
                $this->section('studio-statement', [
                    'heading' => 'Meet the practice behind the work',
                    'summary' => 'An editorial studio statement keeps the team and philosophy legible without the theme owning people records.',
                ]),
                $this->section('studio-services'),
                $this->section('awards'),
                $this->section('cta'),
                $this->footer(),
            ],
            'design-studio-case-study' => [
                $this->navigation(),
                $this->section('project-gallery', [
                    'heading' => 'A case study that reads with editorial intent',
                    'summary' => 'A detailed case study pairs imagery and proof so prospective clients can engage with confidence.',
                ]),
                $this->section('lookbook'),
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
    private function section(string $sectionKey, array $data = []): DesignStudioScreenshotSection
    {
        return new DesignStudioScreenshotSection($sectionKey, $data);
    }

    private function navigation(): DesignStudioScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Marrow & Vale',
            'items' => [
                ['label' => 'Projects', 'url' => '#projects'],
                ['label' => 'Lookbook', 'url' => '#lookbook'],
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Studio', 'url' => '#studio'],
            ],
            'consultationUrl' => '#contact',
        ]);
    }

    private function hero(): DesignStudioScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Spaces a studio can stand behind',
            'eyebrow' => 'Design Studio',
            'summary' => 'An editorial design homepage for projects, lookbooks, studio services, awards, and enquiry-led journeys.',
            'actions' => [
                ['label' => 'Start a project', 'url' => '#contact'],
                ['label' => 'View projects', 'url' => '#projects'],
            ],
        ]);
    }

    private function footer(): DesignStudioScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Marrow & Vale',
            'items' => [
                ['label' => 'Projects', 'url' => '#projects'],
                ['label' => 'Lookbook', 'url' => '#lookbook'],
                ['label' => 'Services', 'url' => '#services'],
                ['label' => 'Studio', 'url' => '#studio'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'design-studio-directory' => 'Theme Design Studio directory',
            'design-studio-detail' => 'Theme Design Studio detail',
            'design-studio-contact' => 'Theme Design Studio contact',
            'design-studio-empty' => 'Theme Design Studio empty state',
            'design-studio-not-found' => 'Theme Design Studio 404 state',
            'design-studio-cta' => 'Theme Design Studio conversion CTA',
            'design-studio-projects' => 'Theme Design Studio projects',
            'design-studio-studio' => 'Theme Design Studio studio',
            'design-studio-case-study' => 'Theme Design Studio case study',
            default => 'Theme Design Studio homepage',
        };
    }
}
