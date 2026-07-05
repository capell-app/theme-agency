<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LiquidGlass\Support\Demo;

use Capell\Core\Enums\LayoutEnum;
use Capell\Core\Enums\PageTypeEnum;
use Capell\FoundationTheme\Contracts\ProvidesThemeDemoContent;
use Capell\FoundationTheme\Support\Demo\ThemeDemoMedia;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;
use Capell\ThemeStudio\LiquidGlass\Enums\WidgetComponentEnum;
use Capell\ThemeStudio\LiquidGlass\LiquidGlassThemeServiceProvider;

/**
 * Complete, vertical-authentic demo content for the Liquid Glass theme.
 *
 * Liquid Glass is definition-only (see LiquidGlassThemeServiceProvider): it
 * registers no ThemeRenderer or section renderers, so every surface renders
 * through the shared `x-capell::layout` + layout-builder container pipeline
 * instead of the legacy section-rendering pipeline. Each surface below seeds
 * a `containers` payload (a 'main' container carrying a `page-content`
 * widget plus one bespoke Liquid Glass widget instance per `cta` /
 * `showcase` / `presets` entry in that surface's {@see sectionCopy()})
 * plus the matching `widgets` blueprint `ThemeDemoPageInstaller` dispatches
 * through `Capell\LayoutBuilder\Support\Creator\WidgetCreator` before
 * writing the containers onto the page's Layout. The page's own
 * `content`/`title` (kept verbatim per surface) is what the page-content
 * widget renders — copy is mined verbatim from the previous screenshot
 * renderer so the demo still reads like a real "glass" site.
 *
 * WIDGET WIRING SCOPE (task "C2"): {@see sectionCopy()}, {@see heroCopy()},
 * and {@see emptyStateListingCopy()} recover the real, previously-authored
 * per-surface copy that the section-builder→layout-builder conversion
 * stopped rendering. Of the seven section types that copy covers, three —
 * `cta`, `showcase`, `presets` — are Liquid Glass's own bespoke, signature
 * sections and are wired below into real
 * `capell.widget.liquid-glass.{cta,showcase,presets}` widget instances (one
 * per occurrence per surface, via `WidgetCreator::bespokeContentWidget()`,
 * since each surface's copy differs and a single shared `Widget` row per
 * type would have one surface clobber another's copy — the same singleton
 * constraint `page-content` has, worked around here with per-surface keys
 * instead). `navigation` / `footer` are wired separately, through
 * `LiquidGlassThemeInterceptor`'s `header_file` / `footer_file` Theme
 * defaults, not through this class. `hero` / `features` / `proof` /
 * `content-listing` are NOT wired to bespoke widgets in this pilot — see
 * `LiquidGlassThemeServiceProvider::registerLayoutAreas()`'s "NOTE on scope"
 * for why (no per-theme override seam exists yet for the shared foundation
 * widget views those four sections would otherwise map to). `heroCopy()`
 * and `emptyStateListingCopy()` remain intentionally inert for the same
 * reason and are preserved here for whenever that follow-up design decision
 * lands.
 */
final class LiquidGlassDemoContent implements ProvidesThemeDemoContent
{
    private const string BRAND = 'Liquid Glass';

