# Capell Theme Scale

Use this scale when creating a new Capell theme, changing one existing theme, or applying a renderer pattern across all themes. The goal is to keep themes expressive without turning public Blade into a second CMS or leaking editor concerns into cached frontend HTML.

## Theme Tiers

| Tier       | Packages                                                                            | Role                                                                                                                                                           | Expected depth                                                                                                                    |
| ---------- | ----------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| Foundation | `capell-app/foundation-theme`                                                       | Default frontend runtime, shared renderer conventions, Tailwind asset generation, media URL handling, generic Blade components, and base Layout Builder areas. | Boring, stable, shared. Changes here affect every child theme.                                                                    |
| Basic/free | `capell-app/theme-agency`, `capell-app/theme-corporate`                             | First-party visual treatments for common service, portfolio, B2B, public-sector, and professional sites.                                                       | Thin child themes with polished presets, page wrappers, and the standard section set.                                             |
| Premium    | `capell-app/theme-commerce`, `capell-app/theme-healthcare`, `capell-app/theme-saas` | Higher-value themes for domains that need richer page patterns, optional package integrations, and more content states.                                        | Extra renderer layers, domain sections, optional package fallbacks, richer marketplace screenshots, and more regression coverage. |

Foundation is the normal/default theme. Basic and premium themes extend Foundation rather than replacing it unless Foundation's rendering contract is genuinely the wrong base.

## What A Theme Can Own

A theme package may own:

- A stable `themeKey` in `capell.json`.
- A runtime service provider that registers `ThemeDefinitionData` with `ThemeRegistry`.
- A `BladeThemeRenderer` page wrapper.
- `ViewSectionRenderer` mappings and custom section renderer classes.
- Preset defaults through `ThemePresetData`.
- Theme-owned public views under `resources/views`.
- Theme-specific CSS assets and preview images.
- Marketplace screenshots and package health checks.
- Demo creation Actions and demo commands when the package ships example content.

A theme package should stay thin. Current first-party child themes do not own migrations, routes, models, permissions, settings tables, or admin resources. Their visible admin surface is the shared Theme management page contributed through the manifest.

## What A Theme Must Not Own

A theme must not store designed page markup in content fields or render editor internals to the public frontend.

Do not put these in public Blade, cached HTML, theme assets, or package demo content:

- Authoring controls, signed editor URLs, permissions, admin routes, field paths, schema labels, model IDs, or package internals.
- Database queries, relationship lazy-loading, direct model lookups, or `loadMissing()` calls.
- Client-specific copy, content, layout wrappers, utility classes, or hardcoded content structures that editors should control.
- Theme-specific presentation HTML inside page body/content fields.

Public Blade receives hydrated render data and renders presentation. It must not discover content by querying.

## Configuration Scale

When a theme feature is configurable, choose the narrowest durable owner.

| Need                                                                                                | Put it in                                                         | Why                                                                           |
| --------------------------------------------------------------------------------------------------- | ----------------------------------------------------------------- | ----------------------------------------------------------------------------- |
| Site-wide visual tokens such as colours, type, button shape, logo treatment, and default card style | Theme settings or theme preset database edits                     | These vary by site and should override package defaults without code changes. |
| Page type structure, required fields, SEO defaults, or page-level editorial controls                | Page blueprint/schema                                             | These describe the page contract, not one renderer.                           |
| Reusable content module fields and allowed variants                                                 | Widget blueprint or Layout Builder block definition               | Editors reuse these across pages and themes.                                  |
| One instance of copy, media, CTA links, selected variant, spacing, or background                    | Page content, widget content, or Layout Builder block assets/meta | Instance-level content belongs in the database.                               |
| Placement outside the main content loop, such as header, footer, announcement, or utility areas     | Layout Builder area registration plus container `meta.area`       | Placement remains editable without inventing hidden static containers.        |
| Renderer-only markup, responsive layout, utility classes, and domain presentation                   | Blade views and section renderer classes                          | Presentation belongs in code so content survives theme changes.               |
| Build-time asset sources, Tailwind sources, or package asset manifests                              | Package config and asset pipeline                                 | These are developer/runtime concerns, not editor content.                     |

