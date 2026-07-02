# Capell Theme, Layout Builder, And AI Orchestrator Boundaries

## Package Responsibilities

### `capell-app/frontend`

Owns Capell's public rendering context: site, language, page, layout, theme key, route params, render hooks, frontend assets, and page cache integration. Frontend resolves the active theme view chain, but does not import concrete premium themes.

### `capell-app/theme-foundation`

Owns the free theme foundation and shared theme runtime. It provides the baseline Blade/Tailwind rendering surface, theme registry, renderer contracts, preview context, and token CSS support. Premium themes may extend it, but Foundation remains the platform fallback and carries the `default` theme key.

### First-party theme packages

Theme packages such as `capell-app/theme-liquid-glass`, `capell-app/theme-dark-product-system`, `capell-app/theme-editorial-serif`, and the other first-party visual `theme-*` packages own polished renderers. They register definitions, curated presets, page renderers, section renderers, views, and visual assets. Each theme installs independently and declares `extends: "capell-app/theme-foundation"` in `capell.json`. There is no Studio metapackage bundling them together.

### `capell-app/layout-builder`

Owns Capell's visual composition layer: layout containers, widgets, widget assets, page-level widget asset overrides, layout presets, layout areas, public layout graphs, and the Filament layout editor. It is a separate optional package in this repository, installed before packages that need editable page composition such as Blog and first-party themes. Core still owns sites, pages, languages, URLs, themes, and base content models.

### `capell-app/ai-orchestrator`

Owns the commercial AI orchestration layer: capability registration, prompt/capability execution boundaries, approval levels, and optional package integrations. AI Orchestrator wraps package-owned Actions such as Layout Builder layout previewing; packages expose normal Actions and do not need commercial AI dependencies unless they provide an AI-assisted surface.

## Composition Model

```text
HTTP request
  -> frontend resolves site, language, page, layout, and active theme key
  -> foundation theme runtime optionally supplies preview theme/preset
  -> theme runtime resolves active or preview theme/preset and brand profile
  -> Layout Builder builds the public layout graph when the page uses editable composition
  -> CapellFrontendThemePageAdapter maps the page and layout graph into portable sections
  -> selected theme renderer renders shared section data
  -> token CSS asset is loaded using the isolated theme/preset/brand cache key
```

## Rules

1. One theme is active per site.
2. Theme inheritance is single-parent through `capell.json` `extends`.
3. Parent preset defaults load first, child preset defaults load second, and Theme admin database edits win last.
4. Foundation Theme owns shared runtime behavior; visual treatment belongs in concrete theme packages.
5. Layout Builder owns layout/widget storage, public layout graphs, layout areas, and page-level widget asset overrides.
6. Packages that need editable visual composition should require `capell-app/layout-builder`; packages that only render fixed public data should not.
7. AI Orchestrator owns AI integration and optional package wrappers; Foundation packages do not import AI Orchestrator classes.
8. Public Blade, cached HTML, and theme output must stay free of authoring controls, editor metadata, signed admin URLs, and database queries.

See [Creating a Capell theme](creating-a-theme.md) for the package contract and install flow.