    /**
     * Bespoke Liquid Glass section types that {@see sectionCopy()} may
     * contain and that this class turns into real, seeded
     * `capell.widget.liquid-glass.*` widget instances (see class docblock).
     *
     * @var array<string, WidgetComponentEnum>
     */
    private const array BESPOKE_SECTION_WIDGETS = [
        'cta' => WidgetComponentEnum::Cta,
        'showcase' => WidgetComponentEnum::Showcase,
        'presets' => WidgetComponentEnum::Presets,
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
     * `capell.widget.liquid-glass.{cta,showcase,presets}` widgets (see class
     * docblock "WIDGET WIRING SCOPE"); the `hero`, `features`, `proof`, and
     * `content-listing` entries this method also returns are not consumed by
     * anything yet, for the reason documented there.
     *
     * @return list<array<string, mixed>>
     */
    public function sectionCopy(string $surface): array
    {
        $media = ThemeDemoMedia::groupedForTheme(LiquidGlassThemeServiceProvider::THEME_KEY);

        return match ($surface) {
            'homepage' => [
                $this->featuresSection(),
                $this->showcaseSection($media),
                $this->presetsSection(),
                $this->proofSection(),
                $this->contentListingSection(
                    heading: 'Browse content cards without leaving the glass',
                    summary: 'Translucent result cards keep listings legible and structured while the theme stays free of content records.',
                    media: $media,
                ),
                $this->ctaSection(
                    heading: 'Bring your pages onto the glass',
                    summary: 'A warm, focused call to action that stays native to the translucent theme.',
                ),
            ],
            'directory' => [
                $this->contentListingSection(
                    heading: 'Browse content cards without leaving the glass',
                    summary: 'Translucent result cards keep listings legible and structured while the theme stays free of content records.',
                    media: $media,
                ),
                $this->showcaseSection($media),
                $this->ctaSection(
                    heading: 'Bring your pages onto the glass',
                    summary: 'A warm, focused call to action that stays native to the translucent theme.',
                ),
            ],
            'detail' => [
                $this->showcaseSection($media),
                $this->proofSection(),
                $this->contentListingSection(
                    heading: 'Scan result groups inside clear translucent panels',
                    summary: 'A content-detail review pairs grouped cards and proof so visitors can evaluate without losing the glass system.',
                    media: $media,
                ),
                $this->ctaSection(
                    heading: 'Bring your pages onto the glass',
                    summary: 'A warm, focused call to action that stays native to the translucent theme.',
                ),
            ],
            'contact' => [
                $this->featuresSection(),
                $this->proofSection(),
                $this->ctaSection(
                    heading: 'Bring your pages onto the glass',
                    summary: 'A warm, focused call to action that stays native to the translucent theme.',
                ),
            ],
            'empty' => [
                $this->featuresSection(),
                $this->ctaSection(
                    heading: 'Bring your pages onto the glass',
                    summary: 'A warm, focused call to action that stays native to the translucent theme.',
                ),
            ],
            'not-found' => [
                $this->ctaSection(
                    heading: 'Bring your pages onto the glass',
                    summary: 'A warm, focused call to action that stays native to the translucent theme.',
                ),
            ],
            'cta' => [
                $this->presetsSection(),
                $this->proofSection(),
                $this->ctaSection(
                    heading: 'Bring your pages onto the glass',
                    summary: 'A warm, focused call to action that stays native to the translucent theme.',
                ),
            ],
            default => [],
        };
    }

    /**
     * The real, previously-authored per-surface hero copy that the
     * section-builder→layout-builder conversion stopped rendering. Each
     * surface's hero carried unique eyebrow/action/media fields (and, for
     * `empty`, `not-found`, and `cta`, a hero summary that differs from the
     * page's `render_data['summary']`) that {@see sectionCopy()} does not
     * capture, since it only recovers the six shared section-builder bodies.
     * Recovered here, verbatim.
     *
     * Still not consumed by anything: `hero` is one of the sections C2
     * deliberately did not wire to a bespoke widget (see class docblock
     * "WIDGET WIRING SCOPE"). Preserved here for whenever a per-theme
     * override seam for `capell.widget.hero` exists.
     *
     * @return array<string, mixed>
     */
    public function heroCopy(string $surface): array
    {
        $media = ThemeDemoMedia::groupedForTheme(LiquidGlassThemeServiceProvider::THEME_KEY);

        return match ($surface) {
            'homepage' => [
                'type' => 'hero',
                'eyebrow' => 'Liquid Glass',
                'heading' => 'A modern glass surface for launch and service pages',
                'summary' => 'A free modern theme with translucent panels, crisp content rhythm, and warm accent actions for launch, listing, and lead journeys.',
                'actions' => [
                    ['label' => 'View features', 'url' => '#features', 'style' => 'primary'],
                    ['label' => 'Get in touch', 'url' => '#contact', 'style' => 'secondary'],
                ],
                'mediaUrl' => $media['hero'][0],
                'mediaAlt' => 'Layered translucent glass panels',
            ],
            'directory' => [
                'type' => 'hero',
                'eyebrow' => 'Listing',
                'heading' => 'Browse content cards without leaving the glass',
                'summary' => 'Translucent result cards keep listings legible and structured while the theme stays free of content records.',
                'actions' => [
                    ['label' => 'View features', 'url' => '#features', 'style' => 'primary'],
                ],
                'mediaUrl' => $media['listing'][0] ?? $media['hero'][0],
                'mediaAlt' => 'Translucent content cards in a glass listing',
            ],
            'detail' => [
                'type' => 'hero',
                'eyebrow' => 'Story',
                'heading' => 'Scan result groups inside clear translucent panels',
                'summary' => 'A content-detail review pairs grouped cards and proof so visitors can evaluate without losing the glass system.',
                'actions' => [
                    ['label' => 'View features', 'url' => '#features', 'style' => 'secondary'],
                ],
                'mediaUrl' => $media['detail'][0],
                'mediaAlt' => 'A launch page rebuilt on the glass section rhythm',
            ],
            'contact' => [
                'type' => 'hero',
                'eyebrow' => 'Contact',
                'heading' => 'A lead path that stays polished through the glass',
                'summary' => 'A non-submitting contact CTA proves the lead journey feels native to the theme while using ordinary public page data.',
                'actions' => [
                    ['label' => 'Get in touch', 'url' => 'mailto:studio@liquidglass.example', 'style' => 'primary'],
                    ['label' => 'View features', 'url' => '#features', 'style' => 'secondary'],
                ],
                'mediaUrl' => $media['contact'][0],
                'mediaAlt' => 'A polished glass lead journey',
            ],
            'empty' => [
                'type' => 'hero',
                'eyebrow' => 'Listing',
                'heading' => 'No content cards match that filter yet',
                'summary' => 'Nothing matches the current filter. Clear it to see every card on the glass, or jump straight to the features.',
                'actions' => [
                    ['label' => 'View features', 'url' => '#features', 'style' => 'primary'],
                    ['label' => 'Get in touch', 'url' => '#contact', 'style' => 'secondary'],
                ],
            ],
            'not-found' => [
                'type' => 'hero',
                'eyebrow' => '404',
                'heading' => 'This page slipped through the glass',
                'summary' => 'The link is broken or the page has moved. Head back to the features, or start a conversation.',
                'actions' => [
                    ['label' => 'Back to home', 'url' => '/', 'style' => 'primary'],
                    ['label' => 'View features', 'url' => '#features', 'style' => 'secondary'],
                ],
            ],
            'cta' => [
                'type' => 'hero',
                'eyebrow' => 'Get started',
                'heading' => 'Bring your pages onto the glass',
                'summary' => 'Move launch, listing, and lead pages onto translucent panels with a section rhythm that stays crisp from first view to conversion.',
                'actions' => [
                    ['label' => 'View features', 'url' => '#features', 'style' => 'primary'],
                    ['label' => 'Get in touch', 'url' => '#contact', 'style' => 'secondary'],
                ],
                'mediaUrl' => $media['cta'][0],
                'mediaAlt' => 'Translucent glass panels for launch pages',
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
     * sections C2 deliberately did not wire to a bespoke widget (see class
     * docblock "WIDGET WIRING SCOPE"). Preserved here for whenever a
     * per-theme override seam for `capell.widget.page.latest` exists.
     *
     * @return array<string, mixed>
     */
    public function emptyStateListingCopy(): array
    {
        return [
            'type' => 'content-listing',
            'heading' => 'Nothing to show on the glass here',
            'summary' => 'When content lands in this group it appears here as translucent cards, newest first.',
            'variant' => 'editorial',
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
            title: self::BRAND . ' — A modern glass surface for launch pages',
            slug: 'theme-' . $themeKey,
            content: $this->prose(
                'A modern glass surface for launch and service pages',
                'Translucent panels, crisp content rhythm, and warm accent actions for launch, listing, and lead journeys.',
            ),
            renderData: [
                'summary' => 'A free modern theme with translucent panels, crisp content rhythm, and warm accent actions for launch, listing, and lead journeys.',
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
            name: self::BRAND . ' Listing',
            title: 'Listing — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-directory',
            content: $this->prose(
                'Browse content cards without leaving the glass',
                'Translucent result cards keep listings legible and structured while the theme stays free of content records.',
            ),
            renderData: [
                'summary' => 'Translucent result cards keep listings legible and structured while the theme stays free of content records.',
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
            name: self::BRAND . ' Detail',
            title: 'Marlow Studio launch page — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-detail',
            content: $this->prose(
                'Scan result groups inside clear translucent panels',
                'A content-detail review pairs grouped cards and proof so visitors can evaluate without losing the glass system.',
            ),
            renderData: [
                'summary' => 'A content-detail review pairs grouped cards and proof so visitors can evaluate without losing the glass system.',
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
            name: self::BRAND . ' Contact',
            title: 'Get in touch — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-contact',
            content: $this->prose(
                'A lead path that stays polished through the glass',
                'A non-submitting contact CTA proves the lead journey feels native to the theme while using ordinary public page data.',
            ),
            renderData: [
                'summary' => 'A non-submitting contact CTA proves the lead journey feels native to the theme while using ordinary public page data.',
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
            title: 'No results — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-empty',
            content: $this->prose(
                'No cards match the glass yet',
                'A graceful empty state for a filtered listing with no matching content cards.',
            ),
            renderData: [
                'summary' => 'No content cards match that filter yet — the glass listing stays calm and points somewhere useful.',
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
                'This page slipped through the glass',
                'A not-found page that routes visitors back into the features and contact paths.',
            ),
            renderData: [
                'summary' => 'That page has moved or never existed — here is the way back onto the glass.',
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
            title: 'Bring your pages onto the glass — ' . self::BRAND,
            slug: 'theme-' . $themeKey . '-cta',
            content: $this->prose(
                'Bring your pages onto the glass',
                'A focused conversion page inviting teams to move their launch and service pages onto the translucent theme.',
            ),
            renderData: [
                'summary' => 'Bring your launch, listing, and lead pages onto the glass.',
                'navigation' => $this->navigation(),
                'footer' => $this->footer(),
            ],
            containers: $this->containers('cta'),
            widgets: $this->widgets('cta'),
        );
    }

    /**
     * The layout-builder container payload for a Liquid Glass demo surface: a
     * single 'main' container carrying the shared `page-content` widget
     * (so the seeded page's own title/content, kept per-surface above,
     * renders through `x-capell::layout`) followed by one bespoke Liquid
     * Glass widget instance per `cta` / `showcase` / `presets` entry in this
     * surface's {@see sectionCopy()}, in the same order that copy used to
     * render in `render_data['sections']`.
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
     * `WidgetCreator::bespokeContentWidget()` call per bespoke Liquid Glass
     * section this surface's {@see sectionCopy()} carries, each with its own
     * surface-scoped `key` and real seeded copy as `meta` (so, e.g.,
     * `homepage`'s `cta` widget and `contact`'s `cta` widget are distinct
     * `Widget` rows with distinct copy, not one shared row that would have
     * one surface's copy clobber another's).
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
     * The ordered list of this surface's `cta` / `showcase` / `presets`
     * section-copy entries (see {@see BESPOKE_SECTION_WIDGETS}), each
     * resolved to the widget key, display name, component, and meta
     * {@see containers()} and {@see widgets()} need. Surface-scoped,
     * 1-indexed-occurrence widget keys (e.g. `liquid-glass-cta-homepage-1`)
     * keep each surface's copy on its own `Widget` row even though several
     * surfaces reuse the same section type.
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
                'key' => sprintf('liquid-glass-%s-%s-%d', $type, $surface, $occurrence),
                'name' => sprintf('Liquid Glass %s (%s)', ucfirst($type), $surface),
                'component' => $component->value,
                'meta' => $section,
            ];
        }

        return $bespokeSections;
    }

    /**
     * @return array<string, mixed>
     */
    private function navigation(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'items' => [
                ['label' => 'Features', 'url' => '#features'],
                ['label' => 'Proof', 'url' => '#proof'],
                ['label' => 'Listing', 'url' => '#content-listing'],
                ['label' => 'Contact', 'url' => '#contact'],
            ],
            'ctaLabel' => 'Get in touch',
            'ctaUrl' => '#contact',
            'consultationUrl' => '#contact',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function footer(): array
    {
        return [
            'brandName' => self::BRAND,
            'brand' => self::BRAND,
            'summary' => 'A free modern theme of translucent panels for launch, listing, and lead pages.',
            'columns' => [
                [
                    'heading' => 'Product',
                    'links' => [
                        ['label' => 'Features', 'url' => '#features'],
                        ['label' => 'Proof', 'url' => '#proof'],
                    ],
                ],
                [
                    'heading' => 'Browse',
                    'links' => [
                        ['label' => 'Listing', 'url' => '#content-listing'],
                        ['label' => 'Detail', 'url' => '#content-listing'],
                    ],
                ],
                [
                    'heading' => 'Company',
                    'links' => [
                        ['label' => 'Contact', 'url' => '#contact'],
                        ['label' => 'Get in touch', 'url' => '#contact'],
                    ],
                ],
            ],
        ];
    }

    private function prose(string $heading, string $summary): string
    {
        return sprintf('<h2>%s</h2><p>%s</p>', e($heading), e($summary));
    }

    /**
     * @return array<string, mixed>
     */
    private function featuresSection(): array
    {
        return [
            'type' => 'features',
            'heading' => 'Surfaces that stay legible through the glass',
            'summary' => 'Translucent panels keep structure, rhythm, and contrast intact across every public page.',
            'features' => [
                [
                    'type' => 'Surface',
                    'title' => 'Frosted panels with depth',
                    'description' => 'Layered translucency adds depth while keeping headings and copy crisp on any backdrop.',
                ],
                [
                    'type' => 'Rhythm',
                    'title' => 'A steady section cadence',
                    'description' => 'Hero, feature, proof, and CTA panels share spacing so pages read with calm momentum.',
                ],
                [
                    'type' => 'Tokens',
                    'title' => 'Brand-aware glass tints',
                    'description' => 'Accent and surface tokens flow through every panel, so the glass adapts to the brand instantly.',
                ],
            ],
        ];
    }

    /**
     * @param  array{hero: list<string>, listing: list<string>, detail: list<string>, proof: list<string>, contact: list<string>, cta: list<string>}  $media
     * @return array<string, mixed>
     */
    private function showcaseSection(array $media): array
    {
        $pool = array_values(array_unique(array_merge(
            $media['listing'],
            $media['detail'],
            $media['proof'],
            $media['cta'],
        )));

        $entries = [
            ['discipline' => 'Launch page', 'title' => 'Marlow Studio launch', 'summary' => 'A service launch rebuilt on the glass section rhythm in a single sprint, with depth and full legibility.', 'metric' => '+34%', 'metricLabel' => 'More launches shipped'],
            ['discipline' => 'Service page', 'title' => 'Tideline Labs service site', 'summary' => 'A shared section rhythm meant new pages dropped in without redesign, all on translucent panels.', 'metric' => '2x', 'metricLabel' => 'Faster page builds'],
            ['discipline' => 'Brand surface', 'title' => 'Northglass product pages', 'summary' => 'Brand tints flowed through every panel, so the glass theme felt bespoke rather than templated.', 'metric' => '4.9/5', 'metricLabel' => 'Reported polish'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $items[] = [
                ...$entry,
                'image' => $pool[$index % max(count($pool), 1)] ?? null,
                'imageAlt' => $entry['title'],
            ];
        }

        return [
            'type' => 'showcase',
            'eyebrow' => 'Selected work',
            'heading' => 'Teams that ship on the glass',
            'summary' => 'Outcome metrics and operator stories show the translucent theme holding up across real launch and service pages.',
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function presetsSection(): array
    {
        return [
            'type' => 'presets',
            'eyebrow' => 'Three presets',
            'heading' => 'One glass system, three token-driven presets',
            'summary' => 'Bright product launches, editorial glass pages, and darker graphite surfaces — all driven by Theme Studio tokens.',
            'presets' => [
                [
                    'name' => 'Clarity',
                    'title' => 'Bright product launches',
                    'description' => 'A light, high-contrast preset for marketing and launch pages where the glass should feel airy.',
                    'surfaces' => ['Launch heroes', 'Feature panels', 'Conversion CTAs'],
                ],
                [
                    'name' => 'Prism',
                    'title' => 'Editorial glass pages',
                    'description' => 'A balanced editorial preset that pairs translucent cards with proof for listings and stories.',
                    'surfaces' => ['Content listings', 'Detail stories', 'Proof panels'],
                ],
                [
                    'name' => 'Graphite',
                    'title' => 'Darker graphite surfaces',
                    'description' => 'A dark preset with deep graphite glass and compact navigation that holds up on mobile.',
                    'surfaces' => ['Dark launch pages', 'Compact navigation', 'Mobile surfaces'],
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
            'heading' => 'Teams that ship on the glass',
            'summary' => 'Outcome metrics and operator quotes show the theme holding up across real launch and service pages.',
            'items' => [
                [
                    'metric' => '+34% launches',
                    'quote' => 'The glass panels gave our launch page depth without losing legibility.',
                    'name' => 'Marlow Studio',
                    'role' => 'Head of Web',
                ],
                [
                    'metric' => '2x faster builds',
                    'quote' => 'A shared section rhythm meant new pages dropped in without redesign.',
                    'name' => 'Tideline Labs',
                    'role' => 'Design Lead',
                ],
                [
                    'metric' => '4.9/5 polish',
                    'quote' => 'Brand tints flowed through every panel, so the theme felt bespoke.',
                    'name' => 'Northglass',
                    'role' => 'Product Marketing',
                ],
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
            ['type' => 'Guide', 'title' => 'Designing with translucent panels', 'summary' => 'How to layer glass surfaces while keeping headings and copy fully legible.', 'url' => '#features'],
            ['type' => 'Story', 'title' => 'Marlow Studio launch page', 'summary' => 'A service launch rebuilt on the glass section rhythm in a single sprint.', 'url' => '#proof'],
            ['type' => 'Reference', 'title' => 'Brand-aware glass tokens', 'summary' => 'Mapping accent and surface colours through every translucent panel.', 'url' => '#content-listing'],
        ];

        $items = [];

        foreach ($entries as $index => $entry) {
            $items[] = [
                ...$entry,
                'image' => $images[$index % max(count($images), 1)] ?? null,
            ];
        }

        return [
            'type' => 'content-listing',
            'heading' => $heading,
            'summary' => $summary,
            'variant' => 'editorial',
            'items' => $items,
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
                ['label' => 'View features', 'url' => '#features', 'style' => 'primary'],
                ['label' => 'Get in touch', 'url' => '#contact', 'style' => 'secondary'],
            ],
        ];
    }
}
