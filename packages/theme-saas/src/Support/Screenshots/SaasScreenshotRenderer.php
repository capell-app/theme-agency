<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Saas\Support\Screenshots;

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\HtmlString;

final class SaasScreenshotRenderer
{
    private const string VIEW_PREFIX = 'capell-theme-saas::sections.';

    public function render(string $screen): View
    {
        $sections = $this->sectionsFor($screen);

        abort_if($sections === [], 404);

        $viewFactory = app(ViewFactory::class);

        $content = collect($sections)
            ->map(fn (SaasScreenshotSection $section): string => $viewFactory->make(
                self::VIEW_PREFIX . $section->key(),
                $section->toViewData(),
            )->render())
            ->implode("\n");

        $pageHtml = view('capell-theme-saas::page', [
            'brand' => new BrandProfileData(
                primaryColor: '#2563eb',
                accentColor: '#06b6d4',
                neutralColor: '#0f172a',
                headingFont: 'sora',
                bodyFont: 'inter',
                spacing: 'balanced',
                cardStyle: 'bordered',
                navigationStyle: 'prominent',
                layoutPresentation: 'structured',
                mediaTreatment: 'framed',
                radius: 'md',
                surfaceColor: '#f8fafc',
                foregroundColor: '#0f172a',
                headingScale: 'balanced',
                cardDensity: 'compact',
            ),
            'content' => new HtmlString($content),
            'page' => null,
            'themeKey' => 'saas',
        ])->render();

        return view('capell-theme-saas::screenshots.fixture', [
            'body' => new HtmlString($pageHtml),
            'title' => $this->titleFor($screen),
        ]);
    }

