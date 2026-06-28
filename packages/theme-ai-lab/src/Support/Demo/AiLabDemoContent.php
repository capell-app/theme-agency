<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AiLab\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the AI Lab theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (model-cards / benchmarks
 * / research-index / playground) alongside the standard hero/features/proof/cta —
 * giving every surface a full, individual frontier-lab site rather than the
 * shared five-section skeleton.
 */
final class AiLabDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Meridian Labs';

    /**
     * @return array<int, ThemeDemoPageDefinition>
     */
    public function definitions(string $themeKey, string $themeName, string $baseUrl): array
    {
        $media = ThemeDemoMedia::groupedForTheme($themeKey);

        return [
            $this->homepage($themeKey, $media),
            $this->directory($themeKey, $media),
            $this->detail($themeKey, $media),
            $this->contact($themeKey, $media),
            $this->empty($themeKey, $media),
            $this->notFound($themeKey, $media),
            $this->cta($themeKey, $media),
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function homepage(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'homepage',
            name: self::BRAND . ' Home',
            title: self::BRAND . ' — Frontier Foundation Models',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A frontier AI research lab',
                'Meridian Labs builds and ships frontier foundation models, publishes the research behind them, and runs the evaluations that prove they hold up.',
            ),
            renderData: [
                'summary' => 'Meridian Labs is a frontier research lab building foundation models for long-horizon reasoning, tool use, and grounded answers — with the benchmarks and research to back them.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Frontier foundation models',
                        'heading' => 'Models a research lab can stand behind',
                        'summary' => 'Meridian M3 is a multimodal foundation model with a 200k-token context window, built for long-horizon reasoning, tool use, and grounded answers. Evaluate it in the playground or against your own benchmark suite.',
                        'actions' => [
                            ['label' => 'Open the playground', 'url' => '#playground', 'style' => 'primary'],
                            ['label' => 'View benchmarks', 'url' => '#benchmarks', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'Meridian Labs research interface',
                    ],
                    $this->modelCardsSection(
                        heading: 'The Meridian model family',
                        summary: 'Three models across the cost-capability frontier. Every model shares the same alignment stack and tool-use interface.',
                    ),
                    $this->benchmarksSection(
                        heading: 'Benchmarks, reported in full',
                        summary: 'Headline evaluation results across reasoning, code, and multimodal suites — with the methodology published alongside every number.',
                    ),
                    $this->featuresSection(
                        heading: 'Built for production reasoning',
                        summary: 'The capabilities teams ask for first when they move a model from a demo into a real workload.',
                    ),
                    $this->researchIndexSection(
                        heading: 'Research from the lab',
                        summary: 'Peer-reviewed papers and technical reports from the teams behind the models.',
                    ),
                    $this->playgroundSection(
                        heading: 'Try the model in the playground',
                        summary: 'Send a prompt, inspect the trace, and compare models side by side — no signup to run the first evaluation.',
                    ),
                    $this->proofSection(
                        heading: 'Trusted in production',
                        summary: 'Where Meridian models already run at scale.',
                    ),
                    $this->ctaSection(
                        heading: 'Run your own evaluation',
                        summary: 'Bring your benchmark suite or start in the playground. Research access is granted within one working day.',
                    ),
                ],
            ],
            type: PageTypeEnum::Home,
            layout: LayoutEnum::Home,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function directory(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'directory',
            name: self::BRAND . ' Research',
            title: 'Research Index — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The Meridian research index',
                'Every paper, technical report, and model card the lab has published — structured to be scanned, cited, and reproduced.',
            ),
            renderData: [
                'summary' => 'A structured index of the research behind the models: papers, technical reports, and reproducible evaluation methodology.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Research index',
                        'heading' => 'The research behind the models',
                        'summary' => 'Browse the lab archive by track — alignment, reasoning, multimodal, and systems. Each entry links the paper, the model card, and the evaluation harness.',
                        'actions' => [
                            ['label' => 'Open the playground', 'url' => '#playground', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Research papers from the lab',
                    ],
                    $this->researchIndexSection(
                        heading: 'Featured research',
                        summary: 'The work the lab keeps returning to.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        summary: 'Technical reports, ablations, and reproducibility notes.',
                    ),
                    $this->ctaSection(
                        heading: 'Reproduce a result',
                        summary: 'Every published number ships with an open evaluation harness. Request research access and run it yourself.',
                    ),
                ],
            ],
            layout: LayoutEnum::Results,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function detail(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Model Profile',
            title: 'Meridian M3 — Model Profile — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Meridian M3 — the flagship reasoning model',
                'A full model profile for Meridian M3: capabilities, context window, tool-use interface, and the benchmark results that define it.',
            ),
            renderData: [
                'summary' => 'A single model profile that pairs capabilities and benchmarks so evaluators can engage Meridian M3 with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Model profile',
                        'heading' => 'Meridian M3 — the flagship reasoning model',
                        'summary' => 'A 200k-token multimodal model tuned for long-horizon planning and reliable tool use. This profile pairs every claimed capability with the benchmark that backs it.',
                        'actions' => [
                            ['label' => 'Evaluate M3', 'url' => '#playground', 'style' => 'primary'],
                            ['label' => 'View benchmarks', 'url' => '#benchmarks', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Meridian M3 model architecture',
                    ],
                    $this->modelCardSpecSection(),
                    $this->benchmarksSection(
                        heading: 'M3 benchmark results',
                        summary: 'How Meridian M3 scores across the public evaluation suites, with methodology linked for each.',
                    ),
                    $this->proofSection(
                        heading: 'How teams run M3',
                        summary: 'Production workloads built on the flagship model.',
                    ),
                    $this->ctaSection(
                        heading: 'Put M3 through your own tests',
                        summary: 'Send a prompt in the playground or wire M3 into your evaluation harness. Access lands within one working day.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function contact(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'contact',
            name: self::BRAND . ' Access',
            title: 'Request research access — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Request research access',
                'Tell us what you want to evaluate. The lab grants API keys and benchmark access directly — no sales funnel between you and the model.',
            ),
            renderData: [
                'summary' => 'Request research access to the Meridian models. We grant API keys and evaluation access directly, usually within one working day.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Access',
                        'heading' => 'Request research access',
                        'summary' => 'The lab works with researchers, infrastructure teams, and product teams running real workloads. Email research@meridianlabs.example or use the channels below — we reply within one working day.',
                        'actions' => [
                            ['label' => 'Email the lab', 'url' => 'mailto:research@meridianlabs.example', 'style' => 'primary'],
                            ['label' => 'Open the playground', 'url' => '#playground', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Meridian Labs research team',
                    ],
                    $this->featuresSection(
                        heading: 'What access gives you',
                        summary: 'Everything you need to evaluate the models seriously, from day one.',
                    ),
                    $this->playgroundSection(
                        heading: 'Start before you wait',
                        summary: 'Run the first evaluations in the open playground while your research key is provisioned.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Book a 30-minute technical call with a researcher and we will map the fastest path to the numbers you need.',
                    ),
                ],
            ],
            layout: LayoutEnum::System,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function empty(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'empty',
            name: self::BRAND . ' No Results',
            title: 'Nothing published here yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing published here yet',
                'A graceful empty state for a filtered research index with no matching publications.',
            ),
            renderData: [
                'summary' => 'No research matches that filter yet — but the lab can still point you toward the right model or benchmark.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Research index',
                        'heading' => 'Nothing published in this track — yet',
                        'summary' => 'The lab has not posted research under this filter. Clear it to see the full archive, or jump straight to the models and benchmarks.',
                        'actions' => [
                            ['label' => 'View all research', 'url' => '#research-index', 'style' => 'primary'],
                            ['label' => 'View benchmarks', 'url' => '#benchmarks', 'style' => 'secondary'],
                        ],
                    ],
                    $this->contentListingSection(
                        heading: 'While you are here',
                        summary: 'Recent reports the lab has published across every track.',
                    ),
                    $this->modelCardsSection(
                        heading: 'Explore the models instead',
                        summary: 'The three models at the centre of the lab\'s research.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific result?',
                        summary: 'Tell us the benchmark or capability and we will send the relevant paper and model card.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function notFound(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'not-found',
            name: self::BRAND . ' 404',
            title: 'Page not found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'Page not found',
                'A not-found page that routes visitors back into the lab\'s models, benchmarks, and research.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the lab.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That result could not be reproduced',
                        'summary' => 'The link is broken or the page has moved. Head back to the models and benchmarks, or open the playground.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'Open the playground', 'url' => '#playground', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us which model or paper you needed and we will point you to it.',
                    ),
                ],
            ],
            type: PageTypeEnum::NotFound,
            layout: LayoutEnum::System,
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function cta(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'cta',
            name: self::BRAND . ' Evaluate',
            title: 'Evaluate the model — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a model evaluation',
                'A focused conversion page that moves a visitor from interest to a running evaluation in the playground.',
            ),
            renderData: [
                'summary' => 'Turn intent into a model evaluation. Start in the playground or wire Meridian into your own benchmark suite today.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Evaluate the model',
                        'heading' => 'Turn intent into a model evaluation',
                        'summary' => 'Whether you run a single prompt or a full benchmark sweep, the path from interest to results is the same: open, send, inspect, compare.',
                        'actions' => [
                            ['label' => 'Open the playground', 'url' => '#playground', 'style' => 'primary'],
                            ['label' => 'Email the lab', 'url' => 'mailto:research@meridianlabs.example', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Meridian Labs evaluation playground',
                    ],
                    $this->playgroundSection(
                        heading: 'Run the first evaluation now',
                        summary: 'No signup to send your first prompt. Inspect the reasoning trace and compare models side by side.',
                    ),
                    $this->proofSection(
                        heading: 'Why teams choose Meridian',
                        summary: 'The numbers behind the models.',
                    ),
                    $this->ctaSection(
                        heading: 'One prompt away',
                        summary: 'Send your first evaluation now, or request a research key and we will reply within one working day.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function modelCardsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'model-cards',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Meridian M3',
                    'summary' => 'The flagship. 200k-token context, multimodal input, and best-in-class long-horizon reasoning for agents and tool use.',
                ],
                [
                    'title' => 'Meridian M3 Fast',
                    'summary' => 'The same alignment stack at a fraction of the latency and cost — tuned for high-volume classification, extraction, and routing.',
                ],
                [
                    'title' => 'Meridian Vision',
                    'summary' => 'A multimodal model for document understanding, chart and diagram reasoning, and grounded visual question answering.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function modelCardSpecSection(): array
    {
        return [
            'type' => 'model-cards',
            'heading' => 'M3 at a glance',
            'summary' => 'The specification evaluators check first, before they run a single prompt.',
            'items' => [
                [
                    'title' => '200k-token context',
                    'summary' => 'Reason over entire codebases, contracts, or research corpora in a single request without retrieval gymnastics.',
                ],
                [
                    'title' => 'Native tool use',
                    'summary' => 'Structured function calling with parallel tool execution and reliable schema adherence for agentic workloads.',
                ],
                [
                    'title' => 'Multimodal input',
                    'summary' => 'Text, images, and documents in one prompt, with grounded citations back to the source spans.',
                ],
                [
                    'title' => 'Aligned by default',
                    'summary' => 'A published alignment stack with documented refusal behaviour and a model card for every release.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function benchmarksSection(string $heading, string $summary): array
    {
        return [
            'type' => 'benchmarks',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Reasoning — 88.4%',
                    'summary' => 'Graduate-level multi-step reasoning suite. Methodology and per-task breakdown published in the M3 technical report.',
                ],
                [
                    'title' => 'Code — 81.2%',
                    'summary' => 'Function-completion and repository-level editing benchmark, scored pass@1 with the harness available to reproduce.',
                ],
                [
                    'title' => 'Tool use — 92.7%',
                    'summary' => 'Agentic tool-calling suite measuring schema adherence and recovery from failed calls across long trajectories.',
                ],
                [
                    'title' => 'Multimodal — 79.5%',
                    'summary' => 'Document and chart understanding benchmark with grounded-citation scoring against the source spans.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(string $heading, string $summary): array
    {
        return [
            'type' => 'features',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Grounded answers',
                    'summary' => 'Responses cite the spans they drew from, so reviewers can verify a claim instead of trusting it.',
                ],
                [
                    'title' => 'Reproducible evals',
                    'summary' => 'Every published benchmark ships with an open harness and a fixed seed, so your numbers match ours.',
                ],
                [
                    'title' => 'Stable JSON output',
                    'summary' => 'Constrained decoding holds the schema even on the long tail, so downstream parsers never break.',
                ],
                [
                    'title' => 'Predictable latency',
                    'summary' => 'Per-token streaming with published p50 and p99 latency, so you can budget the request before you send it.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function researchIndexSection(string $heading, string $summary): array
    {
        return [
            'type' => 'research-index',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Long-horizon planning in foundation models',
                    'summary' => 'How M3 maintains a coherent plan across hundreds of tool calls, and the failure modes that remain.',
                ],
                [
                    'title' => 'Grounded answering with span-level citations',
                    'summary' => 'A training recipe that ties every claim to a retrievable source, with an evaluation for citation faithfulness.',
                ],
                [
                    'title' => 'An open harness for agentic tool use',
                    'summary' => 'The benchmark and scoring code behind the lab\'s reported tool-use numbers, released for reproduction.',
                ],
                [
                    'title' => 'Alignment stack for the Meridian family',
                    'summary' => 'A technical report on the refusal behaviour, red-team coverage, and model cards shipped with every release.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function playgroundSection(string $heading, string $summary): array
    {
        return [
            'type' => 'playground',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Send a prompt',
                    'summary' => 'Paste a real task and stream the response token by token, with system-prompt and temperature controls.',
                ],
                [
                    'title' => 'Inspect the trace',
                    'summary' => 'Expand every tool call, retrieval, and intermediate step to see exactly how the model reached its answer.',
                ],
                [
                    'title' => 'Compare side by side',
                    'summary' => 'Run M3, M3 Fast, and Vision on the same prompt and diff the outputs, latency, and token cost.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary): array
    {
        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Scaling laws for tool-augmented reasoning',
                    'summary' => 'A technical report on how reasoning accuracy scales with available tools rather than raw parameter count.',
                ],
                [
                    'title' => 'Ablations on the M3 alignment stack',
                    'summary' => 'What each stage of post-training contributes to refusal accuracy and helpfulness, measured in isolation.',
                ],
                [
                    'title' => 'Reproducibility notes for the benchmark suite',
                    'summary' => 'Exact seeds, prompts, and harness versions for every headline number the lab reports.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => '200k tokens',
                    'summary' => 'Context window — reason over whole codebases and corpora in a single request.',
                ],
                [
                    'title' => '1 working day',
                    'summary' => 'Typical turnaround on a research access request, granted by the team directly.',
                ],
                [
                    'title' => 'Open harness',
                    'summary' => 'Every published benchmark number ships with the code to reproduce it.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Open the playground', 'url' => '#playground', 'style' => 'primary'],
                ['label' => 'View benchmarks', 'url' => '#benchmarks', 'style' => 'secondary'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brandName' => self::BRAND,
            'items' => [
                ['label' => 'Models', 'url' => '#model-cards'],
                ['label' => 'Benchmarks', 'url' => '#benchmarks'],
                ['label' => 'Research', 'url' => '#research-index'],
                ['label' => 'Playground', 'url' => '#playground'],
                ['label' => 'Access', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Open the playground',
            'ctaUrl' => '#playground',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A frontier AI research lab building foundation models for reasoning, tool use, and grounded answers.',
            'columns' => [
                [
                    'heading' => 'Models',
                    'links' => [
                        ['label' => 'Meridian M3', 'url' => '#model-cards'],
                        ['label' => 'Meridian M3 Fast', 'url' => '#model-cards'],
                        ['label' => 'Meridian Vision', 'url' => '#model-cards'],
                        ['label' => 'Benchmarks', 'url' => '#benchmarks'],
                    ],
                ],
                [
                    'heading' => 'Research',
                    'links' => [
                        ['label' => 'Research index', 'url' => '#research-index'],
                        ['label' => 'Technical reports', 'url' => '#research-index'],
                        ['label' => 'Evaluation harness', 'url' => '#benchmarks'],
                        ['label' => 'Model cards', 'url' => '#model-cards'],
                    ],
                ],
                [
                    'heading' => 'Access',
                    'links' => [
                        ['label' => 'Playground', 'url' => '#playground'],
                        ['label' => 'Request research access', 'url' => '#contact'],
                        ['label' => 'research@meridianlabs.example', 'url' => 'mailto:research@meridianlabs.example'],
                    ],
                ],
            ],
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
