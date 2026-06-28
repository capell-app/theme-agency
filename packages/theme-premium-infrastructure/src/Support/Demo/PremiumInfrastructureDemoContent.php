<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\PremiumInfrastructure\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Premium Infrastructure theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the page
 * adapter emits the theme's signature renderers (product-panels / solutions /
 * global-scale / developer-tools / case-studies-news / trust-compliance /
 * newsletter) alongside the standard hero / proof / content-listing / cta — giving
 * every surface a full, individual cloud-infrastructure platform site rather than
 * the shared five-section skeleton.
 */
final class PremiumInfrastructureDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Gridline';

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
            title: self::BRAND . ' — Cloud Infrastructure for Serious Teams',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Cloud infrastructure built for serious technical buyers',
                'Gridline runs compute, networking, and edge delivery on a single global control plane — with the uptime record, compliance evidence, and developer tooling enterprise platform teams demand.',
            ),
            renderData: [
                'summary' => 'Gridline is a global cloud infrastructure platform: managed compute, private networking, and edge delivery on one control plane, backed by a 99.99% uptime SLA and SOC 2 Type II evidence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Global cloud platform',
                        'heading' => 'Infrastructure your platform team can stand behind',
                        'summary' => 'Provision compute, private networking, and edge delivery from a single control plane. Gridline carries a 99.99% uptime SLA, ships SOC 2 and ISO 27001 evidence on request, and gives engineers a typed API and CLI from day one.',
                        'primary_label' => 'Explore the platform',
                        'primary_url' => '#product-panels',
                        'secondary_label' => 'Talk to an engineer',
                        'secondary_url' => '#contact',
                        'mediaUrl' => $media['hero'][0],
                        'mediaAlt' => 'The Gridline infrastructure control plane',
                    ],
                    $this->productPanelsSection(
                        heading: 'One control plane, three primitives',
                        summary: 'Compute, networking, and edge delivery managed together — so capacity, routing, and failover stay in step instead of drifting across consoles.',
                    ),
                    $this->solutionsSection(
                        heading: 'Built for the workloads that pay the bills',
                        summary: 'The platform patterns teams reach for first when latency, durability, and compliance all have to hold at once.',
                    ),
                    $this->globalScaleSection(
                        heading: 'Twenty-eight regions, one network fabric',
                        summary: 'Anycast routing and private backbone links keep traffic on Gridline\'s own network from the first hop, not the public internet.',
                    ),
                    $this->developerToolsSection(
                        heading: 'Tooling engineers actually keep',
                        summary: 'A typed API, a first-class CLI, and Terraform modules that match the console one-to-one — so infrastructure stays in code review, not in tickets.',
                    ),
                    $this->caseStudiesNewsSection(
                        heading: 'How teams run production on Gridline',
                        summary: 'Real migrations and the numbers that came out the other side.',
                    ),
                    $this->trustComplianceSection(
                        heading: 'Compliance evidence on request',
                        summary: 'The certifications, controls, and audit artefacts your security team will ask for before sign-off.',
                    ),
                    $this->proofSection(),
                    $this->newsletterSection(
                        heading: 'Engineering changelog, no marketing',
                        summary: 'Region launches, API changes, and post-incident writeups — sent the day they ship.',
                    ),
                    $this->ctaSection(
                        heading: 'Stand up your first environment',
                        summary: 'Provision a project, wire it to Terraform, and bring traffic across at your own pace. A solutions engineer can map the migration with you.',
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
            name: self::BRAND . ' Solutions',
            title: 'Solutions Catalog — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'The Gridline solutions catalog',
                'Every platform pattern Gridline supports — compute, data, networking, and edge — structured so an architect can match a workload to the right primitives in minutes.',
            ),
            renderData: [
                'summary' => 'A structured catalog of platform solutions: managed compute, durable storage, private networking, and edge delivery, each with the reference architecture behind it.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Solutions catalog',
                        'heading' => 'Match the workload to the right primitives',
                        'summary' => 'Browse Gridline solutions by domain — compute, data, networking, and edge. Each entry links the reference architecture, the pricing model, and the Terraform module that stands it up.',
                        'primary_label' => 'Explore the platform',
                        'primary_url' => '#product-panels',
                        'secondary_label' => 'Talk to an engineer',
                        'secondary_url' => '#contact',
                        'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                        'mediaAlt' => 'Gridline solutions catalog',
                    ],
                    $this->solutionsSection(
                        heading: 'Featured solutions',
                        summary: 'The patterns platform teams adopt first.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the catalog',
                        summary: 'Reference architectures, sizing guides, and migration playbooks.',
                    ),
                    $this->productPanelsSection(
                        heading: 'The primitives underneath',
                        summary: 'Every solution is composed from the same three managed building blocks.',
                    ),
                    $this->ctaSection(
                        heading: 'Not sure which fits?',
                        summary: 'Tell a solutions engineer about the workload and we will send the reference architecture and a sizing estimate.',
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
            name: self::BRAND . ' Managed Compute',
            title: 'Managed Compute — Solution Profile — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Managed Compute — the flagship Gridline primitive',
                'A full solution profile for Gridline Managed Compute: instance families, autoscaling behaviour, the networking it slots into, and the uptime evidence behind the SLA.',
            ),
            renderData: [
                'summary' => 'A single solution profile that pairs compute capabilities with the global-scale and compliance evidence platform teams check before they commit.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Solution profile',
                        'heading' => 'Managed Compute — the platform\'s workhorse',
                        'summary' => 'Autoscaling instance groups with sub-second health checks, live migration on host failure, and a 99.99% uptime SLA. This profile pairs every capability with the network primitive and the evidence that backs it.',
                        'primary_label' => 'Provision compute',
                        'primary_url' => '#contact',
                        'secondary_label' => 'Read the SLA',
                        'secondary_url' => '#trust-compliance',
                        'mediaUrl' => $media['detail'][0],
                        'mediaAlt' => 'Gridline Managed Compute architecture',
                    ],
                    $this->productPanelsSection(
                        heading: 'Managed Compute at a glance',
                        summary: 'The specification a platform engineer checks first, before they move a single workload.',
                    ),
                    $this->globalScaleSection(
                        heading: 'How compute spans the network',
                        summary: 'Instance groups place across regions on the private backbone, with anycast routing handling failover automatically.',
                    ),
                    $this->caseStudiesNewsSection(
                        heading: 'Teams running on Managed Compute',
                        summary: 'Production workloads built on the flagship primitive.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'Provision Managed Compute',
                        summary: 'Stand up an instance group from the console or Terraform in minutes, or have a solutions engineer size it with you first.',
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
            name: self::BRAND . ' Contact',
            title: 'Talk to our infrastructure team — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Talk to the Gridline infrastructure team',
                'Tell us about the workload you want to move. Solutions engineers map the migration, size the environment, and walk your security team through the compliance evidence directly.',
            ),
            renderData: [
                'summary' => 'Talk to a Gridline solutions engineer about migrations, sizing, compliance reviews, or enterprise pricing. We reply within one working day.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Talk to our infrastructure team',
                        'summary' => 'We work with platform, infrastructure, and security teams running real production load. Email engineering@gridline.example or use the channels below — a solutions engineer replies within one working day.',
                        'primary_label' => 'Email the team',
                        'primary_url' => 'mailto:engineering@gridline.example',
                        'secondary_label' => 'Explore the platform',
                        'secondary_url' => '#product-panels',
                        'mediaUrl' => $media['contact'][0],
                        'mediaAlt' => 'The Gridline solutions engineering team',
                    ],
                    $this->developerToolsSection(
                        heading: 'What a conversation gives you',
                        summary: 'Everything a platform team needs to evaluate Gridline seriously, from the first call.',
                    ),
                    $this->trustComplianceSection(
                        heading: 'Bring your security team',
                        summary: 'We walk auditors through the controls and evidence directly — no portal request queue.',
                    ),
                    $this->newsletterSection(
                        heading: 'Stay on the changelog',
                        summary: 'Region launches, API changes, and post-incident writeups, sent the day they ship.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Book a 30-minute architecture call and we will map the fastest path to a running environment.',
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
                'A graceful empty state for a filtered solutions catalog with no matching entries.',
            ),
            renderData: [
                'summary' => 'No solution matches that filter yet — but a solutions engineer can still point you to the right primitives.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Solutions catalog',
                        'heading' => 'Nothing matches that filter — yet',
                        'summary' => 'No solution is catalogued under this filter. Clear it to see everything, or jump straight to the platform primitives.',
                        'primary_label' => 'View all solutions',
                        'primary_url' => '#solutions',
                        'secondary_label' => 'Explore the platform',
                        'secondary_url' => '#product-panels',
                    ],
                    $this->contentListingSection(
                        heading: 'While you are here',
                        summary: 'Reference architectures teams reach for most often.',
                    ),
                    $this->productPanelsSection(
                        heading: 'Start from the primitives instead',
                        summary: 'The three managed building blocks every solution composes.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific pattern?',
                        summary: 'Tell a solutions engineer the workload and we will send the matching reference architecture and a sizing estimate.',
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
                'A not-found page that routes visitors back into the platform, solutions, and developer tooling.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the platform.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That route returned a 404',
                        'summary' => 'The link is broken or the page has moved. Head back to the platform and solutions, or talk to an engineer.',
                        'primary_label' => 'Back to home',
                        'primary_url' => '/',
                        'secondary_label' => 'Explore the platform',
                        'secondary_url' => '#product-panels',
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us which solution or doc you needed and a solutions engineer will point you to it.',
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
            title: 'Stand up your first environment — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a running environment',
                'A focused conversion page that moves a platform team from interest to a provisioned Gridline project.',
            ),
            renderData: [
                'summary' => 'Turn intent into a running environment. Provision your first project from the console or Terraform today, or have an engineer size it with you.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get started',
                        'heading' => 'Turn intent into a running environment',
                        'summary' => 'Whether you stand up one instance group or migrate a whole estate, the path is the same: create a project, wire it to Terraform, bring traffic across at your pace.',
                        'primary_label' => 'Create a project',
                        'primary_url' => '#contact',
                        'secondary_label' => 'Email the team',
                        'secondary_url' => 'mailto:engineering@gridline.example',
                        'mediaUrl' => $media['cta'][0],
                        'mediaAlt' => 'Provisioning a Gridline environment',
                    ],
                    $this->developerToolsSection(
                        heading: 'Everything in code from the start',
                        summary: 'A typed API, a first-class CLI, and Terraform modules that match the console one-to-one.',
                    ),
                    $this->proofSection(),
                    $this->ctaSection(
                        heading: 'One project away',
                        summary: 'Provision your first environment now, or book an architecture call and we will reply within one working day.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function productPanelsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'product-panels',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Managed Compute',
                    'summary' => 'Autoscaling instance groups with live migration on host failure and sub-second health checks, billed by the second.',
                ],
                [
                    'title' => 'Private Networking',
                    'summary' => 'Software-defined VPCs, private service connect, and a global backbone that keeps traffic off the public internet.',
                ],
                [
                    'title' => 'Edge Delivery',
                    'summary' => 'Anycast CDN and programmable edge functions that put cache and logic within 30ms of every user.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function solutionsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'solutions',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'High-throughput APIs',
                    'summary' => 'Autoscaling compute behind a global anycast edge, with request-level routing and zero-downtime deploys.',
                ],
                [
                    'title' => 'Durable data platforms',
                    'summary' => 'Replicated block storage and managed Postgres with point-in-time recovery and cross-region failover.',
                ],
                [
                    'title' => 'Real-time streaming',
                    'summary' => 'Low-latency private networking and edge functions for telemetry, events, and live media pipelines.',
                ],
                [
                    'title' => 'Regulated workloads',
                    'summary' => 'Region pinning, customer-managed keys, and audit logging for finance, health, and public-sector teams.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function globalScaleSection(string $heading, string $summary): array
    {
        return [
            'type' => 'global-scale',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => '28 regions',
                    'meta' => 'Six continents',
                    'summary' => 'Provision in any region from the same control plane, with data residency enforced at the project level.',
                ],
                [
                    'title' => 'Private backbone',
                    'meta' => 'Network fabric',
                    'summary' => 'Inter-region traffic rides Gridline\'s own fibre, not the public internet, for predictable latency and lower egress.',
                ],
                [
                    'title' => 'Anycast routing',
                    'meta' => 'Automatic failover',
                    'summary' => 'Every public endpoint is anycast, so a region outage reroutes users to the next-nearest healthy region in seconds.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function developerToolsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'developer-tools',
            'heading' => $heading,
            'summary' => $summary,
            'label' => 'Read the API reference',
            'url' => '#developers',
            'items' => [
                [
                    'title' => 'Typed API and SDKs',
                    'summary' => 'A versioned REST API with generated SDKs for Go, TypeScript, and Python, and an OpenAPI spec you can codegen against.',
                ],
                [
                    'title' => 'First-class CLI',
                    'summary' => 'Provision, inspect, and tail logs from the terminal — every console action has a scriptable command behind it.',
                ],
                [
                    'title' => 'Terraform parity',
                    'summary' => 'Official Terraform modules that map to the console one-to-one, so infrastructure changes stay in code review.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function caseStudiesNewsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'case-studies-news',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Payments platform cut p99 latency 40%',
                    'meta' => 'Fintech migration',
                    'summary' => 'Moving the API tier onto Managed Compute behind the anycast edge took p99 from 220ms to 130ms across three regions.',
                ],
                [
                    'title' => 'Streaming service survived a region outage',
                    'meta' => 'Resilience',
                    'summary' => 'Anycast failover rerouted four million live viewers to a neighbouring region in under twenty seconds, with no manual intervention.',
                ],
                [
                    'title' => 'Health platform passed its SOC 2 audit',
                    'meta' => 'Compliance',
                    'summary' => 'Region pinning and customer-managed keys let a health-tech team clear their Type II audit on Gridline without a single exception.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function trustComplianceSection(string $heading, string $summary): array
    {
        return [
            'type' => 'trust-compliance',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'SOC 2 Type II',
                    'summary' => 'Audited annually by an independent firm; the report is available under NDA on request.',
                ],
                [
                    'title' => 'ISO 27001',
                    'summary' => 'Certified information-security management across every region and operational team.',
                ],
                [
                    'title' => 'Customer-managed keys',
                    'summary' => 'Bring your own KMS keys and revoke them at any time to cut access to data at rest.',
                ],
                [
                    'title' => 'Audit logging',
                    'summary' => 'Every control-plane action is logged, exportable, and retained to satisfy your own audit trail.',
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
            'action' => '#newsletter',
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
                    'title' => 'Reference architecture: multi-region API tier',
                    'category' => 'Architecture',
                    'summary' => 'How to place Managed Compute, private networking, and the anycast edge for a globally available API.',
                ],
                [
                    'title' => 'Sizing guide: durable Postgres at scale',
                    'category' => 'Data',
                    'summary' => 'Instance families, replication topology, and recovery objectives for a managed database under real load.',
                ],
                [
                    'title' => 'Migration playbook: lift-and-shift to Gridline',
                    'category' => 'Migration',
                    'summary' => 'A staged plan for bringing an existing estate across with Terraform, with rollback at every step.',
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
            'items' => [
                [
                    'value' => '99.99%',
                    'label' => 'Uptime SLA, measured and credited per region.',
                ],
                [
                    'value' => '28',
                    'label' => 'Regions across six continents on one control plane.',
                ],
                [
                    'value' => '<30ms',
                    'label' => 'Edge latency to the median user, anywhere on the network.',
                ],
                [
                    'value' => '1 day',
                    'label' => 'Typical turnaround on an enterprise solutions request.',
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
            'label' => 'Talk to an engineer',
            'url' => '#contact',
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
                ['label' => 'Platform', 'url' => '#product-panels'],
                ['label' => 'Solutions', 'url' => '#solutions'],
                ['label' => 'Global scale', 'url' => '#global-scale'],
                ['label' => 'Developers', 'url' => '#developer-tools'],
                ['label' => 'Trust', 'url' => '#trust-compliance'],
            ],
            'ctaLabel' => 'Talk to an engineer',
            'ctaUrl' => '#contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'summary' => 'A global cloud infrastructure platform: managed compute, private networking, and edge delivery on one control plane.',
            'columns' => [
                [
                    'heading' => 'Platform',
                    'links' => [
                        ['label' => 'Managed Compute', 'url' => '#product-panels'],
                        ['label' => 'Private Networking', 'url' => '#product-panels'],
                        ['label' => 'Edge Delivery', 'url' => '#product-panels'],
                        ['label' => 'Global scale', 'url' => '#global-scale'],
                    ],
                ],
                [
                    'heading' => 'Solutions',
                    'links' => [
                        ['label' => 'High-throughput APIs', 'url' => '#solutions'],
                        ['label' => 'Durable data platforms', 'url' => '#solutions'],
                        ['label' => 'Regulated workloads', 'url' => '#solutions'],
                        ['label' => 'Reference architectures', 'url' => '#content-listing'],
                    ],
                ],
                [
                    'heading' => 'Developers',
                    'links' => [
                        ['label' => 'API reference', 'url' => '#developer-tools'],
                        ['label' => 'CLI', 'url' => '#developer-tools'],
                        ['label' => 'Terraform modules', 'url' => '#developer-tools'],
                        ['label' => 'Changelog', 'url' => '#newsletter'],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'links' => [
                        ['label' => 'Trust and compliance', 'url' => '#trust-compliance'],
                        ['label' => 'Talk to an engineer', 'url' => '#contact'],
                        ['label' => 'engineering@gridline.example', 'url' => 'mailto:engineering@gridline.example'],
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