    /**
     * @return array<int, SaasScreenshotSection>
     */
    private function sectionsFor(string $screen): array
    {
        return match ($screen) {
            'theme-preset-selection-showing-saas',
            'theme-preview-url-output',
            'frontend-page-rendered-with-saas-theme',
            'saas-homepage-layout' => [
                $this->navigation(),
                $this->hero(),
                $this->section('features'),
                $this->section('proof'),
                $this->section('logos'),
                $this->section('comparison'),
                $this->section('pricing'),
                $this->section('cta'),
                $this->footer(),
            ],
            'saas-directory-layout' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'A product directory built to be evaluated',
                    'summary' => 'Structured resource cards keep SaaS evaluation paths clear without the theme owning content records.',
                ]),
                $this->section('features'),
                $this->section('cta'),
                $this->footer(),
            ],
            'saas-detail-layout' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'A product story that reads with momentum',
                    'summary' => 'An article-style product view pairs narrative and proof so evaluators can engage with confidence.',
                ]),
                $this->section('proof'),
                $this->section('cta'),
                $this->footer(),
            ],
            'saas-contact-layout' => [
                $this->navigation(),
                $this->section('demo-request', [
                    'heading' => 'Reach the product team through one focused path',
                    'summary' => 'A non-submitting demo-request CTA proves the contact journey stays tied to product workflow.',
                ]),
                $this->section('faq'),
                $this->footer(),
            ],
            'saas-empty-state-layout' => [
                $this->navigation(),
                $this->section('content-listing', [
                    'heading' => 'Nothing published here yet',
                    'summary' => 'An empty listing state stays premium and structured while the product prepares its content.',
                ]),
                $this->footer(),
            ],
            'saas-cta-layout' => [
                $this->navigation(),
                $this->section('demo-request'),
                $this->section('proof'),
                $this->section('cta', [
                    'heading' => 'Turn intent into a booked demo',
                    'summary' => 'A conversion-focused CTA stack keeps the path to trialling the product direct and premium.',
                ]),
                $this->footer(),
            ],
            default => [],
        };
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function section(string $sectionKey, array $data = []): SaasScreenshotSection
    {
        return new SaasScreenshotSection($sectionKey, [...$this->defaultDataFor($sectionKey), ...$data]);
    }

    /**
     * Representative sample data so every strict section view renders with
     * real-looking content and never iterates over null.
     *
     * @return array<string, mixed>
     */
    private function defaultDataFor(string $sectionKey): array
    {
        return match ($sectionKey) {
            'features' => [
                'heading' => 'Workflow that activates faster',
                'summary' => 'Every surface is built to move evaluators from signup to value in the first session.',
                'features' => [
                    [
                        'type' => 'Activation',
                        'icon' => 'rocket',
                        'title' => 'Guided onboarding flows',
                        'description' => 'Checklists and inline prompts walk new teams to their first workflow win.',
                        'metric' => '2x faster activation',
                    ],
                    [
                        'type' => 'Automation',
                        'icon' => 'bolt',
                        'title' => 'Triggers and playbooks',
                        'description' => 'Codify repeatable processes so the product runs the busywork for the team.',
                        'metric' => '40% less manual work',
                    ],
                    [
                        'type' => 'Insight',
                        'icon' => 'chart',
                        'title' => 'Live product analytics',
                        'description' => 'Usage signals surface adoption gaps before they become churn risks.',
                        'metric' => 'Real-time signals',
                    ],
                ],
            ],
            'proof' => [
                'heading' => 'Teams that prove value with Launchdeck',
                'summary' => 'Outcome metrics and operator quotes show the product earning its place in the stack.',
                'items' => [
                    [
                        'name' => 'Northwind Ops',
                        'metric' => '+38% activation',
                        'summary' => 'New workspaces reached their first automated workflow inside a week.',
                        'role' => 'Head of Operations',
                    ],
                    [
                        'name' => 'Brightline SaaS',
                        'quote' => 'Launchdeck turned our onboarding from a deck into a product.',
                        'summary' => 'Self-serve trials now convert without a sales touch.',
                        'role' => 'VP Product',
                    ],
                    [
                        'name' => 'Cohort Labs',
                        'metric' => '4.8/5 adoption',
                        'summary' => 'Usage analytics flagged adoption gaps before renewals.',
                        'role' => 'Customer Success Lead',
                    ],
                ],
            ],
            'logos' => [
                'heading' => 'Trusted by product-led teams',
                'items' => [
                    ['name' => 'Northwind'],
                    ['name' => 'Brightline'],
                    ['name' => 'Cohort Labs'],
                    ['name' => 'Meridian'],
                    ['name' => 'Parallel'],
                    ['name' => 'Outset'],
                ],
            ],
            'comparison' => [
                'heading' => 'Why teams switch to Launchdeck',
                'summary' => 'A product-led stack replaces stitched-together onboarding and analytics tools.',
                'items' => [
                    [
                        'title' => 'Guided activation',
                        'description' => 'Built-in onboarding flows instead of static docs and email sequences.',
                    ],
                    [
                        'title' => 'Workflow automation',
                        'description' => 'Native triggers and playbooks instead of brittle third-party glue.',
                    ],
                    [
                        'title' => 'Adoption analytics',
                        'description' => 'Product usage signals instead of disconnected dashboards.',
                    ],
                ],
            ],
            'pricing' => [
                'heading' => 'Pricing that scales with adoption',
                'summary' => 'Start free, then grow into automation and analytics as the team activates.',
                'items' => [
                    [
                        'type' => 'Starter',
                        'title' => 'Launch',
                        'price' => '$0',
                        'period' => '/mo',
                        'summary' => 'For small teams validating their first workflow.',
                        'cta' => 'Start free',
                        'url' => '#demo',
                        'features' => [
                            'Guided onboarding' => true,
                            'Up to 3 workflows' => true,
                            'Adoption analytics' => false,
                        ],
                    ],
                    [
                        'type' => 'Growth',
                        'title' => 'Scale',
                        'price' => '$49',
                        'period' => '/mo',
                        'popular' => true,
                        'summary' => 'For product-led teams automating activation.',
                        'cta' => 'Book a demo',
                        'url' => '#demo',
                        'features' => [
                            'Guided onboarding' => true,
                            'Up to 3 workflows' => true,
                            'Adoption analytics' => true,
                        ],
                    ],
                    [
                        'type' => 'Enterprise',
                        'title' => 'Command',
                        'price' => 'Custom',
                        'period' => '',
                        'summary' => 'For organisations standardising on the platform.',
                        'cta' => 'Talk to sales',
                        'url' => '#demo',
                        'features' => [
                            'Guided onboarding' => true,
                            'Up to 3 workflows' => true,
                            'Adoption analytics' => true,
                        ],
                    ],
                ],
            ],
            'cta' => [
                'heading' => 'See the product prove its value',
                'summary' => 'Book a guided demo and watch a workflow activate in real time.',
                'actions' => [
                    ['label' => 'Book a demo', 'url' => '#demo'],
                    ['label' => 'View features', 'url' => '#features', 'style' => 'secondary'],
                ],
            ],
            'content-listing' => [
                'heading' => 'Product resources and evaluation paths',
                'summary' => 'Structured resource cards keep SaaS evaluation paths clear.',
                'items' => [
                    [
                        'type' => 'Guide',
                        'title' => 'Activation playbook',
                        'summary' => 'How leading teams reach their first workflow win in week one.',
                        'url' => '#features',
                    ],
                    [
                        'type' => 'Story',
                        'title' => 'Northwind Ops case study',
                        'summary' => 'Cutting manual onboarding work by 40% with automated flows.',
                        'url' => '#proof',
                    ],
                    [
                        'type' => 'Reference',
                        'title' => 'Pricing and plans',
                        'summary' => 'Choose the plan that matches your adoption stage.',
                        'url' => '#pricing',
                    ],
                ],
            ],
            'demo-request' => [
                'heading' => 'Book a guided product demo',
                'summary' => 'One focused path to see the workflow activate for your team.',
                'items' => [
                    [
                        'type' => 'Demo',
                        'title' => 'Live walkthrough',
                        'summary' => 'A 30-minute session mapped to your activation goals.',
                    ],
                    [
                        'type' => 'Trial',
                        'title' => 'Hands-on workspace',
                        'summary' => 'A seeded environment to explore workflows yourself.',
                    ],
                ],
            ],
            'faq' => [
                'heading' => 'Questions before you book',
                'summary' => 'Quick answers on activation, pricing, and rollout.',
                'items' => [
                    [
                        'question' => 'How fast can a team activate?',
                        'answer' => 'Most teams reach their first automated workflow within the first session.',
                    ],
                    [
                        'question' => 'Is there a free plan?',
                        'answer' => 'Yes, the Launch plan is free for small teams validating a first workflow.',
                    ],
                    [
                        'question' => 'Do you offer guided onboarding?',
                        'answer' => 'Every plan includes guided onboarding flows and inline activation prompts.',
                    ],
                ],
            ],
            default => [],
        };
    }

    private function navigation(): SaasScreenshotSection
    {
        return $this->section('navigation', [
            'brandName' => 'Launchdeck',
            'items' => [
                ['label' => 'Features', 'url' => '#features'],
                ['label' => 'Pricing', 'url' => '#pricing'],
                ['label' => 'Comparison', 'url' => '#comparison'],
                ['label' => 'Demo', 'url' => '#demo'],
            ],
            'consultationUrl' => '#demo',
        ]);
    }

    private function hero(): SaasScreenshotSection
    {
        return $this->section('hero', [
            'heading' => 'Software that proves its value fast',
            'eyebrow' => 'SaaS',
            'summary' => 'A product-led landing homepage for features, proof, comparison, pricing, and demo-led conversion journeys.',
            'actions' => [
                ['label' => 'Book a demo', 'url' => '#demo'],
                ['label' => 'View features', 'url' => '#features'],
            ],
        ]);
    }

    private function footer(): SaasScreenshotSection
    {
        return $this->section('footer', [
            'brandName' => 'Launchdeck',
            'summary' => 'A product-led platform that turns onboarding into activation and adoption.',
            'columns' => [
                [
                    'heading' => 'Product',
                    'links' => [
                        ['label' => 'Features', 'url' => '#features'],
                        ['label' => 'Pricing', 'url' => '#pricing'],
                        ['label' => 'Comparison', 'url' => '#comparison'],
                    ],
                ],
                [
                    'heading' => 'Resources',
                    'links' => [
                        ['label' => 'Activation playbook', 'url' => '#features'],
                        ['label' => 'Case studies', 'url' => '#proof'],
                        ['label' => 'Documentation', 'url' => '#features'],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'links' => [
                        ['label' => 'Book a demo', 'url' => '#demo'],
                        ['label' => 'Contact', 'url' => '#demo'],
                    ],
                ],
            ],
        ]);
    }

    private function titleFor(string $screen): string
    {
        return match ($screen) {
            'saas-directory-layout' => 'Theme SaaS directory',
            'saas-detail-layout' => 'Theme SaaS detail',
            'saas-contact-layout' => 'Theme SaaS contact',
            'saas-empty-state-layout' => 'Theme SaaS empty state',
            'saas-cta-layout' => 'Theme SaaS conversion CTA',
            default => 'Theme SaaS homepage',
        };
    }
}
