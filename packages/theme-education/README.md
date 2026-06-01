# Theme Education

Theme Education is a Capell theme for schools, course providers, training
teams, and learning programmes that need clear pathways from discovery to
enrolment.

## At A Glance

- Package: `capell-app/theme-education`
- Namespace: `Capell\ThemeStudio\Education\`
- Theme key: `education`
- Surfaces: frontend, console
- Service provider:
  `Capell\ThemeStudio\Education\EducationThemeServiceProvider`
- Demo command:
  `capell:theme-education-demo {--url=} {--languages=} {--sites=} {--force}`
- Extends: `capell-app/foundation-theme`
- Database impact: none

## Why It Helps Your Capell Workflow

- Site owners can launch an education-focused site with course, instructor,
  event, resource, FAQ, and enrolment sections already shaped for learning
  journeys.
- Editors can compose education pages through the normal Capell theme and Layout
  Builder workflow instead of commissioning a custom frontend for every course
  catalogue.
- Developers get a package-scoped renderer and demo installer without changing
  Foundation Theme or Capell core rendering contracts.

## Best Used With

- [Foundation Theme](../foundation-theme/README.md)
- [Blog](../blog/README.md) for resources and learning content.
- [Events](../events/README.md) for open days, sessions, and course events.
- [Form Builder](../form-builder/README.md) for enrolment or enquiry CTAs.
- [SEO Suite](../seo-suite/README.md) for course discovery metadata.

## What It Adds

- Registers the `education` theme definition and preset.
- Ships an education page wrapper and theme CSS.
- Adds education-specific section renderers for course catalogue, instructors,
  events, enrolment CTA, resources, FAQ, and supporting content blocks.
- Uses Core `ViewSectionRenderer` extra view data for optional Blog, Events, and Form Builder
  sections.
- Adds a demo install command backed by `InstallEducationThemeDemoAction`.
- Adds `ThemeEducationHealthCheck` and a Theme management page contribution.

## Runtime Surface

| Area                | Path                                               |
| ------------------- | -------------------------------------------------- |
| Provider            | `src/EducationThemeServiceProvider.php`            |
| Demo command        | `src/Console/Commands/DemoCommand.php`             |
| Demo action         | `src/Actions/InstallEducationThemeDemoAction.php`  |
| Renderer            | Core `ViewSectionRenderer` extra view data         |
| Theme management    | `src/Manifest/ThemeManagementPageContribution.php` |
| Health check        | `src/Health/ThemeEducationHealthCheck.php`         |
| Views               | `resources/views/page.blade.php`                   |
| CSS                 | `resources/css/theme-education.css`                |
| Screenshot manifest | `docs/screenshots.json`                            |

## Install Impact

- Adds a frontend theme renderer and console demo command.
- Adds no migrations, settings, models, package-owned routes, or admin
  resources.
- Depends on Foundation Theme and reads normal Capell page/theme runtime data.
- Optional sections degrade through package-aware renderer integration checks
  when Blog, Events, or Form Builder are not installed.

## Docs

- [Docs index](docs/README.md)
- [Overview](docs/overview.md)
- [Creating a Capell theme](../../docs/creating-a-theme.md)
- [Theme Screenshot QA Playbook](../../docs/theme-screenshot-qa-playbook.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-education/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Keep public theme output free of admin URLs, signed preview URLs, editor
  selectors, theme internals, model IDs, and permission metadata.
- Keep optional package checks inside the service provider/renderer layer, not
  public Blade.
- Keep the docs aligned with `EducationThemeServiceProvider::definition()` when
  section keys, presets, or optional integrations change.
