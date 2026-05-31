# Theme Nonprofit

Status: **Available, no schema impact** · Kind: **theme** · Theme key:
**nonprofit** · Contexts: **frontend, console** · Product group:
**Capell Foundation** · Commercial proposal: **paid first-party theme**

Theme Nonprofit helps a Capell site present impact, campaigns, events, stories,
contact paths, and supporter calls to action in one coherent frontend theme.

## What This Package Adds

- A `nonprofit` theme definition and preset.
- Nonprofit-specific CSS and page wrapper.
- Section renderers for impact, campaigns, volunteer/donate, events, stories,
  contact, proof, CTA, and footer flows.
- Optional renderer awareness for Campaign Studio, Form Builder, Events, and
  Blog.
- A demo command that installs route-backed nonprofit demo pages.
- Theme health and management-page manifest contributions.

## Why It Matters

For a non-technical owner, this theme answers: "Can visitors understand our
mission, see proof of impact, and take the next supporter action?" It gives
campaign and community pages a clear structure without custom theme work.

For developers, the package keeps impact-led presentation separate from
Foundation Theme. Campaign, form, event, and story sections only rely on
optional packages when those packages are installed.

## Runtime Shape

- `NonprofitThemeServiceProvider` registers the theme when
  `capell-app/theme-nonprofit` is installed.
- `DemoCommand` calls `InstallNonprofitThemeDemoAction`.
- Core `ViewSectionRenderer` extra view data guards optional Campaign Studio, Form Builder,
  Events, and Blog sections.
- `ThemeNonprofitHealthCheck` exposes package health to diagnostics.

## Data And Persistence

This package owns no database tables, settings, models, or routes. It reads
Foundation Theme runtime data and Capell page content.

## Screenshot Plan

`docs/screenshots.json` describes marketplace screenshots for homepage,
directory, detail, contact, conversion CTA, and section-suite states.

## Verification

```bash
vendor/bin/pest packages/theme-nonprofit/tests --configuration=phpunit.xml
```

The focused tests cover theme definition, manifest requirements, package-aware
rendering, and public output safety.
