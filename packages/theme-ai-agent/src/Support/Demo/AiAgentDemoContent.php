<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\AiAgent\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the AI Agent theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (outcome-metrics /
 * agent-in-action / integrations-grid / use-cases / roi-calculator) alongside
 * the standard hero/features/proof/cta — giving every surface a full,
 * individual autonomous-agent product site rather than the shared
 * five-section skeleton.
 */
final class AiAgentDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Aria';

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
            title: self::BRAND . ' — The AI Agent for Support and Operations',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'An AI agent your team can stand behind',
                'Aria resolves support tickets and runs back-office operations end to end — connected to your tools, measured on outcomes, and accountable to a human when it matters.',
            ),
            renderData: [
                'summary' => 'Aria is an autonomous AI agent that resolves support and operations work end to end — wired into your existing tools, measured on resolution rate and time saved, with a human in the loop for the calls that need one.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Autonomous support and operations',
                        'heading' => 'An AI agent your team can stand behind',
                        'summary' => 'Aria reads the ticket, gathers context across your tools, takes the action, and closes the loop — autonomously when it is confident, with a clean handoff to a teammate when it is not. Deploy it on your busiest queue this week.',
                        'actions' => [
                            ['label' => 'Book a deployment', 'url' => '#cta', 'style' => 'primary'],
                            ['label' => 'See it in action', 'url' => '#agent-in-action', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'The Aria agent console resolving a support queue',
                    ],
                    $this->outcomeMetricsSection(
                        heading: 'Outcomes, not activity',
                        summary: 'What changes in the first ninety days of running Aria on a live queue — measured against your own baseline, not ours.',
                    ),
                    $this->agentInActionSection(
                        heading: 'Watch the agent work the loop',
                        summary: 'Every resolution follows the same trace: understand the request, gather context, take the action, confirm the outcome. Nothing happens off-screen.',
                    ),
                    $this->featuresSection(
                        heading: 'Built to be trusted in production',
                        summary: 'The controls operations leaders ask for before they let an agent touch a live customer.',
                    ),
                    $this->integrationsGridSection(
                        heading: 'Wired into the tools you already run',
                        summary: 'Aria acts inside your stack — reading and writing the same systems your team does, with scoped permissions on every connection.',
                    ),
                    $this->useCasesSection(
                        heading: 'Where teams deploy Aria first',
                        summary: 'The highest-volume, most repetitive queues — the work that burns out a team and is perfect for an agent to own.',
                    ),
                    $this->roiCalculatorSection(
                        heading: 'Model the return before you deploy',
                        summary: 'Plug in your ticket volume and handle time. The math behind a typical first-queue deployment, laid out line by line.',
                    ),
                    $this->proofSection(
                        heading: 'Proof from live deployments',
                        summary: 'What teams running Aria on production queues are seeing today.',
                    ),
                    $this->ctaSection(
                        heading: 'Deploy Aria on your busiest queue',
                        summary: 'Pick one high-volume queue and we will have Aria resolving live tickets within a week — shadow mode first, autonomous when you sign off.',
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
            name: self::BRAND . ' Use Cases',
            title: 'Use-Case Library — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The Aria use-case library',
                'Every workflow Aria is built to own — support, operations, finance, and IT — structured so you can find the queue that maps to your team and deploy it.',
            ),
            renderData: [
                'summary' => 'A structured library of the workflows Aria can own end to end, grouped by team so you can find the queue that maps to yours and deploy it first.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Use-case library',
                        'heading' => 'Find the workflow Aria should own first',
                        'summary' => 'Browse by team — support, operations, finance, IT. Each use case links the action loop, the integrations it needs, and the outcome metric it moves.',
                        'actions' => [
                            ['label' => 'Book a deployment', 'url' => '#cta', 'style' => 'primary'],
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'The Aria use-case library',
                    ],
                    $this->useCasesSection(
                        heading: 'Workflows Aria owns end to end',
                        summary: 'The queues teams hand to Aria first, with the outcome each one moves.',
                    ),
                    $this->contentListingSection(
                        heading: 'More automations in the library',
                        summary: 'Lower-volume workflows that Aria handles alongside the headline queues.',
                    ),
                    $this->ctaSection(
                        heading: 'Map a use case to your team',
                        summary: 'Tell us your highest-volume queue and we will scope an Aria deployment around it.',
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
            name: self::BRAND . ' Agent Profile',
            title: 'Tier-1 Support Resolution — Agent Profile — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Tier-1 support resolution — the agent profile',
                'A full profile for the support-resolution agent: the action loop it runs, the systems it touches, and the resolution and time-saved numbers it moves on a live queue.',
            ),
            renderData: [
                'summary' => 'A single agent profile that pairs the action loop with outcome metrics so an operations leader can evaluate a tier-1 support deployment with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Agent profile',
                        'heading' => 'Tier-1 support resolution, owned end to end',
                        'summary' => 'This profile pairs every step of the resolution loop with the integration it needs and the metric it moves — so you can judge the deployment on outcomes, not a demo.',
                        'actions' => [
                            ['label' => 'Deploy this agent', 'url' => '#cta', 'style' => 'primary'],
                            ['label' => 'See the action loop', 'url' => '#agent-in-action', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'The tier-1 support resolution agent profile',
                    ],
                    $this->agentInActionSection(
                        heading: 'The resolution loop, step by step',
                        summary: 'How the support agent moves a ticket from open to resolved, and where it pauses for a human.',
                    ),
                    $this->outcomeMetricsSection(
                        heading: 'What this agent moves on a live queue',
                        summary: 'Measured against the team baseline in the first ninety days of autonomous operation.',
                    ),
                    $this->integrationsGridSection(
                        heading: 'Systems this agent touches',
                        summary: 'The connected tools the support agent reads from and writes to, each with scoped permissions.',
                    ),
                    $this->proofSection(
                        heading: 'Teams already running this agent',
                        summary: 'Live support queues resolved by the tier-1 agent today.',
                    ),
                    $this->ctaSection(
                        heading: 'Put this agent on your support queue',
                        summary: 'Start in shadow mode on a slice of live tickets, then switch to autonomous once the resolution rate clears your bar.',
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
            name: self::BRAND . ' Book a Deployment',
            title: 'Book a deployment — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Book an Aria deployment',
                'Tell us your busiest queue. A deployment engineer scopes the agent, wires the integrations, and gets Aria resolving live tickets — usually within a week.',
            ),
            renderData: [
                'summary' => 'Book a deployment for your busiest queue. A deployment engineer scopes the agent and wires the integrations directly — Aria is usually resolving live tickets within a week.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Book a deployment',
                        'heading' => 'Get Aria on a live queue this week',
                        'summary' => 'Email deploy@aria.example or use the channels below. A deployment engineer — not a salesperson — replies within one working day and scopes the first queue with you.',
                        'actions' => [
                            ['label' => 'Email the deployment team', 'url' => 'mailto:deploy@aria.example', 'style' => 'primary'],
                            ['label' => 'See it in action', 'url' => '#agent-in-action', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Aria deployment team',
                    ],
                    $this->featuresSection(
                        heading: 'What a deployment includes',
                        summary: 'Everything that ships in the first week, from scoping the queue to the first autonomous resolution.',
                    ),
                    $this->integrationsGridSection(
                        heading: 'Bring your stack',
                        summary: 'The systems we wire Aria into during onboarding — connected with scoped permissions before a single ticket is touched.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through first?',
                        summary: 'Book a 30-minute call with a deployment engineer and we will map the fastest path from your busiest queue to an autonomous agent.',
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
            title: 'No use cases match that filter — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No use cases match that filter',
                'A graceful empty state for a filtered use-case library with no matching workflows.',
            ),
            renderData: [
                'summary' => 'No use cases match that filter yet — but Aria can still point you toward the right workflow or queue to start with.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Use-case library',
                        'heading' => 'No workflows match that filter — yet',
                        'summary' => 'Nothing in the library matches the team or queue you selected. Clear the filter to see every workflow, or jump straight to the use cases teams deploy first.',
                        'actions' => [
                            ['label' => 'View all use cases', 'url' => '#use-cases', 'style' => 'primary'],
                            ['label' => 'Book a deployment', 'url' => '#cta', 'style' => 'secondary'],
                        ],
                    ],
                    $this->useCasesSection(
                        heading: 'Workflows teams deploy first',
                        summary: 'The highest-volume queues Aria is built to own.',
                    ),
                    $this->contentListingSection(
                        heading: 'While you are here',
                        summary: 'Lower-volume automations Aria handles across teams.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific workflow?',
                        summary: 'Tell us the queue you want to automate and we will confirm whether Aria can own it end to end.',
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
                'A not-found page that routes visitors back into Aria\'s use cases, integrations, and proof.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the agent.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page could not be found',
                        'summary' => 'The link is broken or the page has moved. Head back to the use cases and integrations, or book a deployment.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View use cases', 'url' => '#use-cases', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us which workflow or queue you needed and we will route you straight to it.',
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
            name: self::BRAND . ' Deploy',
            title: 'Turn intent into a booked deployment — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a booked deployment',
                'A focused conversion page that moves a visitor from interest to a scoped Aria deployment on a live queue.',
            ),
            renderData: [
                'summary' => 'Turn intent into a booked deployment. Model the return, see the proof, and scope Aria onto your busiest queue today.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Deploy the agent',
                        'heading' => 'Turn intent into a booked deployment',
                        'summary' => 'Whether you start with one queue or roll Aria across a team, the path is the same: model the return, run it in shadow mode, then switch it autonomous.',
                        'actions' => [
                            ['label' => 'Book a deployment', 'url' => 'mailto:deploy@aria.example', 'style' => 'primary'],
                            ['label' => 'Model the return', 'url' => '#roi', 'style' => 'secondary'],
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Scoping an Aria deployment',
                    ],
                    $this->roiCalculatorSection(
                        heading: 'Model the return first',
                        summary: 'Plug in your volume and handle time and see the payback on a first-queue deployment, line by line.',
                    ),
                    $this->proofSection(
                        heading: 'Why teams choose Aria',
                        summary: 'The numbers behind live deployments.',
                    ),
                    $this->ctaSection(
                        heading: 'One queue away',
                        summary: 'Book your deployment now, or email the team and a deployment engineer will reply within one working day.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function outcomeMetricsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'outcome-metrics',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => '68% auto-resolved',
                    'summary' => 'Share of tier-1 tickets Aria closes end to end without a human, measured on a live queue after ninety days.',
                ],
                [
                    'title' => '4.2 minutes saved per ticket',
                    'summary' => 'Average handle time the team gives back once Aria owns first response and resolution on the queue.',
                ],
                [
                    'title' => 'Under 30 seconds to first action',
                    'summary' => 'Median time from a ticket landing to Aria taking the first real step toward resolving it.',
                ],
                [
                    'title' => '4.7 / 5 customer satisfaction',
                    'summary' => 'CSAT on Aria-resolved conversations, held level with or above the team\'s human baseline.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function agentInActionSection(string $heading, string $summary): array
    {
        return [
            'type' => 'agent-in-action',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Understand the request',
                    'summary' => 'Aria reads the ticket, pulls the customer\'s history, and classifies intent before it does anything — no scripted decision tree.',
                ],
                [
                    'title' => 'Gather context across tools',
                    'summary' => 'It queries your billing, CRM, and order systems to assemble the full picture the way a senior agent would.',
                ],
                [
                    'title' => 'Take the action',
                    'summary' => 'Issue the refund, update the subscription, reset the access — Aria executes inside your systems with scoped permissions.',
                ],
                [
                    'title' => 'Confirm and hand off',
                    'summary' => 'It closes the loop with the customer, logs the resolution, and escalates to a teammate the moment confidence drops.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function integrationsGridSection(string $heading, string $summary): array
    {
        return [
            'type' => 'integrations-grid',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Helpdesk and ticketing',
                    'summary' => 'Reads, replies to, and resolves tickets in Zendesk, Intercom, and Freshdesk without leaving the queue.',
                ],
                [
                    'title' => 'CRM and customer data',
                    'summary' => 'Pulls account context from Salesforce and HubSpot so every action is grounded in the customer\'s real history.',
                ],
                [
                    'title' => 'Billing and payments',
                    'summary' => 'Issues refunds, adjusts subscriptions, and reconciles charges through Stripe and your billing platform.',
                ],
                [
                    'title' => 'Internal tools and APIs',
                    'summary' => 'Connects to your own services over REST and webhooks, with scoped credentials and a full audit trail per call.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function useCasesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'use-cases',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Tier-1 support resolution',
                    'summary' => 'Refunds, order status, password resets, and account changes — the high-volume support work Aria owns end to end.',
                ],
                [
                    'title' => 'Order and subscription operations',
                    'summary' => 'Cancellations, upgrades, address changes, and failed-payment recovery, handled across billing and fulfilment.',
                ],
                [
                    'title' => 'Finance back office',
                    'summary' => 'Invoice matching, dunning follow-ups, and reconciliation tasks that quietly consume an operations team\'s week.',
                ],
                [
                    'title' => 'IT and access requests',
                    'summary' => 'Provisioning, deprovisioning, and access approvals routed through policy, with every grant logged for audit.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function roiCalculatorSection(string $heading, string $summary): array
    {
        return [
            'type' => 'roi-calculator',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Your inputs',
                    'summary' => 'Monthly ticket volume, average handle time, and fully loaded cost per agent hour — the three numbers the model runs on.',
                ],
                [
                    'title' => 'Hours returned',
                    'summary' => 'At 68% auto-resolution and 4.2 minutes saved per ticket, a 20,000-ticket month gives roughly 950 agent hours back.',
                ],
                [
                    'title' => 'Monthly payback',
                    'summary' => 'Those returned hours typically cover the deployment several times over inside the first quarter, before any CSAT lift.',
                ],
                [
                    'title' => 'Capacity unlocked',
                    'summary' => 'The team stops drowning in tier-1 volume and moves onto the complex work only a human should be doing.',
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
                    'title' => 'Human in the loop',
                    'summary' => 'Confidence thresholds you set decide what Aria resolves alone and what it escalates, with a clean handoff every time.',
                ],
                [
                    'title' => 'Shadow mode first',
                    'summary' => 'Aria drafts resolutions a human approves before it ever acts autonomously, so you trust it on real tickets before you let go.',
                ],
                [
                    'title' => 'Scoped permissions',
                    'summary' => 'Every integration is connected with the narrowest access the workflow needs, and every action is logged for audit.',
                ],
                [
                    'title' => 'Outcome reporting',
                    'summary' => 'Resolution rate, time saved, and CSAT tracked per queue against your baseline — the numbers a leader reports upward.',
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
                    'title' => 'Returns and exchanges',
                    'summary' => 'Aria validates the policy, issues the label, and updates the order — closing the loop without a human touch.',
                ],
                [
                    'title' => 'Account recovery',
                    'summary' => 'Identity checks, password resets, and access restoration handled to policy, with sensitive steps escalated.',
                ],
                [
                    'title' => 'Vendor onboarding',
                    'summary' => 'Document collection, system access, and approval routing for new vendors, tracked end to end.',
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
                    'title' => '68% auto-resolved',
                    'summary' => 'Tier-1 tickets Aria closes end to end on a live queue, with no human in the loop.',
                ],
                [
                    'title' => '1 week to live',
                    'summary' => 'Typical time from booking a deployment to Aria resolving real tickets in shadow mode.',
                ],
                [
                    'title' => 'Full audit trail',
                    'summary' => 'Every action Aria takes is logged with the context behind it, ready for review.',
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
                ['label' => 'Book a deployment', 'url' => '#cta', 'style' => 'primary'],
                ['label' => 'View use cases', 'url' => '#use-cases', 'style' => 'secondary'],
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
                ['label' => 'Use cases', 'url' => '#use-cases'],
                ['label' => 'Integrations', 'url' => '#integrations'],
                ['label' => 'ROI', 'url' => '#roi'],
                ['label' => 'Proof', 'url' => '#proof'],
                ['label' => 'Deploy', 'url' => '#cta'],
            ],
            'ctaLabel' => 'Book a deployment',
            'ctaUrl' => '#cta',
            'consultationUrl' => '#cta',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'An autonomous AI agent that resolves support and operations work end to end — wired into your tools and measured on outcomes.',
            'columns' => [
                [
                    'heading' => 'Product',
                    'links' => [
                        ['label' => 'Use cases', 'url' => '#use-cases'],
                        ['label' => 'Integrations', 'url' => '#integrations'],
                        ['label' => 'The action loop', 'url' => '#agent-in-action'],
                        ['label' => 'Outcomes', 'url' => '#proof'],
                    ],
                ],
                [
                    'heading' => 'Evaluate',
                    'links' => [
                        ['label' => 'ROI calculator', 'url' => '#roi'],
                        ['label' => 'Proof from deployments', 'url' => '#proof'],
                        ['label' => 'Book a deployment', 'url' => '#cta'],
                        ['label' => 'deploy@aria.example', 'url' => 'mailto:deploy@aria.example'],
                    ],
                ],
                [
                    'heading' => 'Trust',
                    'links' => [
                        ['label' => 'Human in the loop', 'url' => '#features'],
                        ['label' => 'Scoped permissions', 'url' => '#features'],
                        ['label' => 'Audit trail', 'url' => '#features'],
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
