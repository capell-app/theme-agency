# Theme Local Services

Status: **Available, no schema impact** · Kind: **theme** · Theme key:
**local-services** · Contexts: **frontend, console** · Product group:
**Capell Foundation** · Commercial proposal: **paid first-party theme**

Theme Local Services helps a Capell site explain services, service areas, proof,
case studies, resources, contact paths, and quote requests in one coherent
frontend theme.

## What This Package Adds

- A `local-services` theme definition and preset.
- Local-services CSS and page wrapper.
- Section renderers for services, service areas, quote forms, case studies,
  resources, contact, proof, CTA, and footer flows.
- Optional renderer awareness for Blog and Form Builder.
- Service-area cards that accept hydrated area data with `label`, `url`, and
  optional `postcode` values, backed by translated defaults.
- A demo command that installs route-backed local-services demo pages.
- Theme health and management-page manifest contributions.

## Why It Matters

For a non-technical owner, this theme answers: "Can visitors see what we do,
where we work, why they should trust us, and how they request a quote?" It gives
that journey a purpose-built structure.

For developers, the package keeps service-business presentation separate from
Foundation Theme. Quote and resource sections only rely on Form Builder or Blog
when those packages are installed.

## Runtime Shape

- `LocalServicesThemeServiceProvider` registers the theme when
  `capell-app/theme-local-services` is installed.
- `DemoCommand` calls `InstallLocalServicesThemeDemoAction`.
- Core `ViewSectionRenderer` extra view data guards optional Blog and Form Builder sections.
- `ThemeLocalServicesHealthCheck` exposes package health to diagnostics.

## Data And Persistence

This package owns no database tables, settings, models, or routes. It reads
Foundation Theme runtime data and Capell page content.

## Screenshot Plan

`docs/screenshots.json` describes the committed route-backed capture set. The
marketplace manifest keeps the extension-card and hero JPG previews, and now
uses real PNG captures for the homepage, service-area, and quote-form workflows
instead of the earlier SVG layout diagrams.

## Verification

```bash
vendor/bin/pest packages/theme-local-services/tests --configuration=phpunit.xml
```

The focused tests cover theme definition, service-area fallback/custom
rendering, manifest requirements, package-aware rendering, health checks, and
public output safety.
