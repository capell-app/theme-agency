# Theme Education

Status: **Available, no schema impact** · Kind: **theme** · Theme key:
**education** · Contexts: **frontend, console** · Product group:
**Capell Foundation** · Commercial proposal: **paid first-party theme**

Theme Education helps a Capell site present courses, training programmes,
learning resources, instructors, events, and enrolment calls to action in one
coherent frontend theme.

## What This Package Adds

- An `education` theme definition and preset.
- Education-specific CSS and page wrapper.
- Section renderers for learning-pathway content such as course catalogue,
  instructors, resources, events, enrolment CTA, FAQ, proof, and footer flows.
- Translated default course catalogue cards, event cards, and instructor role
  labels for localisable public education copy.
- Optional renderer awareness for Blog, Events, and Form Builder.
- A demo command that installs route-backed education demo pages.
- Theme health and management-page manifest contributions.

## Why It Matters

For a non-technical owner, this theme answers: "Can I show what people can
learn, why they should trust us, and how they enrol?" It gives that site shape
without asking the owner to understand renderer contracts.

For developers, the package keeps education presentation separate from
Foundation Theme. It registers through Capell theme APIs, ships no schema, and
can be tested as a package.

## Runtime Shape

- `EducationThemeServiceProvider` registers the theme when
  `capell-app/theme-education` is installed.
- `DemoCommand` calls `InstallEducationThemeDemoAction`.
- Core `ViewSectionRenderer` extra view data guards optional Blog, Events, and Form Builder
  sections.
- `ThemeEducationHealthCheck` exposes package health to diagnostics.

## Data And Persistence

This package owns no database tables, settings, models, or routes. It reads
Foundation Theme runtime data and Capell page content.

## Screenshot Plan

`docs/screenshots.json` describes deployment captures for the admin theme list,
seeded frontend demo, homepage, course catalogue, instructors, events,
enrolment, learning resources, and signed preview output. The committed
marketplace manifest currently uses the three real preview assets under
`docs/assets/marketplace`; the fuller route-backed PNG capture set still needs
to be generated.

## Verification

```bash
vendor/bin/pest packages/theme-education/tests --configuration=phpunit.xml
```

The focused tests cover theme definition, translated default education layouts,
manifest requirements, package-aware rendering, health diagnostics, and public
output safety.