If the value changes per site, page, widget, block, language, or editor decision, it belongs in persisted Capell data. If it is only the HTML/CSS shape used to display that data, it belongs in the theme package.

## Layout Builder And Page Assets

Layout Builder is the composition layer for themes. It owns layout containers, blocks, block assets, public layout graphs, reusable presets, layout areas, and the Filament editor. Themes should consume its public graph and render hydrated block data.

Use Layout Builder when:

- Editors need to arrange sections or blocks.
- A page needs per-instance media, CTA, copy, variant, spacing, or background controls.
- A theme area needs editable content outside the main page loop.
- A preset should duplicate structure and presentation settings without duplicating client content.

Use page assets and block assets for content that belongs to a rendered instance. Keep portable editorial content in the database and keep design wrappers in Blade. Demo creators should seed minimal editable copy and attach configured blocks, not save full designed HTML.

Public renderers should use `BuildPublicLayoutGraphAction` or existing renderer components. Do not query from public views.

## Creating A New Theme

Start with Foundation unless there is a strong reason not to.

1. Create a Composer package under `packages/theme-client`.
2. Add `capell.json` with `manifest-version: 3`, `kind: "theme"`, a stable `themeKey`, `extends: "capell-app/foundation-theme"`, and runtime provider metadata.
3. Require `capell-app/core` and `capell-app/foundation-theme`. Add supported optional packages only when renderers actually integrate with them.
4. Register the package with `CapellCore::registerPackage()` in `register()`.
5. In `boot()`, stop early when the package is not installed, load views, and register the theme definition, page wrapper, and section renderers.
6. Add the standard section set first: `navigation`, `hero`, `features`, `proof`, `content-listing`, `cta`, and `footer`.
7. Add premium/domain sections only when the theme has a real domain need and a safe fallback for missing optional packages.
8. Add demo content through an Action. Store content and block configuration in Capell data; keep presentation in views.
9. Add focused tests for the definition, manifest requirements, renderer registration, optional package fallback, and public-output safety.

Theme keys are content identifiers. Renaming one is a migration, not a cosmetic package rename.

## Applying Logic Across All Themes

Use this order when a change should affect every theme:

1. Put shared runtime behaviour in Foundation or the core Theme Studio runtime.
2. Put shared Layout Builder behaviour in Layout Builder Actions, payload contributors, layout areas, or block presentation projection.
3. Keep child themes limited to theme-specific page wrappers, section views, presets, and custom renderers.
4. Update every child theme only when the public contract changes or each theme needs distinct markup.
5. Add cross-theme tests or visual fixtures for the shared contract, then targeted package tests for each affected renderer.

Do not copy the same logic into five theme service providers if a shared registry, Action, DTO, or Foundation component can own it cleanly.

## Premium Theme Expectations

Premium themes need more than a colour palette over the standard sections. They should include:

- Domain-specific section renderers.
- Optional package integrations with graceful empty/missing-package states.
- More complete marketplace screenshots and demo paths.
- Mobile and desktop visual regression coverage for the most theme-specific surfaces.
- Tests proving public output stays free of authoring metadata.
- Clear health checks for missing manifests, optional integrations, and stale theme data.

Premium does not mean more static content. It means better defaults, richer configurable structures, and stronger coverage.

## Verification

For documentation-only theme changes, check links and terminology against the package manifests and README files.

For package code changes, start narrow:

```bash
vendor/bin/pest packages/theme-saas/tests --configuration=phpunit.xml
```

Then run the affected package suites and any shared Foundation or Layout Builder tests when the contract changes:

```bash
vendor/bin/pest packages/foundation-theme/tests packages/layout-builder/tests --configuration=phpunit.xml
```

Use `composer preflight` before committing broader theme/runtime changes.
