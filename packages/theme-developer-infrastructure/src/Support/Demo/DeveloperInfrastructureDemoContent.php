<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\DeveloperInfrastructure\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Developer Infrastructure theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the page
 * adapter emits the theme's signature renderers (platform-pillars / command-blocks /
 * deploy-timeline / docs-changelog / integrations / architecture-enterprise) alongside
 * the standard hero / features / proof / cta — giving every surface a full, individual
 * developer-platform site rather than the shared five-section skeleton.
 */
final class DeveloperInfrastructureDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Northbound';

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
            title: self::BRAND . ' — Deploy infrastructure with confidence',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'The deployment platform for engineering teams',
                'Northbound ships your services to global edge regions, runs the build and rollout pipeline, and keeps the docs, changelog, and integrations developers reach for first.',
            ),
            renderData: [
                'summary' => 'Northbound is the deployment platform that takes a git push to a globally distributed service — with build pipelines, instant rollbacks, first-class observability, and the integrations your stack already runs on.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Developer infrastructure',
                        'heading' => 'Ship infrastructure your team can deploy with confidence',
                        'summary' => 'Push to a branch and Northbound builds, deploys, and routes traffic across the edge in under ninety seconds — with one-command rollbacks, preview environments per pull request, and observability wired in from the first deploy.',
                        'primary_label' => 'Read the docs',
                        'primary_url' => '#docs-changelog',
                        'secondary_label' => 'Explore the platform',
                        'secondary_url' => '#platform-pillars',
                        'notes' => [
                            '90-second median deploy from push to live traffic',
                            'One-command rollback to any previous release',
                            'Preview environment for every pull request',
                        ],
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'The Northbound deployment dashboard',
                    ],
                    $this->platformPillarsSection(
                        heading: 'One platform from git push to global traffic',
                        summary: 'Build, deploy, observe, and roll back from a single control plane — no glue scripts holding your release process together.',
                    ),
                    $this->commandBlocksSection(
                        heading: 'A workflow that fits in muscle memory',
                        summary: 'The whole deploy lifecycle from your terminal. Install the CLI, link a repository, and ship.',
                    ),
                    $this->deployTimelineSection(
                        heading: 'Every deploy, fully traced',
                        summary: 'Watch a release move through build, rollout, and verification — with the timing and status of every stage on record.',
                    ),
                    $this->docsChangelogSection(
                        heading: 'Docs and changelog developers actually read',
                        summary: 'Versioned API references, runnable guides, and a changelog that ships alongside every platform release.',
                    ),
                    $this->integrationsSection(
                        heading: 'Wired into the stack you already run',
                        summary: 'First-class connectors for the source control, observability, and secrets tools your team depends on.',
                    ),
                    $this->architectureEnterpriseSection(
                        heading: 'Architecture built for teams under scrutiny',
                        summary: 'Isolated tenancy, audited access, and compliance posture that holds up when security review asks the hard questions.',
                    ),
                    $this->proofSection(
                        heading: 'The numbers teams run Northbound on',
                        summary: 'Reliability and speed, measured where it counts.',
                    ),
                    $this->newsletterSection(
                        heading: 'Release notes in your inbox',
                        summary: 'Subscribe for platform changelog entries, new region launches, and deep-dive engineering posts — no marketing filler.',
                    ),
                    $this->ctaSection(
                        heading: 'Deploy your first service today',
                        summary: 'Connect a repository, push to main, and watch it go live across the edge. The free tier needs no card to start.',
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
            name: self::BRAND . ' Docs',
            title: 'Docs & Changelog — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The Northbound docs and changelog archive',
                'Every guide, API reference, and changelog entry the platform ships — structured to be scanned, searched, and linked straight from your editor.',
            ),
            renderData: [
                'summary' => 'A structured archive of platform documentation and release notes: runnable guides, versioned references, and every changelog entry shipped to date.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Docs & changelog',
                        'heading' => 'Everything the platform ships, documented',
                        'summary' => 'Browse the archive by track — getting started, deploy pipelines, networking, and the changelog. Each entry links the guide, the API reference, and the CLI command behind it.',
                        'primary_label' => 'Open the docs',
                        'primary_url' => '#docs-changelog',
                        'secondary_label' => 'Browse integrations',
                        'secondary_url' => '#integrations',
                        'notes' => [
                            'Versioned references for every API surface',
                            'Runnable guides you can copy straight into a terminal',
                            'Changelog updated with each platform release',
                        ],
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'The Northbound documentation library',
                    ],
                    $this->docsChangelogSection(
                        heading: 'Latest from the changelog',
                        summary: 'The releases the platform team shipped most recently.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        summary: 'Deploy notes, migration guides, and reference material across every track.',
                    ),
                    $this->ctaSection(
                        heading: 'Found what you needed?',
                        summary: 'Pull the CLI, link a repository, and run the guide you were reading against a live preview environment.',
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
            name: self::BRAND . ' Edge Network',
            title: 'Edge Network — Platform Profile — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'The Northbound edge network',
                'A full platform profile for the edge network: how a deploy reaches global regions, the command workflow behind it, and the architecture that keeps it isolated and audited.',
            ),
            renderData: [
                'summary' => 'A single platform profile pairing the deploy workflow and the architecture proof so engineering teams can adopt the edge network with confidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Platform profile',
                        'heading' => 'The edge network — global by default',
                        'summary' => 'One push, deployed to every region. This profile pairs the command workflow that gets you there with the architecture and compliance posture that keeps it production-grade.',
                        'primary_label' => 'Deploy to the edge',
                        'primary_url' => '#cta',
                        'secondary_label' => 'Read the architecture',
                        'secondary_url' => '#architecture-enterprise',
                        'notes' => [
                            'Anycast routing across 30 edge regions',
                            'Atomic rollouts with instant rollback',
                            'Per-region health checks before traffic shifts',
                        ],
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'The Northbound edge network topology',
                    ],
                    $this->commandBlocksSection(
                        heading: 'The deploy workflow, end to end',
                        summary: 'The exact commands that take a repository from linked to live across the edge.',
                    ),
                    $this->deployTimelineSection(
                        heading: 'How a release reaches the edge',
                        summary: 'Each stage of an edge rollout, traced with the timing and status engineers check first.',
                    ),
                    $this->architectureEnterpriseSection(
                        heading: 'Architecture behind the edge',
                        summary: 'Isolation, routing, and audit guarantees that hold up under enterprise review.',
                    ),
                    $this->proofSection(
                        heading: 'What the edge network delivers',
                        summary: 'The reliability and reach teams build on.',
                    ),
                    $this->ctaSection(
                        heading: 'Put the edge network to work',
                        summary: 'Link a repository and deploy a real service to every region. The free tier needs no card to start.',
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
            name: self::BRAND . ' Sales',
            title: 'Talk to the platform team — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Talk to the platform team',
                'Tell us what you are deploying. The platform team answers architecture, migration, and enterprise questions directly — no sales funnel between you and an engineer.',
            ),
            renderData: [
                'summary' => 'Reach the Northbound platform team for a demo, a migration plan, or an enterprise rollout. We route you to an engineer, usually within one working day.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Talk to us',
                        'heading' => 'Reach the platform team directly',
                        'summary' => 'Whether you are evaluating a migration, sizing an enterprise rollout, or just want feedback on your deploy pipeline, you talk to an engineer — not a sales script. Email platform@northbound.example or use a path below.',
                        'primary_label' => 'Book a demo',
                        'primary_url' => '#cta',
                        'secondary_label' => 'Read the docs',
                        'secondary_url' => '#docs-changelog',
                        'notes' => [
                            'Architecture and migration questions answered by engineers',
                            'Enterprise pilots scoped in a single call',
                            'One working day median first response',
                        ],
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Northbound platform team',
                    ],
                    $this->featuresSection(
                        heading: 'What a conversation gets you',
                        summary: 'Everything you need to evaluate Northbound for a real workload, from the first call.',
                    ),
                    $this->newsletterSection(
                        heading: 'Prefer to follow along first?',
                        summary: 'Subscribe to platform release notes and engineering deep-dives while you decide — unsubscribe in one click.',
                    ),
                    $this->ctaSection(
                        heading: 'Ready when you are',
                        summary: 'Book a 30-minute technical call and we will map the fastest path from your current setup to a Northbound deploy.',
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
            title: 'Nothing matches that filter — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'Nothing matches that filter yet',
                'A graceful empty state for a filtered docs and changelog search that returns no matching entries.',
            ),
            renderData: [
                'summary' => 'No documentation or changelog entries match that filter yet — but the platform can still point you to the right guide or integration.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Docs & changelog',
                        'heading' => 'No entries match that filter — yet',
                        'summary' => 'Nothing in the archive matches this search. Clear the filter to see every guide and changelog entry, or jump straight to the platform and integrations.',
                        'primary_label' => 'Clear the filter',
                        'primary_url' => '#docs-changelog',
                        'secondary_label' => 'Browse the platform',
                        'secondary_url' => '#platform-pillars',
                        'notes' => [
                            'The full archive is one click away',
                            'Search runs across docs, deploys, and changelog',
                            'Every region launch lands here first',
                        ],
                    ],
                    $this->contentListingSection(
                        heading: 'While you are here',
                        summary: 'Recent guides and changelog entries across every track.',
                    ),
                    $this->platformPillarsSection(
                        heading: 'Explore the platform instead',
                        summary: 'The four capabilities at the centre of how teams deploy on Northbound.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell the platform team which guide or integration you need and we will point you straight to it.',
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
                'A not-found page that routes visitors back into the platform, docs, and integrations.',
            ),
            renderData: [
                'summary' => 'That page has moved or never shipped — here is the way back into the platform.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That route returned a 404',
                        'summary' => 'The link is broken or the page has been deployed away. Head back to the platform and docs, or open the changelog to see what shipped recently.',
                        'primary_label' => 'Back to home',
                        'primary_url' => '/',
                        'secondary_label' => 'Open the docs',
                        'secondary_url' => '#docs-changelog',
                        'notes' => [
                            'The platform overview is one click away',
                            'Search the docs for what you were after',
                            'The changelog lists every recent release',
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell the platform team which page or guide you needed and we will route you to it.',
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
            name: self::BRAND . ' Get Started',
            title: 'Deploy your first service — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn a git push into a global deploy',
                'A focused conversion page that moves a visitor from interest to a live service running across the edge.',
            ),
            renderData: [
                'summary' => 'Turn a git push into a global deploy. Link a repository and ship your first service across the edge today.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get started',
                        'heading' => 'Turn a git push into a global deploy',
                        'summary' => 'Whether you ship one service or a hundred, the path is the same: link the repo, push to main, and watch Northbound build, deploy, and route traffic across the edge.',
                        'primary_label' => 'Deploy your first service',
                        'primary_url' => '#cta',
                        'secondary_label' => 'Read the docs',
                        'secondary_url' => '#docs-changelog',
                        'notes' => [
                            'No card required on the free tier',
                            'First deploy live in under two minutes',
                            'Roll back instantly if anything looks wrong',
                        ],
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'The Northbound deploy command in a terminal',
                    ],
                    $this->commandBlocksSection(
                        heading: 'Three commands to your first deploy',
                        summary: 'Install the CLI, link a repository, and ship — the whole onboarding lives in your terminal.',
                    ),
                    $this->proofSection(
                        heading: 'Why teams choose Northbound',
                        summary: 'The reliability and speed behind the platform.',
                    ),
                    $this->ctaSection(
                        heading: 'One push away',
                        summary: 'Connect a repository now, or book a call with the platform team and we will reply within one working day.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function platformPillarsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'platform-pillars',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Build pipeline',
                    'summary' => 'Detect the framework, build a reproducible image, and cache layers so the median build finishes in seconds, not minutes.',
                ],
                [
                    'title' => 'Edge deploy',
                    'summary' => 'Atomic rollouts to 30 anycast regions, with per-region health checks before any traffic shifts to the new release.',
                ],
                [
                    'title' => 'Observability',
                    'summary' => 'Structured logs, request traces, and golden-signal metrics wired in from the first deploy — no agent to install.',
                ],
                [
                    'title' => 'Instant rollback',
                    'summary' => 'Every release is immutable and addressable, so rolling back to a known-good version is a single command.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function commandBlocksSection(string $heading, string $summary): array
    {
        return [
            'type' => 'command-blocks',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'nb login',
                    'summary' => 'Authenticate the CLI against your team in one browser round-trip. Tokens are scoped per environment and revocable.',
                ],
                [
                    'title' => 'nb link',
                    'summary' => 'Connect a git repository and Northbound infers the build, sets up preview environments, and wires the changelog.',
                ],
                [
                    'title' => 'nb deploy',
                    'summary' => 'Build, ship, and route traffic across the edge — or let a push to main trigger the same pipeline automatically.',
                ],
                [
                    'title' => 'nb rollback',
                    'summary' => 'Pin traffic back to any previous release instantly while you investigate, with no rebuild required.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function deployTimelineSection(string $heading, string $summary): array
    {
        return [
            'type' => 'deploy-timeline',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Build',
                    'summary' => 'Source is fetched, dependencies are restored from cache, and a reproducible image is produced and signed.',
                    'meta' => '0:00 – 0:34',
                    'care_note' => 'Cached layers reused',
                ],
                [
                    'title' => 'Rollout',
                    'summary' => 'The new release is staged to every edge region and health-checked before a single request is shifted to it.',
                    'meta' => '0:34 – 1:12',
                    'care_note' => '30 regions, zero downtime',
                ],
                [
                    'title' => 'Verify',
                    'summary' => 'Golden-signal metrics are watched against the previous release; traffic completes its shift only when they hold.',
                    'meta' => '1:12 – 1:28',
                    'care_note' => 'Auto-rollback on regression',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function docsChangelogSection(string $heading, string $summary): array
    {
        return [
            'type' => 'docs-changelog',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Open the docs',
            'url' => '#docs-changelog',
            'items' => [
                [
                    'title' => 'v4.2 — Regional failover',
                    'summary' => 'Traffic now drains automatically from a degraded region to its nearest healthy neighbour, with no configuration required.',
                ],
                [
                    'title' => 'v4.1 — Preview environment secrets',
                    'summary' => 'Per-branch preview environments now inherit scoped secrets, so pull-request deploys mirror production safely.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function integrationsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'integrations',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'GitHub & GitLab',
                    'summary' => 'Deploy on push, comment preview URLs on every pull request, and gate merges on a green rollout.',
                    'meta' => 'Source control',
                ],
                [
                    'title' => 'Datadog & Grafana',
                    'summary' => 'Stream logs, traces, and metrics straight into the observability stack your team already watches.',
                    'meta' => 'Observability',
                ],
                [
                    'title' => 'Vault & 1Password',
                    'summary' => 'Sync secrets at deploy time so credentials never live in your repository or your build logs.',
                    'meta' => 'Secrets',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function architectureEnterpriseSection(string $heading, string $summary): array
    {
        return [
            'type' => 'architecture-enterprise',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Isolated tenancy',
                    'summary' => 'Each workload runs in its own network-isolated sandbox, so a noisy neighbour can never reach across the boundary.',
                ],
                [
                    'title' => 'Audited access',
                    'summary' => 'Every deploy, rollback, and config change is signed, attributed, and streamed to an immutable audit log.',
                ],
                [
                    'title' => 'Compliance posture',
                    'summary' => 'SOC 2 Type II and ISO 27001 controls, with region pinning available for data-residency requirements.',
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
                    'title' => 'Architecture review',
                    'summary' => 'An engineer maps your current deploy pipeline to Northbound and flags the risky parts before you commit.',
                ],
                [
                    'title' => 'Migration plan',
                    'summary' => 'A staged cutover plan that moves services region by region with a rollback path at every step.',
                ],
                [
                    'title' => 'Hands-on pilot',
                    'summary' => 'Deploy a real workload on a shared call so the platform proves itself against your traffic, not a demo app.',
                ],
                [
                    'title' => 'Enterprise scoping',
                    'summary' => 'Tenancy, audit, and compliance requirements scoped up front so security review has answers before it asks.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsletterSection(string $heading, string $summary): array
    {
        return [
            'type' => 'newsletter',
            'heading' => $heading,
            'summary' => $summary,
            'action' => '#cta',
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
                    'title' => 'Zero-downtime database migrations',
                    'summary' => 'A guide to expand-and-contract schema changes that ship safely behind a Northbound rollout.',
                    'category' => 'Guide',
                ],
                [
                    'title' => 'Tuning build cache hit rates',
                    'summary' => 'How layer caching is keyed, and the three changes that cut most teams build times in half.',
                    'category' => 'Guide',
                ],
                [
                    'title' => 'Reading a deploy trace',
                    'summary' => 'Walk through every stage of a rollout trace and learn which signal tells you a release is in trouble.',
                    'category' => 'Reference',
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
                    'value' => '99.99%',
                    'label' => 'Platform uptime measured across the edge over the trailing year.',
                ],
                [
                    'value' => '90s',
                    'label' => 'Median time from a git push to live traffic across every region.',
                ],
                [
                    'value' => '30 regions',
                    'label' => 'Anycast edge locations a single deploy reaches by default.',
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
            'label' => 'Deploy your first service',
            'url' => '#cta',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'items' => [
                ['label' => 'Platform', 'url' => '#platform-pillars'],
                ['label' => 'Docs', 'url' => '#docs-changelog'],
                ['label' => 'Integrations', 'url' => '#integrations'],
                ['label' => 'Enterprise', 'url' => '#architecture-enterprise'],
                ['label' => 'Pricing', 'url' => '#cta'],
            ],
            'ctaLabel' => 'Deploy your first service',
            'ctaUrl' => '#cta',
            'consultationUrl' => '#cta',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'heading' => 'Platform',
                'title' => 'Platform',
                'links' => [
                    ['label' => 'Build pipeline', 'url' => '#platform-pillars'],
                    ['label' => 'Edge deploy', 'url' => '#platform-pillars'],
                    ['label' => 'Observability', 'url' => '#platform-pillars'],
                    ['label' => 'Deploy timeline', 'url' => '#deploy-timeline'],
                ],
            ],
            [
                'heading' => 'Developers',
                'title' => 'Developers',
                'links' => [
                    ['label' => 'Docs', 'url' => '#docs-changelog'],
                    ['label' => 'Changelog', 'url' => '#docs-changelog'],
                    ['label' => 'Integrations', 'url' => '#integrations'],
                    ['label' => 'CLI reference', 'url' => '#command-blocks'],
                ],
            ],
            [
                'heading' => 'Company',
                'title' => 'Company',
                'links' => [
                    ['label' => 'Enterprise', 'url' => '#architecture-enterprise'],
                    ['label' => 'Talk to the team', 'url' => '#cta'],
                    ['label' => 'platform@northbound.example', 'url' => 'mailto:platform@northbound.example'],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'The deployment platform that takes a git push to a globally distributed service — build, deploy, observe, and roll back from one control plane.',
            'columns' => $columns,
            'items' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
