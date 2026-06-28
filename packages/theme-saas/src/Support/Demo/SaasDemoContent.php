<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Saas\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the SaaS theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's product-led renderers (features / proof /
 * logos / comparison / pricing / content-listing / demo-request / faq)
 * alongside the standard hero/cta — giving every surface a full, individual
 * product site rather than the shared five-section skeleton.
 *
 * Copy and section structure are mined verbatim from SaasScreenshotRenderer.
 */
final class SaasDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Launchdeck';

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
            title: self::BRAND . ' — Software that proves its value fast',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Software that proves its value fast',
                'A product-led landing homepage for features, proof, comparison, pricing, and demo-led conversion journeys.',
            ),
            renderData: [
                'summary' => 'A product-led platform that turns onboarding into activation and adoption.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection($media),
                    $this->featuresSection(),
                    $this->proofSection(),
                    $this->logosSection(),
                    $this->comparisonSection(),
                    $this->pricingSection(),
                    $this->ctaSection(),
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
            name: self::BRAND . ' Resources',
            title: 'Resources — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A product directory built to be evaluated',
                'Structured resource cards keep SaaS evaluation paths clear without the theme owning content records.',
            ),
            renderData: [
                'summary' => 'Structured resource cards keep SaaS evaluation paths clear without the theme owning content records.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->contentListingSection(
                        heading: 'A product directory built to be evaluated',
                        summary: 'Structured resource cards keep SaaS evaluation paths clear without the theme owning content records.',
                    ),
                    $this->featuresSection(),
                    $this->ctaSection(),
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
            name: self::BRAND . ' Product Story',
            title: 'Product story — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A product story that reads with momentum',
                'An article-style product view pairs narrative and proof so evaluators can engage with confidence.',
            ),
            renderData: [
                'summary' => 'An article-style product view pairs narrative and proof so evaluators can engage with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->contentListingSection(
                        heading: 'A product story that reads with momentum',
                        summary: 'An article-style product view pairs narrative and proof so evaluators can engage with confidence.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(),
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
            name: self::BRAND . ' Demo',
            title: 'Book a demo — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the product team through one focused path',
                'A non-submitting demo-request CTA proves the contact journey stays tied to product workflow.',
            ),
            renderData: [
                'summary' => 'A non-submitting demo-request CTA proves the contact journey stays tied to product workflow.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->demoRequestSection(
                        heading: 'Reach the product team through one focused path',
                        summary: 'A non-submitting demo-request CTA proves the contact journey stays tied to product workflow.',
                    ),
                    $this->faqSection(),
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
            title: 'Nothing published yet — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing published here yet',
                'An empty listing state stays premium and structured while the product prepares its content.',
            ),
            renderData: [
                'summary' => 'An empty listing state stays premium and structured while the product prepares its content.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing published here yet',
                        'summary' => 'An empty listing state stays premium and structured while the product prepares its content.',
                        'items' => [],
                    ],
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
                'A not-found page that routes evaluators back into the product features and demo paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back to the product.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        $media,
                        heading: 'This page took a different route',
                        eyebrow: '404',
                        summary: 'The link is broken or the page has moved. Head back to the product features, or book a guided demo.',
                    ),
                    $this->ctaSection(),
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
            name: self::BRAND . ' Enquire',
            title: 'Turn intent into a booked demo — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a booked demo',
                'A conversion-focused CTA stack keeps the path to trialling the product direct and premium.',
            ),
            renderData: [
                'summary' => 'A conversion-focused CTA stack keeps the path to trialling the product direct and premium.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->demoRequestSection(),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Turn intent into a booked demo',
                        summary: 'A conversion-focused CTA stack keeps the path to trialling the product direct and premium.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function heroSection(array $media, ?string $heading = null, ?string $eyebrow = null, ?string $summary = null): array
    {
        return [
            'type' => 'hero',
            'heading' => $heading ?? 'Software that proves its value fast',
            'eyebrow' => $eyebrow ?? 'SaaS',
            'summary' => $summary ?? 'A product-led landing homepage for features, proof, comparison, pricing, and demo-led conversion journeys.',
            'actions' => [
                ['label' => 'Book a demo', 'url' => '#demo'],
                ['label' => 'View features', 'url' => '#features'],
            ],
            'mediaUrl' => $media['hero'][0] ?? null,
            'mediaAlt' => 'Launchdeck product dashboard',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(): array
    {
        return [
            'type' => 'features',
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function proofSection(): array
    {
        return [
            'type' => 'proof',
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function logosSection(): array
    {
        return [
            'type' => 'logos',
            'heading' => 'Trusted by product-led teams',
            'items' => [
                ['name' => 'Northwind'],
                ['name' => 'Brightline'],
                ['name' => 'Cohort Labs'],
                ['name' => 'Meridian'],
                ['name' => 'Parallel'],
                ['name' => 'Outset'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function comparisonSection(): array
    {
        return [
            'type' => 'comparison',
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function pricingSection(): array
    {
        return [
            'type' => 'pricing',
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(?string $heading = null, ?string $summary = null): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading ?? 'See the product prove its value',
            'summary' => $summary ?? 'Book a guided demo and watch a workflow activate in real time.',
            'actions' => [
                ['label' => 'Book a demo', 'url' => '#demo'],
                ['label' => 'View features', 'url' => '#features', 'style' => 'secondary'],
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function demoRequestSection(?string $heading = null, ?string $summary = null): array
    {
        return [
            'type' => 'demo-request',
            'heading' => $heading ?? 'Book a guided product demo',
            'summary' => $summary ?? 'One focused path to see the workflow activate for your team.',
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
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function faqSection(): array
    {
        return [
            'type' => 'faq',
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
                ['label' => 'Features', 'url' => '#features'],
                ['label' => 'Pricing', 'url' => '#pricing'],
                ['label' => 'Comparison', 'url' => '#comparison'],
                ['label' => 'Demo', 'url' => '#demo'],
            ],
            'ctaLabel' => 'Book a demo',
            'ctaUrl' => '#demo',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
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
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
