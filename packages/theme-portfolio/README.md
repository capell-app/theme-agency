# Theme Portfolio

Theme Portfolio is a Capell theme for creators, consultants, independent
studios, and case-study-led service businesses that need work, proof, media kit,
and newsletter sections.

## At A Glance

- Package: `capell-app/theme-portfolio`
- Namespace: `Capell\ThemeStudio\Portfolio\`
- Theme key: `portfolio`
- Surfaces: frontend, console
- Service provider:
  `Capell\ThemeStudio\Portfolio\PortfolioThemeServiceProvider`
- Demo command:
  `capell:theme-portfolio-demo {--url=} {--languages=} {--sites=} {--force}`
- Extends: `capell-app/foundation-theme`
- Database impact: none

## Why It Helps Your Capell Workflow

- Site owners can show work, services, testimonials, case studies, speaking or
  media-kit content, and newsletter conversion in one portfolio-focused theme.
- Editors can maintain portfolio pages through normal Capell page/theme tools
  rather than hardcoded case-study templates.
- Developers get a renderer package with optional Content Sections, Media
  Library, and Newsletter checks for richer studio pages.

## Best Used With

- [Foundation Theme](../foundation-theme/README.md)
- [Content Sections](../content-sections/README.md) for case-study content.
- [Media Library](../media-library/README.md) for work-grid media.
- [Newsletter](../newsletter/README.md) for audience capture.
- [Theme Agency](../theme-agency/README.md) when comparing portfolio and agency
  positioning.

## What It Adds

- Registers the `portfolio` theme definition and preset.
- Ships a portfolio page wrapper and theme CSS.
- Adds section renderers for work grid, case studies, services, testimonials,
  speaking/media kit, newsletter, proof, CTA, and supporting content blocks.
- Uses Core `ViewSectionRenderer` extra view data for optional Content Sections, Media Library, and
  Newsletter sections.
- Renders the newsletter form only when hydrated with a real capture action;
  static states use translated CTA copy instead of a dead form post.
- Adds a demo install command backed by `InstallPortfolioThemeDemoAction`.
- Adds `ThemePortfolioHealthCheck` and a Theme management page contribution.

## Runtime Surface

| Area                | Path                                               |
| ------------------- | -------------------------------------------------- |
| Provider            | `src/PortfolioThemeServiceProvider.php`            |
| Demo command        | `src/Console/Commands/DemoCommand.php`             |
| Demo action         | `src/Actions/InstallPortfolioThemeDemoAction.php`  |
| Renderer            | Core `ViewSectionRenderer` extra view data         |
| Theme management    | `src/Manifest/ThemeManagementPageContribution.php` |
| Health check        | `src/Health/ThemePortfolioHealthCheck.php`         |
| Views               | `resources/views/page.blade.php`                   |
| CSS                 | `resources/css/theme-portfolio.css`                |
| Screenshot manifest | `docs/screenshots.json`                            |

## Install Impact

- Adds a frontend theme renderer and console demo command.
- Adds no migrations, settings, models, package-owned routes, or admin
  resources.
- Depends on Foundation Theme and reads normal Capell page/theme runtime data.
- Optional sections stay guarded when Content Sections, Media Library, or
  Newsletter are not installed.

## Product Direction

Keep this package separate only while it offers a genuinely distinct portfolio
and case-study lane. If it overlaps with Theme Agency after theme improvements,
merge the useful work, case-study, media-kit, and newsletter sections into the
stronger studio theme rather than maintaining two near-identical packages.

## Docs

- [Docs index](docs/README.md)
- [Overview](docs/overview.md)
- [Creating a Capell theme](../../docs/creating-a-theme.md)
- [Theme Screenshot QA Playbook](../../docs/theme-screenshot-qa-playbook.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-portfolio/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Keep public theme output free of admin URLs, signed preview URLs, editor
  selectors, theme internals, model IDs, and permission metadata.
- Keep optional package checks inside the service provider/renderer layer, not
  public Blade.
- Keep newsletter capture actions hydrated through section data; do not
  reintroduce `action="#"` forms.
- Keep the docs aligned with `PortfolioThemeServiceProvider::definition()` when
  section keys, presets, or optional integrations change.
