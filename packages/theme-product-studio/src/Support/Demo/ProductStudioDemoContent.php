<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ProductStudio\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;

/**
 * Complete, vertical-authentic demo content for the Product Studio theme.
 *
 * Each surface is seeded as an ordered `render_data['sections']` list so the
 * page adapter emits the theme's signature renderers (case-studies / tech-stack
 * / process / engagement-models / features) alongside the standard hero / proof
 * / cta — giving every surface a full product-engineering studio site rather
 * than the shared five-section skeleton. Copy is mined from the theme's own
 * screenshot renderer for Northbound Studio.
 */
final class ProductStudioDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Northbound Studio';

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
            title: self::BRAND . ' — Product & Engineering Studio',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A product studio teams can ship behind',
                'Northbound Studio designs and builds production software for founders and engineering teams who need work that ships, not slideware.',
            ),
            renderData: [
                'summary' => 'Northbound Studio is a product and engineering studio. We take ambitious software from architecture to launch and stay on through the hard last mile.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Product Studio',
                        heading: 'A product studio teams can ship behind',
                        summary: 'A structured engineering partner for case studies, tech stack, process, engagement models, and conversion-led journeys — built to take software from architecture to launch.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: 'Northbound Studio engineering team at work',
                    ),
                    $this->caseStudiesSection(
                        heading: 'Shipped work, not pitch decks',
                        summary: 'Structured case studies that pair outcomes with the stack behind them, so technical buyers can evaluate with confidence.',
                    ),
                    $this->techStackSection(
                        heading: 'A technical stack you can stand behind',
                        summary: 'The tools and platforms we reach for first, chosen for teams who will own the codebase long after launch.',
                    ),
                    $this->processSection(
                        heading: 'How an engagement runs',
                        summary: 'A predictable path from first call to production, with no surprises in scope or schedule.',
                    ),
                    $this->engagementModelsSection(
                        heading: 'Ways to work together',
                        summary: 'Pick the shape that fits the brief — a fixed discovery, a delivery sprint, or an embedded team.',
                    ),
                    $this->featuresSection(
                        heading: 'What you get from day one',
                        summary: 'The standards every Northbound engagement holds, whatever the engagement model.',
                    ),
                    $this->proofSection(
                        heading: 'Proof from the last two years',
                        summary: 'Outcomes from software we designed, built, and launched.',
                    ),
                    $this->ctaSection(
                        heading: 'Have a product to build?',
                        summary: 'Tell us what you are shipping. We reply to every enquiry within one working day with a clear next step.',
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
            name: self::BRAND . ' Case Studies',
            title: 'Case studies — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'A directory of shipped work built to be scanned',
                'Structured case-study cards keep the studio credible and legible, with the stack and the outcome side by side.',
            ),
            renderData: [
                'summary' => 'A directory of shipped work across platform engineering, product design, and migrations — built to be scanned and trusted.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Case studies',
                        heading: 'A directory of shipped work built to be scanned',
                        summary: 'Browse the studio archive. Each card pairs the business outcome with the engineering stack behind it.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: 'Northbound Studio case-study archive',
                    ),
                    $this->caseStudiesSection(
                        heading: 'Featured engagements',
                        summary: 'The projects we keep coming back to, across fintech, climate, and developer tooling.',
                    ),
                    $this->contentListingSection(
                        heading: 'More from the archive',
                        summary: 'Smaller deliveries, audits, and platform rescues.',
                    ),
                    $this->ctaSection(
                        heading: 'See work that fits your brief?',
                        summary: 'Tell us what you are planning and we will send the most relevant case studies, with context on scope and timing.',
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
            name: self::BRAND . ' Case Study',
            title: 'Harbor Ledger — Case Study — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'A case study that reads with engineering rigour',
                'How Northbound rebuilt the Harbor Ledger payments platform and cut settlement latency by 70 percent.',
            ),
            renderData: [
                'summary' => 'A single project view that pairs outcomes and stack so prospective clients can evaluate with engineering rigour.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Case study',
                        heading: 'Harbor Ledger — settlement at a tenth of the latency',
                        summary: 'A payments platform buckling under growth. We re-architected the settlement engine and shipped it without a maintenance window.',
                        media: $media,
                        mediaKey: 'detail',
                        mediaAlt: 'Harbor Ledger platform work',
                    ),
                    $this->caseStudiesSection(
                        heading: 'What we shipped',
                        summary: 'The work broken into the outcomes that mattered to the Harbor Ledger team.',
                    ),
                    $this->techStackSection(
                        heading: 'The stack behind it',
                        summary: 'Everything chosen so the in-house team could own and extend it after handover.',
                    ),
                    $this->processSection(
                        heading: 'How the engagement ran',
                        summary: 'Sixteen weeks from architecture review to a clean production cut-over.',
                    ),
                    $this->ctaSection(
                        heading: 'Want results like these?',
                        summary: 'Most engagements start with a short paid discovery. Tell us about the build and we will map a path.',
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
            title: 'Start a project — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Start a project through one confident path',
                'Tell us what you are shipping. We scope every engagement directly with the engineers who do the work.',
            ),
            renderData: [
                'summary' => 'Tell us what you are building. We scope every engagement directly with the senior team — no account managers in between.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Contact',
                        heading: 'Start a project through one confident path',
                        summary: 'Email hello@northbound.example or use the details below — we reply within one working day with a clear next step.',
                        media: $media,
                        mediaKey: 'contact',
                        mediaAlt: 'The Northbound Studio workspace',
                    ),
                    $this->engagementModelsSection(
                        heading: 'How we can help',
                        summary: 'Pick the shape that fits. Most projects start with a short paid discovery.',
                    ),
                    $this->featuresSection(
                        heading: 'What to expect',
                        summary: 'The standards we hold from the first week of any engagement.',
                    ),
                    $this->ctaSection(
                        heading: 'Prefer to talk it through?',
                        summary: 'Book a 30-minute intro call and we will tell you honestly whether we are the right studio for the build.',
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
                'An empty listing state that stays structured and credible while the studio prepares its content.',
            ),
            renderData: [
                'summary' => 'No case studies match that filter yet — but the studio can still point you somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: 'Case studies',
                        heading: 'No case studies match that filter — yet',
                        summary: 'We have not published work in this category. Clear the filter to see everything, or tell us what you are looking for.',
                        media: $media,
                        mediaKey: 'listing',
                        mediaAlt: 'Northbound Studio case-study archive',
                    ),
                    [
                        'type' => 'content-listing',
                        'heading' => 'Nothing to show here',
                        'summary' => 'When case studies land in this category they will appear here, newest first.',
                        'items' => [],
                    ],
                    $this->engagementModelsSection(
                        heading: 'While you are here',
                        summary: 'The three ways teams most often start with Northbound.',
                    ),
                    $this->ctaSection(
                        heading: 'Looking for something specific?',
                        summary: 'Tell us the brief and we will send relevant work from the archive.',
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
                'That page could not be found',
                'A not-found page that keeps the studio credible and routes visitors back into the engagement journey.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the studio.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: '404',
                        heading: 'That page could not be found',
                        summary: 'The link is broken or the page has moved. Head back to the case studies, or start a conversation with the studio.',
                        media: $media,
                        mediaKey: 'hero',
                        mediaAlt: 'Northbound Studio',
                    ),
                    $this->ctaSection(
                        heading: 'Still looking for something?',
                        summary: 'Tell us what you needed and we will point you to the right place.',
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
            name: self::BRAND . ' Enquire',
            title: 'Work with us — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Turn intent into a scoped engagement',
                'A conversion-focused page that keeps the path to starting a project direct and credible.',
            ),
            renderData: [
                'summary' => 'Turn intent into a scoped engagement. Start a project with the studio.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
                'sections' => [
                    $this->heroSection(
                        eyebrow: "Let's build it",
                        heading: 'Turn intent into a scoped engagement',
                        summary: 'Whether it is a greenfield product or a platform rescue, we bring the same senior team and the same standard.',
                        media: $media,
                        mediaKey: 'cta',
                        mediaAlt: 'Northbound Studio team',
                    ),
                    $this->engagementModelsSection(
                        heading: 'Choose how to start',
                        summary: 'Three clear ways to work together, each with a fixed first step.',
                    ),
                    $this->proofSection(
                        heading: 'Why teams choose Northbound',
                        summary: 'The numbers behind the work.',
                    ),
                    $this->ctaSection(
                        heading: 'One brief away',
                        summary: 'Send the project over and we will come back within one working day with next steps.',
                    ),
                ],
            ],
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function heroSection(
        string $eyebrow,
        string $heading,
        string $summary,
        array $media,
        string $mediaKey,
        string $mediaAlt,
    ): array {
        return [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'actions' => [
                ['label' => 'Start a project', 'url' => '#contact', 'style' => 'primary'],
                ['label' => 'View case studies', 'url' => '#case-studies', 'style' => 'secondary'],
            ],
            'mediaUrl' => $media[$mediaKey][0] ?? $media['hero'][0],
            'mediaAlt' => $mediaAlt,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function caseStudiesSection(string $heading, string $summary): array
    {
        return [
            'type' => 'case-studies',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Harbor Ledger — settlement re-architecture',
                    'summary' => 'Cut settlement latency by 70 percent and shipped the cut-over with zero downtime for a high-volume payments platform.',
                ],
                [
                    'title' => 'Verdant — carbon accounting platform',
                    'summary' => 'A greenfield data platform that ingests utility feeds and reports auditable emissions for enterprise climate teams.',
                ],
                [
                    'title' => 'Switchboard — developer tooling rescue',
                    'summary' => 'Stabilised a flaky CI platform, halved build times, and handed a clean codebase back to the in-house team.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function techStackSection(string $heading, string $summary): array
    {
        return [
            'type' => 'tech-stack',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Application & API',
                    'summary' => 'Laravel and TypeScript services with typed contracts, tested at the boundary and built for teams to extend.',
                ],
                [
                    'title' => 'Data & infrastructure',
                    'summary' => 'PostgreSQL, Redis, and event streaming on Terraform-managed infrastructure your team can read and own.',
                ],
                [
                    'title' => 'Delivery & observability',
                    'summary' => 'CI pipelines, automated migrations, and tracing wired in from day one so production is never a black box.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function processSection(string $heading, string $summary): array
    {
        return [
            'type' => 'process',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => '01 — Discovery',
                    'summary' => 'A short paid sprint to map the architecture, risks, and a costed plan you keep whether or not we build it.',
                ],
                [
                    'title' => '02 — Delivery',
                    'summary' => 'Weekly shipping against a shared backlog, with working software in your hands from the first fortnight.',
                ],
                [
                    'title' => '03 — Handover',
                    'summary' => 'Documentation, runbooks, and pairing so your team owns the system the day we step back.',
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function engagementModelsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'engagement-models',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                [
                    'title' => 'Discovery engagement',
                    'summary' => 'Two to three weeks to de-risk the build: architecture, estimate, and a plan you own outright.',
                ],
                [
                    'title' => 'Delivery sprint',
                    'summary' => 'A senior squad shipping production software against a fixed scope and a fixed timeline.',
                ],
                [
                    'title' => 'Embedded team',
                    'summary' => 'Engineers who join your team, work in your tools, and lift the standard of everything around them.',
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
                    'title' => 'Senior engineers only',
                    'summary' => 'No juniors billed at senior rates. The people who scope the work are the people who build it.',
                ],
                [
                    'title' => 'Tested, owned code',
                    'summary' => 'Typed, tested, and documented so your team inherits a codebase, not a liability.',
                ],
                [
                    'title' => 'Production from week one',
                    'summary' => 'We deploy early and often, so progress is something you can run, not just review in a deck.',
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
                    'title' => '16 wks',
                    'summary' => 'Typical product-to-production timeline across recent engagements.',
                ],
                [
                    'title' => '30+',
                    'summary' => 'Production systems shipped across fintech, climate, and developer tooling since 2019.',
                ],
                [
                    'title' => '1 day',
                    'summary' => 'You hear back from a real engineer on the team, fast, on every enquiry.',
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
                    'title' => 'Meridian — API audit',
                    'summary' => 'A two-week review of a public API ahead of an enterprise rollout, with a prioritised remediation plan.',
                ],
                [
                    'title' => 'Kindle Labs — search rebuild',
                    'summary' => 'Replaced a brittle search layer with a typed, observable service handling ten times the query volume.',
                ],
                [
                    'title' => 'Glasshouse — migration',
                    'summary' => 'Moved a monolith onto managed infrastructure with zero data loss and no customer-facing downtime.',
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
                ['label' => 'Start a project', 'url' => '#contact', 'style' => 'primary'],
                ['label' => 'View case studies', 'url' => '#case-studies', 'style' => 'secondary'],
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
                ['label' => 'Case studies', 'url' => '#case-studies'],
                ['label' => 'Tech stack', 'url' => '#tech-stack'],
                ['label' => 'Engagement models', 'url' => '#engagement-models'],
                ['label' => 'Process', 'url' => '#process'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Start a project',
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
            'summary' => 'A product and engineering studio. We take ambitious software from architecture to launch.',
            'columns' => [
                [
                    'heading' => 'Studio',
                    'links' => [
                        ['label' => 'Case studies', 'url' => '#case-studies'],
                        ['label' => 'Process', 'url' => '#process'],
                        ['label' => 'Engagement models', 'url' => '#engagement-models'],
                    ],
                ],
                [
                    'heading' => 'Capabilities',
                    'links' => [
                        ['label' => 'Tech stack', 'url' => '#tech-stack'],
                        ['label' => 'Platform engineering', 'url' => '#case-studies'],
                        ['label' => 'Product design', 'url' => '#case-studies'],
                    ],
                ],
                [
                    'heading' => 'Connect',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'hello@northbound.example', 'url' => 'mailto:hello@northbound.example'],
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
