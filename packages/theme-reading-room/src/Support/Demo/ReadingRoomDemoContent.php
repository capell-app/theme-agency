<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ReadingRoom\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;
use Capell\ThemeStudio\ReadingRoom\Enums\WidgetComponentEnum;
use InvalidArgumentException;

/**
 * Complete, vertical-authentic demo content for the Reading Room theme —
 * "Fieldnote Docs", a fictional documentation/knowledge-base platform for
 * developer tools.
 *
 * Reading Room is layout-native (see `ReadingRoomThemeServiceProvider`): it
 * registers no `ThemeRenderer` or section renderers, so every surface seeds
 * layout-builder `containers` (a `main` container carrying the shared
 * `page-content` widget plus this surface's bespoke Reading Room widget
 * instances) instead of a legacy `render_data['sections']` list — mirrors
 * `Capell\ThemeStudio\NightShift\Support\Demo\NightShiftDemoContent` exactly.
 *
 * Differentiation note (programme doc, §E "Five-way conversion
 * differentiation"): Reading Room's copy is deliberately calm — minimized
 * CTAs, scanability, and a serif-option body — never night-shift's dense
 * mono-labelled SaaS urgency, call-out's state-driven green/amber/red
 * urgency, or main-stage's poster-bold countdown FOMO. Every surface below
 * opens with the search-first hero, not a hard sales pitch, and even the
 * "cta" surface reads as a version-changelog page, not a checkout funnel.
 */
