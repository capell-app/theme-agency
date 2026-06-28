<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\FintechTrust\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Fintech Trust theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (verification-flow /
 * security-architecture / compliance-badges / coverage-map / metric-cards)
 * alongside the standard hero/proof/cta — giving every surface a full,
 * individual regulated-fintech site rather than the shared skeleton.
 */
final class FintechTrustDemoContent implements ProvidesThemeDemoContent
{
    private const BRAND = 'Veridian Trust';

    /**
     * @return array<int, ThemeDemoPageDefinition>
     */
    public function definitions(string $themeKey, string $themeName, string $baseUrl): array
    {
        return [
            $this->homepage($themeKey),
            $this->directory($themeKey),
            $this->detail($themeKey),
            $this->contact($themeKey),
            $this->empty($themeKey),
            $this->notFound($themeKey),
            $this->cta($themeKey),
        ];
    }

    private function homepage(string $themeKey): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'homepage',
            name: self::BRAND . ' Home',
            title: self::BRAND . ' — Business verification & KYB',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Verify a business in seconds, with a trail you can audit',
                'Veridian Trust is the verification layer for regulated fintech — identity, KYB, ownership, and risk in one auditable platform.',
            ),
            renderData: [
                'summary' => 'Veridian Trust is the verification layer for regulated fintech. Identity, KYB, ownership, and risk decisions in one auditable platform.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Business verification platform',
                        'heading' => 'Verify a business in seconds, with a trail you can audit',
                        'summary' => 'Onboard companies and their beneficial owners against authoritative registries, score risk in real time, and hand your auditors a record that never goes stale.',
                        'actions' => [
                            ['label' => 'Start a verification', 'url' => '#verification-flow', 'style' => 'primary'],
                            ['label' => 'Explore security', 'url' => '#security-architecture', 'style' => 'secondary'],
                        ],
                    ],
                    $this->complianceBadgesSection(),
                    $this->verificationFlowSection(
                        heading: 'A verification flow built to be audited',
                        summary: 'Five structured steps move a business from first lookup to a signed-off decision — every step timestamped and attributable.',
                    ),
                    $this->securityArchitectureSection(),
                    $this->coverageMapSection(),
                    $this->metricCardsSection(),
                    $this->proofSection(
                        heading: 'Trusted where the stakes are highest',
                        summary: 'Compliance and risk teams at regulated institutions run onboarding on Veridian Trust.',
                    ),
                    $this->ctaSection(
                        heading: 'Bring verification in-house in a sprint',
                        summary: 'Most teams run their first live verification within two weeks of signing. Talk to us about your onboarding and reporting obligations.',
                    ),
                ],
            ],
            type: PageTypeEnum::Home,
            layout: LayoutEnum::Home,
        );
    }

    private function directory(string $themeKey): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'directory',
            name: self::BRAND . ' Coverage',
            title: 'Verification coverage — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Registry and jurisdiction coverage, built to be scanned',
                'A directory of the registries, watchlists, and jurisdictions Veridian Trust checks against on every business verification.',
            ),
            renderData: [
                'summary' => 'The registries, watchlists, and jurisdictions Veridian Trust checks against on every business verification.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Coverage directory',
                        'heading' => 'Every source we check, in one legible place',
                        'summary' => 'Filter by registry, watchlist, or jurisdiction to see exactly what each verification touches — and how fresh the underlying data is.',
                        'actions' => [
                            ['label' => 'Talk to the trust team', 'url' => '#cta', 'style' => 'primary'],
                        ],
                    ],
                    $this->coverageListingSection(),
                    $this->coverageMapSection(),
                    $this->ctaSection(
                        heading: 'Need a jurisdiction we have not listed?',
                        summary: 'We add registries and watchlists on request. Tell us where you onboard and we will map the available authoritative sources.',
                    ),
                ],
            ],
            layout: LayoutEnum::Results,
        );
    }

    private function detail(string $themeKey): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Verification Record',
            title: 'Verification record — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A verification record that reads with confidence',
                'How a single business verification record pairs ownership, registration, and risk so a compliance officer can sign off with certainty.',
            ),
            renderData: [
                'summary' => 'A single verification record pairs ownership, registration, and risk so a compliance officer can sign off with certainty.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Verification record',
                        'heading' => 'Meridian Capital Ltd — verified and decision-ready',
                        'summary' => 'Registration confirmed against Companies House, four beneficial owners screened, no adverse media or sanctions hits. Risk band: low. Reviewed 4 minutes ago.',
                        'actions' => [
                            ['label' => 'Download audit pack', 'url' => '#cta', 'style' => 'primary'],
                            ['label' => 'View security model', 'url' => '#security-architecture', 'style' => 'secondary'],
                        ],
                    ],
                    $this->verificationFlowSection(
                        heading: 'How this record was assembled',
                        summary: 'Each step shows the source that was checked, the result, and the analyst or rule that cleared it.',
                    ),
                    $this->securityArchitectureSection(),
                    $this->metricCardsSection(),
                    $this->ctaSection(
                        heading: 'Hand this record to your auditors',
                        summary: 'Export the full evidence trail — sources, timestamps, and sign-offs — as a tamper-evident audit pack in one click.',
                    ),
                ],
            ],
        );
    }

    private function contact(string $themeKey): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'contact',
            name: self::BRAND . ' Contact',
            title: 'Talk to the trust team — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Reach the trust team through one confident path',
                'Speak to a compliance specialist about onboarding, KYB coverage, and the reporting obligations specific to your licence.',
            ),
            renderData: [
                'summary' => 'Speak to a compliance specialist about onboarding, KYB coverage, and the reporting obligations specific to your licence.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Contact',
                        'heading' => 'Reach the trust team through one confident path',
                        'summary' => 'Email trust@veridian.example or book a 30-minute review with a compliance specialist. We reply to every regulated enquiry within one business day.',
                        'actions' => [
                            ['label' => 'Email the trust team', 'url' => 'mailto:trust@veridian.example', 'style' => 'primary'],
                            ['label' => 'Explore the platform', 'url' => '#verification-flow', 'style' => 'secondary'],
                        ],
                    ],
                    $this->featuresSection(
                        heading: 'What a first conversation covers',
                        summary: 'We scope your onboarding flow against the obligations you actually carry.',
                    ),
                    $this->proofSection(
                        heading: 'You are in regulated company',
                        summary: 'Compliance teams who chose to standardise verification on Veridian Trust.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer a working session?',
                        summary: 'Bring a real onboarding case and we will run it live, end to end, on a call with our compliance and engineering leads.',
                    ),
                ],
            ],
            layout: LayoutEnum::System,
        );
    }

    private function empty(string $themeKey): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'empty',
            name: self::BRAND . ' No Results',
            title: 'No matching records — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No verification records match that filter',
                'A graceful empty state for a filtered verification queue with no matching businesses.',
            ),
            renderData: [
                'summary' => 'No verification records match that filter yet — here is how to widen the search or start a new check.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Verification queue',
                        'heading' => 'No records match that filter — yet',
                        'summary' => 'Nothing in the queue matches your registry, risk band, and date range. Clear the filter to see every record, or start a fresh verification.',
                        'actions' => [
                            ['label' => 'Start a verification', 'url' => '#verification-flow', 'style' => 'primary'],
                            ['label' => 'View all coverage', 'url' => '#coverage-map', 'style' => 'secondary'],
                        ],
                    ],
                    $this->verificationFlowSection(
                        heading: 'How a record gets here',
                        summary: 'When a business completes these steps it lands in your queue, newest first, ready for review.',
                    ),
                    $this->featuresSection(
                        heading: 'While the queue is clear',
                        summary: 'Three things worth checking before the next batch of records arrives.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for a specific business?',
                        summary: 'Search by company number or name, or ask the trust team to run a one-off verification on your behalf.',
                    ),
                ],
            ],
        );
    }

    private function notFound(string $themeKey): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'not-found',
            name: self::BRAND . ' 404',
            title: 'Page not found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'That page could not be found',
                'A not-found page that routes visitors back into the verification and coverage paths.',
            ),
            renderData: [
                'summary' => 'That record or page is no longer here — here is the way back into the platform.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => '404',
                        'heading' => 'That page could not be found',
                        'summary' => 'The link is broken or the record has moved. Head back to verification, review coverage, or reach the trust team directly.',
                        'actions' => [
                            ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                            ['label' => 'View coverage', 'url' => '#coverage-map', 'style' => 'secondary'],
                        ],
                    ],
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell the trust team what you needed and we will point you to the right record or page.',
                    ),
                ],
            ],
            type: PageTypeEnum::NotFound,
            layout: LayoutEnum::System,
        );
    }

    private function cta(string $themeKey): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'cta',
            name: self::BRAND . ' Get Started',
            title: 'Adopt Veridian Trust — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into an onboarded compliance team',
                'A focused conversion page inviting regulated teams to adopt the verification platform.',
            ),
            renderData: [
                'summary' => 'Turn intent into an onboarded compliance team. Start with a scoped pilot on your real onboarding flow.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    [
                        'type' => 'hero',
                        'eyebrow' => 'Get started',
                        'heading' => 'Turn intent into an onboarded compliance team',
                        'summary' => 'Run a scoped pilot on your real onboarding flow in two weeks. Keep your reviewers, your rules, and your audit obligations — gain the trail.',
                        'actions' => [
                            ['label' => 'Book a pilot', 'url' => 'mailto:trust@veridian.example', 'style' => 'primary'],
                            ['label' => 'See the verification flow', 'url' => '#verification-flow', 'style' => 'secondary'],
                        ],
                    ],
                    $this->proofSection(
                        heading: 'Why regulated teams switch',
                        summary: 'The outcomes compliance leads cite after moving onboarding to Veridian Trust.',
                    ),
                    $this->metricCardsSection(),
                    $this->ctaSection(
                        heading: 'One conversation from a live pilot',
                        summary: 'Send us your onboarding requirements and licence type. We will come back within one business day with a pilot plan.',
                    ),
                ],
            ],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function complianceBadgesSection(): array
    {
        return [
            'type' => 'compliance-badges',
            'heading' => 'Certified to the standards your auditors expect',
            'summary' => 'Independently attested controls, renewed on schedule, with reports available under NDA.',
            'items' => [
                ['title' => 'SOC 2 Type II', 'summary' => 'Annual third-party audit of security, availability, and confidentiality controls.'],
                ['title' => 'ISO 27001', 'summary' => 'Certified information security management system across the platform.'],
                ['title' => 'GDPR & UK GDPR', 'summary' => 'Lawful processing, data minimisation, and EU/UK data residency options.'],
                ['title' => 'PCI DSS aligned', 'summary' => 'Cardholder-data handling scoped and segregated to PCI requirements.'],
                ['title' => 'FCA-ready KYB', 'summary' => 'Onboarding evidence mapped to UK money-laundering regulations.'],
                ['title' => 'AICPA audit trail', 'summary' => 'Immutable evidence logs structured for external assurance.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function verificationFlowSection(string $heading, string $summary): array
    {
        return [
            'type' => 'verification-flow',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => '1. Registry lookup', 'summary' => 'Match the business against the authoritative registry for its jurisdiction and pull live registration status.'],
                ['title' => '2. Ownership mapping', 'summary' => 'Resolve the ultimate beneficial owners and the control structure above them, down to the natural persons.'],
                ['title' => '3. Identity & screening', 'summary' => 'Verify each owner and run sanctions, PEP, and adverse-media screening against current watchlists.'],
                ['title' => '4. Risk scoring', 'summary' => 'Combine registry, ownership, and screening signals into a transparent, explainable risk band.'],
                ['title' => '5. Decision & sign-off', 'summary' => 'A reviewer approves, escalates, or rejects — and every action is timestamped and attributed.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function securityArchitectureSection(): array
    {
        return [
            'type' => 'security-architecture',
            'heading' => 'Security architecture your auditors can stand behind',
            'summary' => 'Layered controls and an immutable audit trail keep sensitive verification data defensible end to end.',
            'items' => [
                ['title' => 'Encryption everywhere', 'summary' => 'AES-256 at rest and TLS 1.3 in transit, with envelope encryption for owner and document data.'],
                ['title' => 'Least-privilege access', 'summary' => 'Role-based access, SSO, and enforced MFA. No standing access to production verification data.'],
                ['title' => 'Immutable audit trail', 'summary' => 'Every lookup, decision, and export is written to a tamper-evident, append-only log.'],
                ['title' => 'Data residency controls', 'summary' => 'Pin processing and storage to EU or UK regions to satisfy local obligations.'],
                ['title' => 'Continuous monitoring', 'summary' => 'Anomaly detection and 24/7 alerting on access patterns and integration health.'],
                ['title' => 'Tested resilience', 'summary' => 'Annual penetration tests and documented incident-response and recovery runbooks.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function coverageMapSection(): array
    {
        return [
            'type' => 'coverage-map',
            'heading' => 'Coverage across the jurisdictions you onboard in',
            'summary' => 'Authoritative registry and watchlist coverage spanning the markets regulated fintechs grow into.',
            'items' => [
                ['title' => 'United Kingdom', 'summary' => 'Companies House registration, PSC ownership data, and HMT sanctions screening.'],
                ['title' => 'European Union', 'summary' => 'National business registries plus EU consolidated sanctions and PEP lists.'],
                ['title' => 'United States', 'summary' => 'Secretary of State filings, OFAC SDN screening, and FinCEN beneficial-ownership data.'],
                ['title' => 'Asia-Pacific', 'summary' => 'Singapore ACRA, Australia ASIC, and Hong Kong Companies Registry coverage.'],
                ['title' => 'Watchlists', 'summary' => 'Continuously refreshed global sanctions, PEP, and adverse-media sources.'],
                ['title' => 'Data freshness', 'summary' => 'Records re-checked on a schedule, so a decision never rests on stale registry data.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function metricCardsSection(): array
    {
        return [
            'type' => 'metric-cards',
            'heading' => 'The numbers compliance teams report back',
            'summary' => 'Outcomes measured across regulated onboarding programmes running on the platform.',
            'items' => [
                ['title' => 'Under 30 seconds', 'summary' => 'Median time from lookup to a decision-ready verification record.'],
                ['title' => '99.9% uptime', 'summary' => 'Platform availability backed by a contractual service-level agreement.'],
                ['title' => '120+ registries', 'summary' => 'Authoritative business and ownership sources checked across jurisdictions.'],
                ['title' => '40% fewer reviews', 'summary' => 'Manual escalations cut by transparent, explainable risk scoring.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function coverageListingSection(): array
    {
        return [
            'type' => 'content-listing',
            'heading' => 'Sources checked on every verification',
            'summary' => 'A scannable directory of the registries and watchlists behind each business record.',
            'items' => [
                ['title' => 'Companies House (UK)', 'summary' => 'Live registration status, filing history, and persons-with-significant-control data.'],
                ['title' => 'OFAC SDN list (US)', 'summary' => 'Real-time sanctions screening against the US Treasury specially-designated-nationals list.'],
                ['title' => 'EU consolidated sanctions', 'summary' => 'Combined member-state and EU-level restrictive-measures screening.'],
                ['title' => 'ACRA (Singapore)', 'summary' => 'Business registration and directorship data from the Singapore registry.'],
                ['title' => 'Global PEP database', 'summary' => 'Politically-exposed-person screening with relationship and family-member coverage.'],
                ['title' => 'Adverse media', 'summary' => 'Structured negative-news screening across licensed and open sources.'],
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
                ['title' => 'Scope your obligations', 'summary' => 'We map your licence and markets to the exact KYB and screening checks you must run.'],
                ['title' => 'Design the onboarding flow', 'summary' => 'Decide where verification sits in your customer journey and how decisions are routed.'],
                ['title' => 'Plan the audit trail', 'summary' => 'Agree what evidence your reviewers and regulators need, and how it is exported.'],
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
                ['name' => 'Head of Financial Crime, challenger bank', 'quote' => 'We replaced three vendors and a spreadsheet with one record our auditors actually trust.'],
                ['name' => 'Compliance Lead, payments platform', 'quote' => 'Onboarding time dropped from days to minutes and the evidence trail is finally defensible.'],
                ['name' => 'MLRO, lending fintech', 'quote' => 'The explainable risk scoring is what got it past our second line and our regulator.'],
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
                ['label' => 'Book a pilot', 'url' => 'mailto:trust@veridian.example', 'style' => 'primary'],
                ['label' => 'See the verification flow', 'url' => '#verification-flow', 'style' => 'secondary'],
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
                ['label' => 'Verification', 'url' => '#verification-flow'],
                ['label' => 'Security', 'url' => '#security-architecture'],
                ['label' => 'Compliance', 'url' => '#compliance-badges'],
                ['label' => 'Coverage', 'url' => '#coverage-map'],
            ],
            'ctaLabel' => 'Book a pilot',
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
            'summary' => 'The verification layer for regulated fintech. Identity, KYB, ownership, and risk in one auditable platform.',
            'columns' => [
                [
                    'heading' => 'Platform',
                    'links' => [
                        ['label' => 'Verification flow', 'url' => '#verification-flow'],
                        ['label' => 'Security architecture', 'url' => '#security-architecture'],
                        ['label' => 'Coverage map', 'url' => '#coverage-map'],
                        ['label' => 'Verification record', 'url' => '#verification-flow'],
                    ],
                ],
                [
                    'heading' => 'Trust & compliance',
                    'links' => [
                        ['label' => 'Compliance & certifications', 'url' => '#compliance-badges'],
                        ['label' => 'SOC 2 & ISO 27001', 'url' => '#compliance-badges'],
                        ['label' => 'Data residency', 'url' => '#security-architecture'],
                        ['label' => 'Status', 'url' => '#security-architecture'],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'links' => [
                        ['label' => 'Contact the trust team', 'url' => '#cta'],
                        ['label' => 'trust@veridian.example', 'url' => 'mailto:trust@veridian.example'],
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
