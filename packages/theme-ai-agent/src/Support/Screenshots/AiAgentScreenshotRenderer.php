<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AiAgent\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class AiAgentScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-ai-agent::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (AiAgentScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-ai-agent::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#0ea5e9',
                accentColor: '#22c55e',
                neutralColor: '#0f172a',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'elevated',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'lg',
                surfaceColor: '#f8fafc',
                foregroundColor: '#0f172a',
                headingScale: 'balanced',
                cardDensity: 'comfortable',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'ai-agent',
        ])->render();

        return view('capell-theme-ai-agent::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, AiAgentScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'ai-agent-homepage' => [
                $this->navigation(),
                $this->hero(),
                $this->section('outcome-metrics'),
                $this->section('agent-in-action'),
                $this->section('features'),
                $this->section('integrations-grid'),
                $this->section('use-cases'),
                $this->section('roi-calculator'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'ai-agent-directory' => [
                $this->navigation(),
                $this->section('use-cases', [
                    'heading' => 'A directory of automations built to be scanned',
                    'summary' => 'Structured use-case cards keep the agent catalogue legible without the theme owning workflow records.',
                ]),
                $this->section('content-listing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'ai-agent-detail' => [
                $this->navigation(),
                $this->section('agent-in-action', [
                    'heading' => 'An automation profile that reads with confidence',
                    'summary' => 'A single agent view pairs the action loop with proof so buyers can evaluate outcomes directly.',
                ]),
                $this->section('outcome-metrics'),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'ai-agent-contact' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'Reach the team through one confident path',
                    'summary' => 'A non-submitting contact CTA proves the journey feels like part of the product experience.',
                ]),
                $this->section('features'),
                $this->footer(),
            ],
            'ai-agent-empty' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays premium and structured while the product prepares its content.',
                ]),
                $this->footer(),
            ],
            'ai-agent-not-found' => [
                $this->navigation(),
                $this->section('cta', [
                    'heading' => 'That page could not be found',
                    'summary' => 'A 404 state keeps the product credible and routes visitors back into the agent journey.',
                ]),
                $this->footer(),
            ],
            'ai-agent-cta' => [
                $this->navigation(),
                $this->section('roi-calculator'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a booked deployment',
                    'summary' => 'A conversion-focused CTA stack keeps the path to deploying the agent direct and premium.',
                ]),
                $this->footer(),
            ],
            'ai-agent-use-cases' => [
                $this->navigation(),
                $this->section('use-cases', [
                    'heading' => 'Use cases built to be scanned and trusted',
                    'summary' => 'Structured use-case groupings keep automations legible without the theme owning workflow records.',
                ]),
                $this->section('agent-in-action'),
                $this->section('cta'),
                $this->footer(),
            ],
            'ai-agent-integrations' => [
                $this->navigation(),
                $this->section('integrations-grid', [
                    'heading' => 'Integrations built to be scanned and trusted',
                    'summary' => 'A structured integrations grid keeps the connected toolset legible without the theme owning system records.',
                ]),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'ai-agent-roi' => [
                $this->navigation(),
                $this->section('roi-calculator', [
                    'heading' => 'Model the return before you deploy',
                    'summary' => 'A non-submitting ROI calculator proves the value journey feels like part of the product experience.',
                ]),
                $this->section('outcome-metrics'),
                $this->section('cta'),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): AiAgentScreenshotSection
    {
        return new AiAgentScreenshotSection($sectionKey, $data);
    }

    private function navigation(): AiAgentScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Aria',
            'items' => [
                ['label' => 'Use cases', 'url' => '#use-cases'],
                ['label' => 'Integrations', 'url' => '#integrations'],
                ['label' => 'ROI', 'url' => '#roi'],
                ['label' => 'Proof', 'url' => '#proof'],
            ],
            'consultationUrl' => '#cta',
        ]);
    }

    private function hero(): AiAgentScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'An AI agent your team can stand behind',
            'eyebrow' => 'AI Agent',
            'summary' => 'An outcome-led homepage for autonomous support and ops automation: the action loop, integrations, ROI, and proof.',
            'actions' => [
                ['label' => 'Book a deployment', 'url' => '#cta'],
                ['label' => 'View use cases', 'url' => '#use-cases'],
            ],
        ]);
    }

    private function footer(): AiAgentScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Aria',
            'items' => [
                ['label' => 'Use cases', 'url' => '#use-cases'],
                ['label' => 'Integrations', 'url' => '#integrations'],
                ['label' => 'ROI', 'url' => '#roi'],
                ['label' => 'Proof', 'url' => '#proof'],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'ai-agent-directory' => 'Theme AI Agent directory',
            'ai-agent-detail' => 'Theme AI Agent detail',
            'ai-agent-contact' => 'Theme AI Agent contact',
            'ai-agent-empty' => 'Theme AI Agent empty state',
            'ai-agent-not-found' => 'Theme AI Agent 404 state',
            'ai-agent-cta' => 'Theme AI Agent conversion CTA',
            'ai-agent-use-cases' => 'Theme AI Agent use cases',
            'ai-agent-integrations' => 'Theme AI Agent integrations',
            'ai-agent-roi' => 'Theme AI Agent ROI calculator',
            default => 'Theme AI Agent homepage',
        };
    }
}
