<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\NightShift\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;
use Capell\ThemeStudio\NightShift\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\NightShift\NightShiftThemeServiceProvider;

/**
 * Complete, vertical-authentic demo content for the Night Shift theme.
 *
 * Night Shift is definition-only (see NightShiftThemeServiceProvider): it
 * registers no ThemeRenderer or section renderers, so every surface renders
 * through the shared `x-capell::layout` + layout-builder container pipeline
 * instead of the legacy section-rendering pipeline. Each surface below seeds
 * a `containers` payload (a 'main' container carrying a `page-content`
 * widget plus one bespoke Night Shift widget instance per
 * `changelog-integrations` / `workflow-rails` / `security-proof` entry in
 * that surface's {@see sectionCopy()}) plus the matching `widgets` blueprint
 * `ThemeDemoPageInstaller` dispatches through
 * `Capell\LayoutBuilder\Support\Creator\WidgetCreator` before writing the
 * containers onto the page's Layout. The page's own `content`/`title` (kept
 * verbatim per surface) is what the page-content widget renders — copy is
 * mined verbatim from the previous section-renderer so the demo still reads
 * like a real product-led SaaS site.
 *
 * WIDGET WIRING SCOPE (mirrors Liquid Glass's task "C2"): {@see sectionCopy()}
 * recovers the real, previously-authored per-surface copy that the
 * section-builder→layout-builder conversion stopped rendering. Of the nine
 * section types that copy covers, three — `changelog-integrations`,
 * `workflow-rails`, `security-proof` — are Night Shift's own bespoke,
 * signature sections (the theme's most distinctive product-UI shells) and
 * are wired below into real
 * `capell.widget.night-shift.{changelog-integrations,workflow-rails,security-proof}`
 * widget instances (one per occurrence per surface, via
 * `WidgetCreator::bespokeContentWidget()`, since each surface's copy differs
 * and a single shared `Widget` row per type would have one surface clobber
 * another's copy — the same singleton constraint `page-content` has, worked
 * around here with per-surface keys instead). `navigation` / `footer` are
 * wired separately, through `NightShiftThemeInterceptor`'s `header_file` /
 * `footer_file` Theme defaults, not through this class. `hero`,
 * `system-hero`, `agents-automation`, `planning-roadmap`, `proof`,
 * `content-listing`, `newsletter`, and `cta` are NOT wired to bespoke
 * widgets in this conversion — see
 * `NightShiftThemeServiceProvider::registerLayoutAreas()`'s "NOTE on scope"
 * for why (no per-theme override seam exists yet for the shared foundation
 * widget views those sections would otherwise map to). Their copy remains
 * intentionally inert in {@see sectionCopy()} for the same reason and is
 * preserved here for whenever that follow-up design decision lands.
 */
