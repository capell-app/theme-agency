<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ProductStudio\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class ProductStudioScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-product-studio::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (ProductStudioScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-product-studio::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#2563eb',
                accentColor: '#14b8a6',
                neutralColor: '#0f172a',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'flat',
                radius: 'md',
                surfaceColor: '#ffffff',
                foregroundColor: '#0f172a',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'product-studio',
        ])->render();

        return view('capell-theme-product-studio::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, ProductStudioScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'product-studio-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('case-studies'),
                $this->section('tech-stack'),
                $this->section('process'),
                $this->section('engagement-models'),
                $this->section('features'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'product-studio-directory' => [
                $this->navigation(),
                $this->section('case-studies', [
                    'heading' => 'A directory of shipped work built to be scanned',
                    'summary' => 'Structured case-study cards keep the studio credible and legible without the theme owning project records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'product-studio-detail' => [
                $this->navigation(),
                $this->section('case-studies', [
                    'heading' => 'A case study that reads with engineering rigour',
                    'summary' => 'A single project view pairs outcomes and stack so prospective clients can evaluate with confidence.',
                ]),
                $this->section('process'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'product-studio-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Start a project through one confident path',
                    'summary' => 'A non-submitting contact CTA proves the enquiry journey feels like part of the studio experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'product-studio-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays structured and credible while the studio prepares its content.',
                ]),
                $this->footer(),
            ],
            'product-studio-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the studio credible and routes visitors back into the engagement journey.',
                ]),
                $this->footer(),
            ],
            'product-studio-cta' => [
                $this->navigation(),
                $this->section('engagement-models'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a scoped engagement',
                    'summary' => 'A conversion-focused CTA stack keeps the path to starting a project direct and credible.',
                ]),
                $this->footer(),
            ],
            'product-studio-case-studies' => [
                $this->navigation(),
                $this->section('case-studies', [
                    'heading' => 'Case studies built to be scanned and trusted',
                    'summary' => 'Structured project groupings keep shipped work credible and legible without owning project records.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'product-studio-engagements' => [
                $this->navigation(),
                $this->section('engagement-models', [
                    'heading' => 'Engagement models built to be compared',
                    'summary' => 'Structured engagement options keep the ways to work together clear and credible.',
                ]),
                $this->section('process'),
                $this->section('cta'),
                $this->footer(),
            ],
            'product-studio-stack' => [
                $this->navigation(),
                $this->section('tech-stack', [
                    'heading' => 'A technical stack you can stand behind',
                    'summary' => 'Structured stack groupings keep capabilities authoritative and legible for technical buyers.',
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
    private function section(string $sectionKey, array $data = []): ProductStudioScreenshotSection
    {
        return new ProductStudioScreenshotSection($sectionKey, $data);
    }

    private function navigation(): ProductStudioScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Northbound Studio',
            'items' => [
                ['label' => 'Case studies', 'url' => '#case-studies'],
                ['label' => 'Tech stack', 'url' => '#tech-stack'],
                ['label' => 'Engagement models', 'url' => '#engagement-models'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'consultationUrl' => '#contact',
        ]);
    }

    private function hero(): ProductStudioScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'A product studio teams can ship behind',
            'eyebrow' => 'Product Studio',
            'summary' => 'A structured engineering homepage for case studies, tech stack, process, engagement models, and conversion-led journeys.',
            'actions' => [
                ['label' => 'Start a project', 'url' => '#contact'],
                ['label' => 'View case studies', 'url' => '#case-studies'],
            ],
        ]);
    }

    private function footer(): ProductStudioScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Northbound Studio',
            'items' => [
                ['label' => 'Case studies', 'url' => '#case-studies'],
                ['label' => 'Tech stack', 'url' => '#tech-stack'],
                ['label' => 'Engagement models', 'url' => '#engagement-models'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'product-studio-directory' => 'Theme Product Studio directory',
            'product-studio-detail' => 'Theme Product Studio detail',
            'product-studio-contact' => 'Theme Product Studio contact',
            'product-studio-empty' => 'Theme Product Studio empty state',
            'product-studio-not-found' => 'Theme Product Studio 404 state',
            'product-studio-cta' => 'Theme Product Studio conversion CTA',
            'product-studio-case-studies' => 'Theme Product Studio case studies',
            'product-studio-engagements' => 'Theme Product Studio engagement models',
            'product-studio-stack' => 'Theme Product Studio tech stack',
            default => 'Theme Product Studio homepage',
        };
    }
}
