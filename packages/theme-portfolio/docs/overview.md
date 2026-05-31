# Theme Portfolio

Status: **Available, no schema impact** · Kind: **theme** · Theme key:
**portfolio** · Contexts: **frontend, console** · Product group:
**Capell Foundation** · Commercial proposal: **paid first-party theme**

Theme Portfolio helps a Capell site present work, case studies, services,
testimonials, media-kit content, newsletter signup, and conversion paths in one
coherent frontend theme.

## What This Package Adds

- A `portfolio` theme definition and preset.
- Portfolio-specific CSS and page wrapper.
- Section renderers for work grid, case studies, services, testimonials,
  speaking/media kit, newsletter, proof, CTA, and footer flows.
- Optional renderer awareness for Content Sections, Media Library, and
  Newsletter.
- A demo command that installs route-backed portfolio demo pages.
- Theme health and management-page manifest contributions.

## Why It Matters

For a non-technical owner, this theme answers: "Can visitors see my work,
understand what I offer, trust the proof, and stay connected?" It gives a
portfolio site a stronger structure than a generic page template.

For developers, the package keeps portfolio presentation separate from
Foundation Theme. Case-study, media, and newsletter sections only rely on
optional packages when those packages are installed.

## Runtime Shape

- `PortfolioThemeServiceProvider` registers the theme when
  `capell-app/theme-portfolio` is installed.
- `DemoCommand` calls `InstallPortfolioThemeDemoAction`.
- Core `ViewSectionRenderer` extra view data guards optional Content Sections, Media Library,
  and Newsletter sections.
- `ThemePortfolioHealthCheck` exposes package health to diagnostics.

## Data And Persistence

This package owns no database tables, settings, models, or routes. It reads
Foundation Theme runtime data and Capell page content.

## Product Direction

Keep this package only while it provides a distinct studio/portfolio lane. If it
continues to overlap with Theme Agency, merge its useful work, case-study,
media-kit, and newsletter ideas into the stronger theme.

## Screenshot Plan

`docs/screenshots.json` describes marketplace screenshots for homepage,
directory, detail, contact, conversion CTA, and section-suite states.

## Verification

```bash
vendor/bin/pest packages/theme-portfolio/tests --configuration=phpunit.xml
```

The focused tests cover theme definition, manifest requirements, package-aware
rendering, and public output safety.
