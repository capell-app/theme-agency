<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AiLab\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class AiLabScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-ai-lab::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (AiLabScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-ai-lab::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#6366f1',
                accentColor: '#22d3ee',
                neutralColor: '#18181b',
                headingFont: 'space-grotesk',
                bodyFont: 'inter',
                spacing: 'airy',
                cardStyle: 'flat',
                navigationStyle: 'minimal',
                layoutPresentation: 'immersive',
                mediaTreatment: 'framed',
                radius: 'sm',
                surfaceColor: '#0a0a0b',
                foregroundColor: '#f4f4f5',
                headingScale: 'dramatic',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'ai-lab',
        ])->render();

        return view('capell-theme-ai-lab::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, AiLabScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'ai-lab-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('model-cards'),
                $this->section('benchmarks'),
                $this->section('features'),
                $this->section('research-index'),
                $this->section('playground'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'ai-lab-directory' => [
                $this->navigation(),
                $this->section('research-index', [
                    'heading' => 'A research index built to be scanned',
                    'summary' => 'Structured research cards keep the lab authoritative and legible without the theme owning publication records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'ai-lab-detail' => [
                $this->navigation(),
                $this->section('model-cards', [
                    'heading' => 'A model profile that reads with authority',
                    'summary' => 'A single model view pairs capabilities and benchmarks so evaluators can engage with confidence.',
                ]),
                $this->section('benchmarks'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'ai-lab-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Reach the lab through one confident path',
                    'summary' => 'A non-submitting contact CTA proves the access journey feels like part of the lab experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'ai-lab-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays technical and structured while the lab prepares its research.',
                ]),
                $this->footer(),
            ],
            'ai-lab-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the lab authoritative and routes visitors back into the research journey.',
                ]),
                $this->footer(),
            ],
            'ai-lab-cta' => [
                $this->navigation(),
                $this->section('playground'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a model evaluation',
                    'summary' => 'A conversion-focused CTA stack keeps the path to running the model direct and technical.',
                ]),
                $this->footer(),
            ],
            'ai-lab-model-suite' => [
                $this->navigation(),
                $this->section('model-cards', [
                    'heading' => 'A model suite built to be scanned and trusted',
                    'summary' => 'Structured model groupings keep capabilities authoritative and legible without owning inference records.',
                ]),
                $this->section('benchmarks'),
                $this->section('cta'),
                $this->footer(),
            ],
            'ai-lab-research-library' => [
                $this->navigation(),
                $this->section('research-index', [
                    'heading' => 'Browse the research behind the lab',
                    'summary' => 'Editorial research cards keep the publication library authoritative and legible without the theme owning paper records.',
                ]),
                $this->section('content-listing'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'ai-lab-playground-preview' => [
                $this->navigation(),
                $this->section('playground', [
                    'heading' => 'Evaluate the model in one confident path',
                    'summary' => 'A non-submitting playground preview proves the evaluation journey feels like part of the lab experience.',
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
    private function section(string $sectionKey, array $data = []): AiLabScreenshotSection
    {
        return new AiLabScreenshotSection($sectionKey, $data);
    }

    private function navigation(): AiLabScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Meridian Labs',
            'items' => [
                ['label' => 'Models', 'url' => '#model-cards'],
                ['label' => 'Benchmarks', 'url' => '#benchmarks'],
                ['label' => 'Research', 'url' => '#research-index'],
                ['label' => 'Playground', 'url' => '#playground'],
            ],
            'consultationUrl' => '#playground',
        ]);
    }

    private function hero(): AiLabScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Frontier models a lab can stand behind',
            'eyebrow' => 'AI Lab',
            'summary' => 'A technical research homepage for foundation models, benchmarks, research publications, and playground-led evaluation journeys.',
            'actions' => [
                ['label' => 'Open the playground', 'url' => '#playground'],
                ['label' => 'View benchmarks', 'url' => '#benchmarks'],
            ],
        ]);
    }

    private function footer(): AiLabScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Meridian Labs',
            'items' => [
                ['label' => 'Models', 'url' => '#model-cards'],
                ['label' => 'Benchmarks', 'url' => '#benchmarks'],
                ['label' => 'Research', 'url' => '#research-index'],
                ['label' => 'Playground', 'url' => '#playground'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'ai-lab-directory' => 'Theme AI Lab directory',
            'ai-lab-detail' => 'Theme AI Lab detail',
            'ai-lab-contact' => 'Theme AI Lab contact',
            'ai-lab-empty' => 'Theme AI Lab empty state',
            'ai-lab-not-found' => 'Theme AI Lab 404 state',
            'ai-lab-cta' => 'Theme AI Lab conversion CTA',
            'ai-lab-model-suite' => 'Theme AI Lab model suite',
            'ai-lab-research-library' => 'Theme AI Lab research library',
            'ai-lab-playground-preview' => 'Theme AI Lab playground preview',
            default => 'Theme AI Lab homepage',
        };
    }
}