final class ReadingRoomDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Fieldnote Docs';

    private const string SUPPORT_EMAIL = 'docs@fieldnote-docs.example';

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
     * @param  array<string, list<string>>  $media
     */
    private function homepage(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'homepage',
            name: self::BRAND . ' Home',
            title: self::BRAND . ' — Documentation that reads like a well-kept field notebook',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'Documentation your team actually reads',
                'Fieldnote Docs turns a scattered wiki into a scannable reference: a doc tree, a real search box, versioned changelogs, and an API reference that copies cleanly into your editor.',
            ),
            renderData: [
                'summary' => 'Fieldnote Docs is a documentation and knowledge-base platform built for scanability — a doc-tree sidebar, in-article contents, search-first landing, and a clean API reference.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
            ],
            type: PageTypeEnum::Home,
            layout: LayoutEnum::Home,
            containers: $this->containers('homepage'),
            widgets: $this->widgets('homepage'),
        );
    }

    /**
     * @param  array<string, list<string>>  $media
     */
    private function directory(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'directory',
            name: self::BRAND . ' Guides',
            title: 'Guides — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Every guide, organised the way you actually look for it',
                'Browse the Fieldnote Docs guide library by product area, or jump straight to the API reference and the version changelog.',
            ),
            renderData: [
                'summary' => 'The full Fieldnote Docs guide index: onboarding, the API reference, and every release note in one scannable directory.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
            ],
            layout: LayoutEnum::Results,
            containers: $this->containers('directory'),
            widgets: $this->widgets('directory'),
        );
    }

    /**
     * @param  array<string, list<string>>  $media
     */
    private function detail(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'detail',
            name: self::BRAND . ' Webhooks Guide',
            title: 'Configuring webhooks — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Configuring webhooks',
                'How to register an endpoint, verify the signing secret, and replay a failed delivery — with the full payload parameter reference alongside the article.',
            ),
            renderData: [
                'summary' => 'A worked example doc page: webhook configuration with an in-article table of contents, admonitions, and the endpoint parameter reference.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
            ],
            containers: $this->containers('detail'),
            widgets: $this->widgets('detail'),
        );
    }

    /**
     * @param  array<string, list<string>>  $media
     */
    private function contact(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'contact',
            name: self::BRAND . ' Support',
            title: 'Ask the docs team — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'Can\'t find it in the docs?',
                'Send the docs team a question and we will either point you to the right page or write the page that is missing.',
            ),
            renderData: [
                'summary' => 'A quiet support surface for questions the docs don\'t yet answer, plus the same search box every other Fieldnote Docs page carries.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
            ],
            layout: LayoutEnum::System,
            containers: $this->containers('contact'),
            widgets: $this->widgets('contact'),
        );
    }

    /**
     * @param  array<string, list<string>>  $media
     */
    private function empty(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'empty',
            name: self::BRAND . ' No Results',
            title: 'No matching pages — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No pages match that search',
                'Nothing in the guide library matched that query yet. Try a broader term, or browse the doc tree instead.',
            ),
            renderData: [
                'summary' => 'A graceful empty search-results state that still surfaces the doc tree and the most popular guides.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
            ],
            containers: $this->containers('empty'),
            widgets: $this->widgets('empty'),
        );
    }

    /**
     * @param  array<string, list<string>>  $media
     */
    private function notFound(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'not-found',
            name: self::BRAND . ' 404',
            title: 'Page not found — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-404',
            content: $this->prose(
                'This page moved or never existed',
                'The link is out of date. Search the docs, or head back to the guide library.',
            ),
            renderData: [
                'summary' => 'A quiet not-found page that routes visitors back into search rather than a hard sales pitch.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
            ],
            type: PageTypeEnum::NotFound,
            layout: LayoutEnum::System,
            containers: $this->containers('not-found'),
            widgets: $this->widgets('not-found'),
        );
    }

    /**
     * @param  array<string, list<string>>  $media
     */
    private function cta(string $themeKey, array $media): ThemeDemoPageDefinition
    {
        return new ThemeDemoPageDefinition(
            surface: 'cta',
            name: self::BRAND . ' Latest Release',
            title: 'What\'s new in Fieldnote Docs — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'What changed in this release',
                'A focused look at the newest version\'s breaking change, the minor additions, and where to read the full migration note.',
            ),
            renderData: [
                'summary' => 'A focused version-changelog surface highlighting the newest release, with the breaking change flagged distinctly from minor and patch entries.',
                'navigation' => $this->navigation($themeKey),
                'footer' => $this->footer($themeKey),
            ],
            containers: $this->containers('cta'),
            widgets: $this->widgets('cta'),
        );
    }

    /**
     * The layout-builder container payload for a Reading Room demo surface:
     * a single 'main' container carrying the shared `page-content` widget
     * followed by this surface's bespoke Reading Room widget instances, in
     * the same order {@see bespokeWidgetsForSurface()} returns them.
     *
     * @return array<string, array<string, mixed>>
     */
    private function containers(string $surface): array
    {
        $widgets = [
            ['widget_key' => 'page-content', 'occurrence' => 1],
        ];

        foreach ($this->bespokeWidgetsForSurface($surface) as $bespokeWidget) {
            $widgets[] = [
                'widget_key' => $bespokeWidget['key'],
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
     * `WidgetCreator::bespokeContentWidget()` call per bespoke Reading Room
     * widget this surface carries, each with its own surface-scoped `key`
     * and real seeded copy as `meta`.
     *
     * @return list<array{method: string, args?: array<array-key, mixed>}>
     */
    private function widgets(string $surface): array
    {
        $widgets = [
            ['method' => 'pageContentWidget'],
        ];

        foreach ($this->bespokeWidgetsForSurface($surface) as $bespokeWidget) {
            $widgets[] = [
                'method' => 'bespokeContentWidget',
                'args' => [
                    $bespokeWidget['key'],
                    $bespokeWidget['name'],
                    $bespokeWidget['component'],
                    $bespokeWidget['meta'],
                ],
            ];
        }

        return $widgets;
    }

    /**
     * The ordered list of bespoke Reading Room widget instances a demo
     * surface seeds, each resolved to the widget key, display name,
     * component, and meta {@see containers()} and {@see widgets()} need.
     * Surface-scoped, 1-indexed-occurrence widget keys (e.g.
     * `reading-room-search-spotlight-hero-homepage-1`) keep each surface's
     * copy on its own `Widget` row even though several surfaces reuse the
     * same widget type.
     *
     * @return list<array{key: string, name: string, component: string, meta: array<string, mixed>}>
     */
    private function bespokeWidgetsForSurface(string $surface): array
    {
        $bySurface = $this->surfaceWidgetMeta($surface);
        $occurrenceByType = [];
        $bespokeWidgets = [];

        foreach ($bySurface as $type => $meta) {
            $occurrenceByType[$type] = ($occurrenceByType[$type] ?? 0) + 1;
            $occurrence = $occurrenceByType[$type];
            $component = $this->componentForType($type);

            $bespokeWidgets[] = [
                'key' => sprintf('reading-room-%s-%s-%d', $type, $surface, $occurrence),
                'name' => sprintf('Reading Room %s (%s)', ucfirst(str_replace('-', ' ', $type)), $surface),
                'component' => $component->value,
                'meta' => $meta,
            ];
        }

        return $bespokeWidgets;
    }

    private function componentForType(string $type): WidgetComponentEnum
    {
        return match ($type) {
            'doc-tree-sidebar' => WidgetComponentEnum::DocTreeSidebar,
            'in-article-toc-scroll-spy' => WidgetComponentEnum::InArticleTocScrollSpy,
            'search-spotlight-hero' => WidgetComponentEnum::SearchSpotlightHero,
            'version-changelog-surfaces' => WidgetComponentEnum::VersionChangelogSurfaces,
            'api-reference-parameter-table' => WidgetComponentEnum::ApiReferenceParameterTable,
            'callout-admonition-system' => WidgetComponentEnum::CalloutAdmonitionSystem,
            'feedback-footer' => WidgetComponentEnum::FeedbackFooter,
            default => throw new InvalidArgumentException("Unknown Reading Room widget type [{$type}]."),
        };
    }

    /**
     * Ordered `[widgetType => meta]` map per surface — the single source of
     * truth {@see bespokeWidgetsForSurface()} turns into keyed widget
     * instances. Each surface below is intentionally individual copy for
     * Fieldnote Docs, not a repeated skeleton.
     *
     * @return array<string, array<string, mixed>>
     */
    private function surfaceWidgetMeta(string $surface): array
    {
        return match ($surface) {
            'homepage' => [
                'search-spotlight-hero' => $this->searchSpotlightHero(
                    variant: 'full',
                    heading: 'Find what you need, fast',
                    summary: 'Search the whole Fieldnote Docs library, or jump straight into the guide, API reference, or changelog you were already looking for.',
                    quickLinks: [
                        ['tag' => 'Guide', 'label' => 'Getting started with Fieldnote Docs', 'url' => '#getting-started'],
                        ['tag' => 'Guide', 'label' => 'Configuring webhooks', 'url' => '#webhooks'],
                        ['tag' => 'API', 'label' => 'Endpoint reference', 'url' => '#api-reference'],
                        ['tag' => 'Changelog', 'label' => 'What changed in v4.2', 'url' => '#changelog'],
                    ],
                ),
                'doc-tree-sidebar' => $this->docTreeSidebar(variant: 'nested'),
                'callout-admonition-system' => $this->calloutAdmonitionSystem(
                    variant: 'block',
                    admonitions: [
                        ['kind' => 'tip', 'body' => 'Search accepts partial matches, so "webhook" finds "Configuring webhooks" and "Webhook signing secrets" alike.'],
                        ['kind' => 'note', 'body' => 'Every guide below is versioned — switch the version selector in the guide itself to read docs for an older release.'],
                    ],
                ),
                'version-changelog-surfaces' => $this->versionChangelogSurfaces(
                    variant: 'rows',
                    heading: 'Recent releases',
                    summary: 'The last few entries from the full changelog — breaking changes are always flagged before minor and patch notes.',
                    entries: [
                        ['version' => 'v4.2.0', 'changeType' => 'breaking', 'title' => 'Webhook payloads now nest `data` under `event`', 'summary' => 'Update any signature-verification code that reads the payload root directly.'],
                        ['version' => 'v4.1.0', 'changeType' => 'minor', 'title' => 'Added the endpoint parameter reference', 'summary' => 'Every documented endpoint now lists its parameters with type badges and copyable request samples.'],
                        ['version' => 'v4.0.3', 'changeType' => 'patch', 'title' => 'Fixed a broken anchor link in the onboarding guide', 'summary' => 'The "Next steps" link at the bottom of the getting-started guide now resolves correctly.'],
                    ],
                ),
            ],
            'directory' => [
                'search-spotlight-hero' => $this->searchSpotlightHero(
                    variant: 'compact',
                    heading: 'Browse every guide',
                    summary: 'Search across guides, the API reference, and the changelog.',
                    quickLinks: [
                        ['tag' => 'Guide', 'label' => 'Getting started', 'url' => '#getting-started'],
                        ['tag' => 'Guide', 'label' => 'Authentication', 'url' => '#authentication'],
                        ['tag' => 'Guide', 'label' => 'Configuring webhooks', 'url' => '#webhooks'],
                        ['tag' => 'API', 'label' => 'Endpoint reference', 'url' => '#api-reference'],
                        ['tag' => 'API', 'label' => 'Rate limits', 'url' => '#rate-limits'],
                        ['tag' => 'Changelog', 'label' => 'Full release history', 'url' => '#changelog'],
                    ],
                ),
                'doc-tree-sidebar' => $this->docTreeSidebar(variant: 'flat-groups'),
            ],
            'detail' => [
                'search-spotlight-hero' => $this->searchSpotlightHero(
                    variant: 'compact',
                    heading: 'Configuring webhooks',
                    summary: 'How to register an endpoint, verify the signing secret, and replay a failed delivery.',
                    quickLinks: [
                        ['tag' => 'Guide', 'label' => 'Authentication', 'url' => '#authentication'],
                        ['tag' => 'API', 'label' => 'Endpoint reference', 'url' => '#api-reference'],
                    ],
                ),
                'doc-tree-sidebar' => $this->docTreeSidebar(variant: 'nested'),
                'in-article-toc-scroll-spy' => [
                    'variant' => 'rail',
                    'heading' => 'On this page',
                    'items' => [
                        ['label' => 'Register an endpoint', 'anchor' => 'register-an-endpoint'],
                        ['label' => 'Verify the signing secret', 'anchor' => 'verify-the-signing-secret'],
                        ['label' => 'Replay a failed delivery', 'anchor' => 'replay-a-failed-delivery'],
                        ['label' => 'Parameter reference', 'anchor' => 'parameter-reference'],
                    ],
                ],
                'callout-admonition-system' => $this->calloutAdmonitionSystem(
                    variant: 'block',
                    admonitions: [
                        ['kind' => 'warning', 'body' => 'Webhook payloads are signed with your workspace secret — always verify the signature before trusting the payload body.'],
                        ['kind' => 'danger', 'body' => 'As of v4.2.0, the payload nests `data` under `event`. Code written against v4.1 or earlier will read the wrong path until updated.'],
                        ['kind' => 'tip', 'body' => 'Use the replay button in the endpoint dashboard to resend any delivery from the last 30 days without changing its payload.'],
                    ],
                ),
                'api-reference-parameter-table' => $this->apiReferenceParameterTable(
                    variant: 'table',
                    heading: 'Webhook delivery parameters',
                    summary: 'Every field the delivered payload carries, in delivery order.',
                    parameters: [
                        ['name' => 'event.type', 'type' => 'string', 'required' => true, 'description' => 'The event name, e.g. "page.published" or "comment.created".'],
                        ['name' => 'event.data', 'type' => 'object', 'required' => true, 'description' => 'The event payload body, shaped per event type.'],
                        ['name' => 'event.occurred_at', 'type' => 'string (ISO 8601)', 'required' => true, 'description' => 'When the event occurred on the server, not when it was delivered.'],
                        ['name' => 'signature', 'type' => 'string', 'required' => true, 'description' => 'HMAC-SHA256 signature of the raw request body, using your endpoint\'s signing secret.'],
                        ['name' => 'delivery_id', 'type' => 'string', 'required' => false, 'description' => 'A unique id for this delivery attempt, useful for de-duplicating retries.'],
                    ],
                    codeSample: "{\n  \"event\": {\n    \"type\": \"page.published\",\n    \"data\": { \"id\": \"pg_492\", \"slug\": \"webhooks\" },\n    \"occurred_at\": \"2026-06-30T09:12:00Z\"\n  },\n  \"signature\": \"sha256=…\",\n  \"delivery_id\": \"dlv_88f1\"\n}",
                ),
                'feedback-footer' => $this->feedbackFooter(
                    variant: 'survey',
                    reasons: [
                        ['label' => 'Missing an example'],
                        ['label' => 'Out of date'],
                        ['label' => 'Hard to follow'],
                    ],
                ),
            ],
            'contact' => [
                'search-spotlight-hero' => $this->searchSpotlightHero(
                    variant: 'compact',
                    heading: 'Ask the docs team',
                    summary: 'Search first — most answers are already written down.',
                    quickLinks: [
                        ['tag' => 'Guide', 'label' => 'Getting started', 'url' => '#getting-started'],
                        ['tag' => 'API', 'label' => 'Endpoint reference', 'url' => '#api-reference'],
                    ],
                ),
                'callout-admonition-system' => $this->calloutAdmonitionSystem(
                    variant: 'inline',
                    admonitions: [
                        ['kind' => 'note', 'body' => 'We read every message within one working day and reply from ' . self::SUPPORT_EMAIL . '.'],
                    ],
                ),
                'feedback-footer' => $this->feedbackFooter(variant: 'simple'),
            ],
            'empty' => [
                'search-spotlight-hero' => $this->searchSpotlightHero(
                    variant: 'full',
                    heading: 'No pages match that search',
                    summary: 'Try a broader term, or start from the doc tree instead.',
                    quickLinks: [
                        ['tag' => 'Guide', 'label' => 'Getting started', 'url' => '#getting-started'],
                        ['tag' => 'Guide', 'label' => 'Authentication', 'url' => '#authentication'],
                        ['tag' => 'API', 'label' => 'Endpoint reference', 'url' => '#api-reference'],
                    ],
                ),
                'doc-tree-sidebar' => $this->docTreeSidebar(variant: 'nested'),
            ],
            'not-found' => [
                'search-spotlight-hero' => $this->searchSpotlightHero(
                    variant: 'compact',
                    heading: 'This page moved or never existed',
                    summary: 'Search the docs, or head back to the guide library.',
                    quickLinks: [
                        ['tag' => 'Guide', 'label' => 'Getting started', 'url' => '#getting-started'],
                        ['tag' => 'Changelog', 'label' => 'Full release history', 'url' => '#changelog'],
                    ],
                ),
            ],
            'cta' => [
                'version-changelog-surfaces' => $this->versionChangelogSurfaces(
                    variant: 'timeline',
                    heading: 'What changed in v4.2',
                    summary: 'The newest release, with the breaking change first.',
                    entries: [
                        ['version' => 'v4.2.0', 'changeType' => 'breaking', 'title' => 'Webhook payloads now nest `data` under `event`', 'summary' => 'Update any signature-verification code that reads the payload root directly — see the full migration note in the webhooks guide.'],
                        ['version' => 'v4.2.0', 'changeType' => 'minor', 'title' => 'Added response-time badges to the endpoint reference', 'summary' => 'Every endpoint in the API reference now shows a typical response-time badge alongside its parameters.'],
                        ['version' => 'v4.2.0', 'changeType' => 'patch', 'title' => 'Fixed inconsistent code-sample copy formatting', 'summary' => 'Copied code samples no longer include the surrounding line numbers.'],
                    ],
                ),
                'callout-admonition-system' => $this->calloutAdmonitionSystem(
                    variant: 'block',
                    admonitions: [
                        ['kind' => 'danger', 'body' => 'This is a breaking change. Read the webhooks guide\'s migration note before upgrading a production integration.'],
                    ],
                ),
                'feedback-footer' => $this->feedbackFooter(variant: 'simple'),
            ],
            default => [],
        };
    }

    /**
     * @param  list<array<string, mixed>>  $quickLinks
     * @return array<string, mixed>
     */
    private function searchSpotlightHero(string $variant, string $heading, string $summary, array $quickLinks): array
    {
        return [
            'variant' => $variant,
            'eyebrow' => 'Documentation',
            'heading' => $heading,
            'summary' => $summary,
            'searchAction' => '#search',
            'searchProvided' => false,
            'quickLinks' => $quickLinks,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function docTreeSidebar(string $variant): array
    {
        return [
            'variant' => $variant,
            'heading' => 'Documentation',
            'currentUrl' => '#webhooks',
            'tree' => [
                [
                    'label' => 'Getting started',
                    'children' => [
                        ['label' => 'Installation', 'url' => '#installation'],
                        ['label' => 'Authentication', 'url' => '#authentication'],
                        ['label' => 'Your first request', 'url' => '#first-request'],
                    ],
                ],
                [
                    'label' => 'Guides',
                    'children' => [
                        ['label' => 'Configuring webhooks', 'url' => '#webhooks'],
                        ['label' => 'Rate limits', 'url' => '#rate-limits'],
                        ['label' => 'Pagination', 'url' => '#pagination'],
                    ],
                ],
                [
                    'label' => 'API reference',
                    'children' => [
                        ['label' => 'Endpoints', 'url' => '#api-reference'],
                        ['label' => 'Errors', 'url' => '#errors'],
                    ],
                ],
                ['label' => 'Changelog', 'url' => '#changelog'],
            ],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $admonitions
     * @return array<string, mixed>
     */
    private function calloutAdmonitionSystem(string $variant, array $admonitions): array
    {
        return [
            'variant' => $variant,
            'admonitions' => $admonitions,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $entries
     * @return array<string, mixed>
     */
    private function versionChangelogSurfaces(string $variant, string $heading, string $summary, array $entries): array
    {
        return [
            'variant' => $variant,
            'heading' => $heading,
            'summary' => $summary,
            'entries' => $entries,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $parameters
     * @return array<string, mixed>
     */
    private function apiReferenceParameterTable(string $variant, string $heading, string $summary, array $parameters, string $codeSample): array
    {
        return [
            'variant' => $variant,
            'heading' => $heading,
            'summary' => $summary,
            'parameters' => $parameters,
            'codeSample' => $codeSample,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $reasons
     * @return array<string, mixed>
     */
    private function feedbackFooter(string $variant, array $reasons = []): array
    {
        return [
            'variant' => $variant,
            'heading' => 'Was this page helpful?',
            'reasons' => $reasons,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(string $themeKey): array
    {
        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'items' => [
                ['label' => 'Guides', 'url' => '/theme-' . $themeKey . '-directory'],
                ['label' => 'API reference', 'url' => '/theme-' . $themeKey . '-directory#api-reference'],
                ['label' => 'Changelog', 'url' => '/theme-' . $themeKey . '-cta'],
                ['label' => 'Support', 'url' => '/theme-' . $themeKey . '-contact'],
            ],
            'ctaLabel' => 'Ask a question',
            'ctaUrl' => '/theme-' . $themeKey . '-contact',
            'consultationUrl' => '/theme-' . $themeKey . '-contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(string $themeKey): array
    {
        $columns = [
            [
                'heading' => 'Documentation',
                'title' => 'Documentation',
                'links' => [
                    ['label' => 'Getting started', 'url' => '/theme-' . $themeKey . '-directory'],
                    ['label' => 'API reference', 'url' => '/theme-' . $themeKey . '-directory#api-reference'],
                    ['label' => 'Changelog', 'url' => '/theme-' . $themeKey . '-cta'],
                ],
            ],
            [
                'heading' => 'Product',
                'title' => 'Product',
                'links' => [
                    ['label' => 'Webhooks guide', 'url' => '/theme-' . $themeKey . '-detail'],
                    ['label' => 'Status', 'url' => '#status'],
                ],
            ],
            [
                'heading' => 'Support',
                'title' => 'Support',
                'links' => [
                    ['label' => 'Ask the docs team', 'url' => '/theme-' . $themeKey . '-contact'],
                    ['label' => self::SUPPORT_EMAIL, 'url' => 'mailto:' . self::SUPPORT_EMAIL],
                ],
            ],
        ];

        return [
            'brand' => self::BRAND,
            'brandName' => self::BRAND,
            'summary' => 'Fieldnote Docs — documentation and knowledge base for developer tools, built to be searched, not skimmed.',
            'items' => $columns,
            'columns' => $columns,
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }
}