final class NightShiftDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Northwind System';

    private const string SOLUTIONS_EMAIL = 'solutions@northwind-system.example';

    /**
     * Bespoke Night Shift section types that {@see sectionCopy()} may
     * contain and that this class turns into real, seeded
     * `capell.widget.night-shift.*` widget instances (see class docblock).
     *
     * @var array<string, WidgetComponentEnum>
     */
    private const array BESPOKE_SECTION_WIDGETS = [
        'changelog-integrations' => WidgetComponentEnum::ChangelogIntegrations,
        'workflow-rails' => WidgetComponentEnum::WorkflowRails,
        'security-proof' => WidgetComponentEnum::SecurityProof,
    ];

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
     * The real, previously-authored per-surface section copy that the
     * section-builder→layout-builder conversion stopped rendering. Recovered
     * here verbatim, keyed by `surface`, in the same order it used to appear
     * in `render_data['sections']`.
     *
     * {@see bespokeSectionsForSurface()} reads this to seed real
     * `capell.widget.night-shift.{changelog-integrations,workflow-rails,security-proof}`
     * widgets (see class docblock "WIDGET WIRING SCOPE"); the `hero`,
     * `system-hero`, `agents-automation`, `planning-roadmap`, `proof`,
     * `content-listing`, `newsletter`, and `cta` entries this method also
     * returns are not consumed by anything yet, for the reason documented
     * there.
     *
     * @return list<array<string, mixed>>
     */
    public function sectionCopy(string $surface): array
    {
        $themeKey = NightShiftThemeServiceProvider::THEME_KEY;
        $media = ThemeDemoMedia::groupedForTheme($themeKey);

        return match ($surface) {
            'homepage' => [
                $this->heroSection(
                    eyebrow: 'Product operating system',
                    heading: 'The dark operating surface your product team runs on',
                    summary: 'Triage the inbox, move issues, ship the roadmap, and let automation handle the rest. Northwind System keeps every workflow in one fast, near-black workspace built for focus.',
                    media: $media,
                    mediaKey: 'hero',
                    mediaAlt: 'Northwind System workspace showing the inbox and triage view',
                    primaryUrl: '/theme-' . $themeKey . '-contact',
                    secondaryUrl: '#workflow-rails',
                    secondaryLabel: 'Explore workflows',
                ),
                $this->systemHeroSection(
                    heading: 'A workspace built around how teams actually ship',
                    summary: 'Three connected surfaces — work, planning, and automation — that keep engineers, PMs, and operators in the same context.',
                    media: $media,
                    mediaKey: 'detail',
                ),
                $this->workflowRailsSection(
                    heading: 'Workflows that move work without the busywork',
                    summary: 'Rails for triage, review, release, and incident response — each one a clear path through the system.',
                ),
                $this->agentsAutomationSection(
                    heading: 'Agents and automation that do the repetitive work',
                    summary: 'Set the rules once. Automation routes issues, drafts updates, and closes the loop while the team stays focused.',
                ),
                $this->planningRoadmapSection(
                    heading: 'Planning and roadmap that stay honest',
                    summary: 'Every roadmap item traces back to the work in flight, so the plan never drifts from reality.',
                    url: '#planning-roadmap',
                ),
                $this->changelogIntegrationsSection(
                    heading: 'Changelog and integrations, always in sync',
                    summary: 'Ship notes write themselves and every tool the team relies on is wired in.',
                    media: $media,
                ),
                $this->securityProofSection(
                    heading: 'Security and trust, proven not promised',
                    summary: 'SOC 2 Type II, SSO, audit logs, and granular roles — the controls security teams ask for first.',
                ),
                $this->proofSection(
                    heading: 'Teams ship more once the system carries the load',
                    summary: 'Outcomes from teams running on Northwind System.',
                ),
                $this->ctaSection(
                    heading: 'See your team in the system',
                    summary: 'Book a 30-minute walkthrough and we will map your workflows onto Northwind System live.',
                    primaryUrl: '/theme-' . $themeKey . '-contact',
                    secondaryUrl: '#workflow-rails',
                    secondaryLabel: 'Explore workflows',
                ),
            ],
            'directory' => [
                $this->heroSection(
                    eyebrow: 'Workflow library',
                    heading: 'Every workflow in the system, ready to run',
                    summary: 'Filter by surface — inbox, issues, roadmap, automation, or security — and drop a workflow into your workspace in a click.',
                    media: $media,
                    mediaKey: 'listing',
                    mediaAlt: 'Northwind System workflow library',
                    primaryUrl: '/theme-' . $themeKey . '-contact',
                ),
                $this->workflowRailsSection(
                    heading: 'Featured workflows',
                    summary: 'The rails teams reach for first.',
                ),
                $this->contentListingSection(
                    heading: 'More from the workflow library',
                    summary: 'Automation recipes, integration templates, and security playbooks.',
                    media: $media,
                ),
                $this->ctaSection(
                    heading: 'Found a workflow that fits?',
                    summary: 'Tell us how your team works today and we will wire the right workflows into a trial workspace.',
                    primaryUrl: '/theme-' . $themeKey . '-contact',
                    secondaryUrl: '#workflow-rails',
                    secondaryLabel: 'Back to featured workflows',
                ),
            ],
            'detail' => [
                $this->heroSection(
                    eyebrow: 'Customer story',
                    heading: 'Helios Robotics cut release lead time in half',
                    summary: 'A hardware-software team drowning in tool sprawl moved every workflow onto one dark surface — and watched the queue finally clear.',
                    media: $media,
                    mediaKey: 'detail',
                    mediaAlt: 'Helios Robotics team working in Northwind System',
                    primaryUrl: '/theme-' . $themeKey . '-contact',
                    secondaryUrl: '#system-hero',
                    secondaryLabel: 'See how it works',
                ),
                $this->systemHeroSection(
                    heading: 'What changed when the work moved into one system',
                    summary: 'Three surfaces replaced six tools, and the handoffs between them disappeared.',
                    media: $media,
                    mediaKey: 'proof',
                ),
                $this->agentsAutomationSection(
                    heading: 'Automation took the repetitive load off the team',
                    summary: 'Routing, triage, and status updates moved to automation so engineers stayed in flow.',
                ),
                $this->planningRoadmapSection(
                    heading: 'Planning the team could trust again',
                    summary: 'The roadmap finally matched the work in flight, week over week.',
                    url: '#planning-roadmap',
                ),
                $this->ctaSection(
                    heading: 'Want results like Helios?',
                    summary: 'Most teams start with a guided pilot on one workflow. Tell us yours and we will scope it.',
                    primaryUrl: '/theme-' . $themeKey . '-contact',
                    secondaryUrl: '#agents-automation',
                    secondaryLabel: 'See the automation',
                ),
            ],
            'contact' => [
                $this->heroSection(
                    eyebrow: 'Talk to us',
                    heading: 'Request a demo of the product system',
                    summary: 'No slide deck. A solutions engineer maps your inbox, issues, and roadmap onto Northwind System and answers the security questions up front. We reply within one working day.',
                    media: $media,
                    mediaKey: 'contact',
                    mediaAlt: 'Northwind System solutions team on a call',
                    primaryUrl: 'mailto:' . self::SOLUTIONS_EMAIL,
                    primaryLabel: 'Email solutions',
                    secondaryUrl: '#newsletter',
                    secondaryLabel: 'Join the newsletter',
                ),
                $this->systemHeroSection(
                    heading: 'What a demo actually covers',
                    summary: 'Three surfaces, your data shape, and the security review — no fluff.',
                    media: $media,
                    mediaKey: 'cta',
                ),
                $this->securityProofSection(
                    heading: 'The security questions, answered first',
                    summary: 'The controls and evidence security teams ask for before a trial begins.',
                ),
                $this->newsletterSection(
                    heading: 'Prefer to just follow along?',
                    summary: 'Join the list and get changelog notes and workflow ideas before your first call.',
                ),
            ],
            'empty' => [
                $this->heroSection(
                    eyebrow: 'Workflow library',
                    heading: 'No workflows match that filter — yet',
                    summary: 'Nothing is wired to this surface in your workspace. Clear the filter to see everything, or ask us to build the workflow you need.',
                    media: [],
                    mediaKey: 'hero',
                    mediaAlt: null,
                    primaryUrl: '#workflow-rails',
                    primaryLabel: 'View all workflows',
                    secondaryUrl: '/theme-' . $themeKey . '-contact',
                    secondaryLabel: 'Request a demo',
                    withMedia: false,
                ),
                $this->emptyStateListingCopy(),
                $this->workflowRailsSection(
                    heading: 'Popular workflows to start from',
                    summary: 'The rails most teams turn on in week one.',
                ),
                $this->ctaSection(
                    heading: 'Need a workflow that is not here?',
                    summary: 'Describe how your team works and we will build the workflow into your trial.',
                    primaryUrl: '/theme-' . $themeKey . '-contact',
                    secondaryUrl: '/',
                    secondaryLabel: 'Back to home',
                ),
            ],
            'not-found' => [
                $this->heroSection(
                    eyebrow: '404',
                    heading: 'This route is not in the system',
                    summary: 'The link is broken or the page has moved. Head back to the homepage, or talk to us about a demo.',
                    media: [],
                    mediaKey: 'hero',
                    mediaAlt: null,
                    primaryUrl: '/',
                    primaryLabel: 'Back to home',
                    secondaryUrl: '/theme-' . $themeKey . '-contact',
                    secondaryLabel: 'Request a demo',
                    withMedia: false,
                ),
                $this->ctaSection(
                    heading: 'Still looking for something?',
                    summary: 'Tell us what you needed and we will route you to the right surface.',
                    primaryUrl: '/theme-' . $themeKey . '-contact',
                    secondaryUrl: '/',
                    secondaryLabel: 'Back to home',
                ),
            ],
            'cta' => [
                $this->heroSection(
                    eyebrow: 'Get started',
                    heading: 'Run your whole team on one product system',
                    summary: 'Move triage, planning, automation, and release onto a single dark surface — and give security the evidence they need on day one.',
                    media: $media,
                    mediaKey: 'cta',
                    mediaAlt: 'Northwind System workspace ready to configure',
                    primaryUrl: '/theme-' . $themeKey . '-contact',
                    secondaryUrl: 'mailto:' . self::SOLUTIONS_EMAIL,
                    secondaryLabel: 'Email solutions',
                ),
                $this->securityProofSection(
                    heading: 'The controls that clear procurement',
                    summary: 'Everything security and IT review before a rollout.',
                ),
                $this->proofSection(
                    heading: 'Why teams move to Northwind System',
                    summary: 'The numbers behind the switch.',
                ),
                $this->ctaSection(
                    heading: 'One pilot away',
                    summary: 'Start a guided pilot on a single workflow and expand once the team feels the difference.',
                    primaryUrl: '/theme-' . $themeKey . '-contact',
                    secondaryUrl: 'mailto:' . self::SOLUTIONS_EMAIL,
                    secondaryLabel: 'Email solutions',
                ),
            ],
            default => [],
        };
    }

    /**
     * The `empty` surface's real, previously-authored inline content-listing
     * empty-state copy (distinct from the shared {@see contentListingSection()}
     * builder used by other surfaces) that the section-builder→layout-builder
     * conversion stopped rendering. Recovered here, verbatim.
     *
     * Still not consumed by anything: `content-listing` is one of the
     * sections this conversion deliberately did not wire to a bespoke
     * widget (see class docblock "WIDGET WIRING SCOPE"). Preserved here for
     * whenever a per-theme override seam for `capell.widget.page.latest`
     * exists.
     *
     * @return array<string, mixed>
     */
    public function emptyStateListingCopy(): array
    {
        return [
            'type' => 'content-listing',
            'heading' => 'Nothing to show on this surface',
            'summary' => 'When workflows are wired to this surface they appear here, most recent first.',
            'items' => [],
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
            title: self::BRAND . ' — The operating surface for product teams',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'One dark surface for the whole product team',
                'Northwind System brings inboxes, issues, roadmaps, automation, integrations, and security into a single workspace teams operate in all day.',
            ),
            renderData: [
                'summary' => 'Northwind System is the dark operating surface where product teams run workflows, automation, roadmaps, and security proof from one place.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            type: PageTypeEnum::Home,
            layout: LayoutEnum::Home,
            containers: $this->containers('homepage'),
            widgets: $this->widgets('homepage'),
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function directory(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'directory',
            name: self::BRAND . ' Workflows',
            title: 'Workflows — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every workflow in the system',
                'Browse the workflows, automations, and integrations teams run on Northwind System, organised by the surface they touch.',
            ),
            renderData: [
                'summary' => 'A scannable index of the workflows, automation runs, and integrations available across Northwind System.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            layout: LayoutEnum::Results,
            containers: $this->containers('directory'),
            widgets: $this->widgets('directory'),
        );
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     */
    private function detail(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Customer Story',
            title: 'Helios Robotics — Customer Story — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'How Helios Robotics cut release lead time in half',
                'A 60-person hardware-software team moved triage, planning, and release onto Northwind System and shipped twice as often.',
            ),
            renderData: [
                'summary' => 'How Helios Robotics consolidated triage, planning, and release onto Northwind System and halved release lead time.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            containers: $this->containers('detail'),
            widgets: $this->widgets('detail'),
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
            title: 'Request a demo — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Request a demo',
                'Tell us how your team works today and a solutions engineer will map your workflows onto Northwind System live.',
            ),
            renderData: [
                'summary' => 'Request a demo and a solutions engineer will walk your team through the system on your own workflows.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            layout: LayoutEnum::System,
            containers: $this->containers('contact'),
            widgets: $this->widgets('contact'),
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
            title: 'No matching workflows — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No workflows match that filter',
                'A graceful empty state for a filtered workflow library with no matching results.',
            ),
            renderData: [
                'summary' => 'No workflows match that filter yet — but the system can still point your team somewhere useful.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            containers: $this->containers('empty'),
            widgets: $this->widgets('empty'),
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
                'A not-found page that routes visitors back into the workflows and demo paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back into the system.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            type: PageTypeEnum::NotFound,
            layout: LayoutEnum::System,
            containers: $this->containers('not-found'),
            widgets: $this->widgets('not-found'),
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
            title: 'Run your team on the system — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Ready to run your team on one system?',
                'A focused conversion page inviting teams to start a guided pilot on Northwind System.',
            ),
            renderData: [
                'summary' => 'Ready to run your team on one system? Start a guided pilot on Northwind System.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            containers: $this->containers('cta'),
            widgets: $this->widgets('cta'),
        );
    }

    /**
     * The layout-builder container payload for a Night Shift demo surface: a
     * single 'main' container carrying the shared `page-content` widget
     * (so the seeded page's own title/content, kept per-surface above,
     * renders through `x-capell::layout`) followed by one bespoke Night
     * Shift widget instance per `changelog-integrations` / `workflow-rails`
     * / `security-proof` entry in this surface's {@see sectionCopy()}, in
     * the same order that copy used to render in `render_data['sections']`.
     *
     * @return array<string, array<string, mixed>>
     */
    private function containers(string $surface): array
    {
        $widgets = [
            ['widget_key' => 'page-content', 'occurrence' => 1],
        ];

        foreach ($this->bespokeSectionsForSurface($surface) as $bespokeSection) {
            $widgets[] = [
                'widget_key' => $bespokeSection['key'],
                'occurrence' => 1,
            ];
        }

        return [
            'main' => [
                'widgets' => $widgets,
            ],
        ];
    }

    /**
     * Widget blueprints `ThemeDemoPageInstaller::createDefinitionWidgets()`
     * dispatches through `WidgetCreator` before `containers()` above is
     * written onto the page's Layout, so every referenced `widget_key`
     * exists: the shared `page-content` widget, plus one
     * `WidgetCreator::bespokeContentWidget()` call per bespoke Night Shift
     * section this surface's {@see sectionCopy()} carries, each with its own
     * surface-scoped `key` and real seeded copy as `meta` (so, e.g.,
     * `homepage`'s `security-proof` widget and `contact`'s `security-proof`
     * widget are distinct `Widget` rows with distinct copy, not one shared
     * row that would have one surface's copy clobber another's).
     *
     * @return list<array{method: string, args?: array<array-key, mixed>}>
     */
    private function widgets(string $surface): array
    {
        $widgets = [
            ['method' => 'pageContentWidget'],
        ];

        foreach ($this->bespokeSectionsForSurface($surface) as $bespokeSection) {
            $widgets[] = [
                'method' => 'bespokeContentWidget',
                'args' => [
                    $bespokeSection['key'],
                    $bespokeSection['name'],
                    $bespokeSection['component'],
                    $bespokeSection['meta'],
                ],
            ];
        }

        return $widgets;
    }

    /**
     * The ordered list of this surface's `changelog-integrations` /
     * `workflow-rails` / `security-proof` section-copy entries (see
     * {@see BESPOKE_SECTION_WIDGETS}), each resolved to the widget key,
     * display name, component, and meta {@see containers()} and
     * {@see widgets()} need. Surface-scoped, 1-indexed-occurrence widget
     * keys (e.g. `night-shift-security-proof-homepage-1`) keep each
     * surface's copy on its own `Widget` row even though several surfaces
     * reuse the same section type.
     *
     * @return list<array{key: string, name: string, component: string, meta: array<string, mixed>}>
     */
    private function bespokeSectionsForSurface(string $surface): array
    {
        $bespokeSections = [];
        $occurrenceByType = [];

        foreach ($this->sectionCopy($surface) as $section) {
            $type = $section['type'] ?? null;

            if (! is_string($type) || ! array_key_exists($type, self::BESPOKE_SECTION_WIDGETS)) {
                continue;
            }

            $occurrenceByType[$type] = ($occurrenceByType[$type] ?? 0) + 1;
            $occurrence = $occurrenceByType[$type];
            $component = self::BESPOKE_SECTION_WIDGETS[$type];

            $bespokeSections[] = [
                'key' => sprintf('night-shift-%s-%s-%d', $type, $surface, $occurrence),
                'name' => sprintf('Night Shift %s (%s)', ucfirst($type), $surface),
                'component' => $component->value,
                'meta' => $section,
            ];
        }

        return $bespokeSections;
    }

    /**
     * @param  array<string, list<string>>  $media
     * @return array<string, mixed>
     */
    private function heroSection(
        string $eyebrow,
        string $heading,
        string $summary,
        array $media,
        string $mediaKey,
        ?string $mediaAlt,
        string $primaryUrl,
        string $secondaryUrl = '',
        string $secondaryLabel = '',
        string $primaryLabel = 'Request a demo',
        bool $withMedia = true,
    ): array {
        $actions = [
            ['label' => $primaryLabel, 'url' => $primaryUrl, 'style' => 'primary'],
        ];

        if ($secondaryUrl !== '' && $secondaryLabel !== '') {
            $actions[] = ['label' => $secondaryLabel, 'url' => $secondaryUrl, 'style' => 'secondary'];
        }

        $section = [
            'type' => 'hero',
            'eyebrow' => $eyebrow,
            'heading' => $heading,
            'summary' => $summary,
            'actions' => $actions,
        ];

        if ($withMedia) {
            $section['mediaUrl'] = $media[$mediaKey][0] ?? null;
            $section['mediaAlt'] = $mediaAlt;
        }

        return $section;
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function systemHeroSection(string $heading, string $summary, array $media, string $mediaKey): array
    {
        $image = $media[$mediaKey][0] ?? $media['hero'][0];

        return [
            'type' => 'system-hero',
            'heading' => $heading,
            'summary' => $summary,
            'image' => $image,
            'imageAlt' => 'Northwind System workspace surface',
            'items' => [
                ['title' => 'Work surface', 'summary' => 'Inbox, issues, and reviews in one triage view, so nothing waits in a second tool.'],
                ['title' => 'Planning surface', 'summary' => 'Roadmaps and cycles that draw straight from the work in flight, never a stale copy.'],
                ['title' => 'Automation surface', 'summary' => 'Rules and agents that route, draft, and close work the moment conditions are met.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function workflowRailsSection(string $heading, string $summary): array
    {
        return [
            'type' => 'workflow-rails',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Triage rail', 'summary' => 'New issues land, get scored, and route to the right owner before standup.'],
                ['title' => 'Review rail', 'summary' => 'Pull requests and design reviews queue with context and clear next actions.'],
                ['title' => 'Release rail', 'summary' => 'Cut a release, generate notes, and notify every channel from one action.'],
                ['title' => 'Incident rail', 'summary' => 'Declare, assign, and timeline an incident without leaving the workspace.'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function agentsAutomationSection(string $heading, string $summary): array
    {
        return [
            'type' => 'agents-automation',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'Triage agent', 'summary' => 'Reads incoming issues, sets priority and labels, and assigns the right team.', 'meta' => 'Runs on every new issue', 'care_note' => 'Reversible · audited'],
                ['title' => 'Standup digest', 'summary' => 'Drafts a daily summary of what moved, what stalled, and what needs a decision.', 'meta' => 'Scheduled · 09:00 local', 'care_note' => 'Posts to your channel'],
                ['title' => 'Release notes writer', 'summary' => 'Turns merged work into clean changelog entries the moment a release is cut.', 'meta' => 'Triggered on release', 'care_note' => 'Editable before publish'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function planningRoadmapSection(string $heading, string $summary, string $url): array
    {
        return [
            'type' => 'planning-roadmap',
            'heading' => $heading,
            'summary' => $summary,
            'url' => $url,
            'label' => 'View the full roadmap',
            'items' => [
                ['title' => 'Now', 'summary' => 'Inbox triage and review rails shipping to every workspace this cycle.'],
                ['title' => 'Next', 'summary' => 'Automation marketplace and shared agent templates for cross-team workflows.'],
                ['title' => 'Later', 'summary' => 'Custom security evidence packs and per-region data residency controls.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function changelogIntegrationsSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Linked to your repos', 'summary' => 'Two-way sync with GitHub and GitLab keeps issues, branches, and PRs in step.', 'meta' => 'v4.8'],
            ['title' => 'Wired into chat', 'summary' => 'Slack and Teams notifications fire from the same rules that move the work.', 'meta' => 'v4.7'],
            ['title' => 'Auto-written changelog', 'summary' => 'Every release ships with notes generated from the work that landed in it.', 'meta' => 'v4.6'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'image' => $image,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'changelog-integrations',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function securityProofSection(string $heading, string $summary): array
    {
        return [
            'type' => 'security-proof',
            'heading' => $heading,
            'summary' => $summary,
            'items' => [
                ['title' => 'SOC 2 Type II', 'summary' => 'Independently audited controls, with the report available under NDA.'],
                ['title' => 'SSO and SCIM', 'summary' => 'SAML single sign-on and automated provisioning across your identity provider.'],
                ['title' => 'Audit logs and roles', 'summary' => 'Every action is logged, and granular roles keep access scoped to the work.'],
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
                ['value' => '2x', 'label' => 'Faster release cadence after the first quarter on the system.'],
                ['value' => '6 to 1', 'label' => 'Tools consolidated into a single product surface, on average.'],
                ['value' => '1 day', 'label' => 'Reply to every demo request, from a real solutions engineer.'],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function contentListingSection(string $heading, string $summary, array $media): array
    {
        $images = array_values(array_unique(array_merge($media['listing'], $media['proof'])));

        $entries = [
            ['title' => 'Auto-route incoming issues', 'category' => 'Automation', 'summary' => 'Score, label, and assign new issues the moment they land in the inbox.'],
            ['title' => 'Sync issues with GitHub', 'category' => 'Integration', 'summary' => 'Keep branches, pull requests, and issues in step across both tools.'],
            ['title' => 'Security review playbook', 'category' => 'Security', 'summary' => 'A repeatable checklist that gathers the evidence procurement asks for.'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $image = $images[$index % max(count($images), 1)] ?? null;
            $items[] = [
                ...$entry,
                'url' => '#workflow-rails',
                'image' => $image,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'items' => $items,
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
            'email_label' => 'Email address',
            'button' => 'Subscribe',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function ctaSection(string $heading, string $summary, string $primaryUrl, string $secondaryUrl, string $secondaryLabel): array
    {
        return [
            'type' => 'cta',
            'heading' => $heading,
            'summary' => $summary,
            'url' => $primaryUrl,
            'label' => 'Request a demo',
            'actions' => [
                ['label' => 'Request a demo', 'url' => $primaryUrl, 'style' => 'primary'],
                ['label' => $secondaryLabel, 'url' => $secondaryUrl, 'style' => 'secondary'],
            ],
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
                ['label' => 'Workflows', 'url' => '/#workflow-rails'],
                ['label' => 'Automation', 'url' => '/#agents-automation'],
                ['label' => 'Roadmap', 'url' => '/#planning-roadmap'],
                ['label' => 'Security', 'url' => '/#security-proof'],
            ],
            'ctaLabel' => 'Request a demo',
            'ctaUrl' => '/theme-night-shift-contact',
            'consultationUrl' => '/theme-night-shift-contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        $columns = [
            [
                'heading' => 'Product',
                'title' => 'Product',
                'links' => [
                    ['label' => 'Workflows', 'url' => '/#workflow-rails'],
                    ['label' => 'Automation', 'url' => '/#agents-automation'],
                    ['label' => 'Roadmap', 'url' => '/#planning-roadmap'],
                    ['label' => 'Integrations', 'url' => '/#changelog-integrations'],
                ],
            ],
            [
                'heading' => 'Trust',
                'title' => 'Trust',
                'links' => [
                    ['label' => 'Security', 'url' => '/#security-proof'],
                    ['label' => 'Customer stories', 'url' => '/theme-night-shift-detail'],
                    ['label' => 'Changelog', 'url' => '/#changelog-integrations'],
                ],
            ],
            [
                'heading' => 'Company',
                'title' => 'Company',
                'links' => [
                    ['label' => 'Request a demo', 'url' => '/theme-night-shift-contact'],
                    ['label' => self::SOLUTIONS_EMAIL, 'url' => 'mailto:' . self::SOLUTIONS_EMAIL],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'The dark operating surface for product teams. Workflows, automation, roadmaps, and security in one place.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
