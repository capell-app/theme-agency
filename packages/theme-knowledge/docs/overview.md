# Theme Knowledge

Status: **Available, no schema impact** · Kind: **theme** · Theme key:
**knowledge** · Contexts: **frontend, console** · Product group:
**Capell Foundation** · Commercial proposal: **paid first-party theme**

Theme Knowledge helps a Capell site present editorial content, resource hubs,
authors, topic pages, search paths, and newsletter conversion in one coherent
frontend theme.

## What This Package Adds

- A `knowledge` theme definition and preset.
- Knowledge-specific CSS and page wrapper.
- Section renderers for doc articles, topic hubs, featured content, resource
  library, search listing, newsletter, authors, proof, CTA, and footer flows.
- Optional renderer awareness for Blog, Search, and Newsletter.
- Data-driven author and topic hub cards with translated defaults.
- A documentation/article layout with hydrated breadcrumbs, category sidebar,
  readable article body, metadata, and sticky table of contents.
- A demo command that installs route-backed knowledge demo pages.
- Theme health and management-page manifest contributions.

## Why It Matters

For a non-technical owner, this theme answers: "Can visitors find useful
content, trust its source, and keep following our updates?" It gives the site
the structure of a serious resource library without asking the owner to plan
renderer internals.

For developers, the package keeps editorial presentation separate from
Foundation Theme. Optional package integrations are checked at render time
instead of assumed.

## Runtime Shape

- `KnowledgeThemeServiceProvider` registers the theme when
  `capell-app/theme-knowledge` is installed.
- `DemoCommand` calls `InstallKnowledgeThemeDemoAction`.
- Core `ViewSectionRenderer` extra view data guards optional Blog, Search, and Newsletter
  sections.
- Public Blade relies on renderer-provided optional package flags and hydrated
  article layout data rather than checking package installation in the view layer.
- `ThemeKnowledgeHealthCheck` exposes package health to diagnostics.

## Data And Persistence

This package owns no database tables, settings, models, or routes. It reads
Foundation Theme runtime data and Capell page content.

## Screenshot Coverage

`docs/screenshots.json` describes committed marketplace screenshots for the admin theme list, full frontend render, homepage, search, topic hubs, featured content, newsletter, author bench, and signed preview output. `capell.json` promotes the same route-backed PNG capture set.

## Verification

```bash
vendor/bin/pest packages/theme-knowledge/tests --configuration=phpunit.xml
```

The focused tests cover theme definition, translated/data-driven author and
topic hub defaults, manifest requirements, package-aware rendering, health
diagnostics, and public output safety.
