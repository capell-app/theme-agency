# Theme Local Services

Theme Local Services is a Capell theme for trades, clinics, consultancies, and
local operators that need service pages, location coverage, proof, and quote
requests.

## At A Glance

- Package: `capell-app/theme-local-services`
- Namespace: `Capell\ThemeStudio\LocalServices\`
- Theme key: `local-services`
- Surfaces: frontend, console
- Service provider:
  `Capell\ThemeStudio\LocalServices\LocalServicesThemeServiceProvider`
- Demo command:
  `capell:theme-local-services-demo {--url=} {--languages=} {--sites=} {--force}`
- Manifest extends: `default`
- Runtime extends: `default`
- Database impact: none

## Why It Helps Your Capell Workflow

- Site owners can present services, service areas, case studies, resources, and
  quote requests in a layout built for local conversion.
- Editors can build service-business pages through normal Capell page and theme
  tools instead of relying on one-off hardcoded landing pages.
- Developers get a renderer package with optional Blog and Form Builder checks
  for resource and quote-form sections.

## Best Used With

- [Capell Frontend](https://github.com/capell-app/frontend)
- [Form Builder](../form-builder/README.md) for quote requests.
- [Blog](../blog/README.md) for advice/resources.
- [SEO Suite](../seo-suite/README.md) for local service discovery.
- [Campaign Studio](../campaign-studio/README.md) when service pages also need
  campaign tracking.

## What It Adds

- Registers the `local-services` theme definition and preset.
- Ships a service-business page wrapper and theme CSS.
- Adds section renderers for services, service areas, quote forms, case studies,
  resources, contact, proof, CTA, and supporting content blocks.
- Uses Core `ViewSectionRenderer` extra view data for optional Blog and Form Builder sections.
- Renders service-area cards from hydrated area data (`label`, `url`, and
  optional `postcode`) with translated defaults.
- Adds a demo install command backed by `InstallLocalServicesThemeDemoAction`.
- Adds `ThemeLocalServicesHealthCheck` and a Theme management page contribution.

## Runtime Surface

| Area                | Path                                                  |
| ------------------- | ----------------------------------------------------- |
| Provider            | `src/LocalServicesThemeServiceProvider.php`           |
| Demo command        | `src/Console/Commands/DemoCommand.php`                |
| Demo action         | `src/Actions/InstallLocalServicesThemeDemoAction.php` |
| Renderer            | Core `ViewSectionRenderer` extra view data            |
| Theme management    | `src/Manifest/ThemeManagementPageContribution.php`    |
| Health check        | `src/Health/ThemeLocalServicesHealthCheck.php`        |
| Views               | `resources/views/page.blade.php`                      |
| CSS                 | `resources/css/theme-local-services.css`              |
| Screenshot manifest | `docs/screenshots.json`                               |

## Install Impact

- Adds a frontend theme renderer and console demo command.
- Adds no migrations, settings, models, package-owned routes, or admin
  resources.
- Depends on Capell Frontend default theme and reads normal Capell page/theme runtime data.
- Optional sections stay guarded when Blog or Form Builder are not installed.

## Docs

- [Docs index](docs/README.md)
- [Overview](docs/overview.md)
- [Creating a Capell theme](../../docs/creating-a-theme.md)
- [Package Screenshot Automation](../../docs/package-screenshot-automation.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-local-services/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Keep public theme output free of admin URLs, signed preview URLs, editor
  selectors, theme internals, model IDs, and permission metadata.
- Keep optional package checks inside the service provider/renderer layer, not
  public Blade.
- Keep public service-area cards pointed at real URLs or the contact anchor, or
  render them as static labels; do not reintroduce dead `href="#"` links.
- Keep the docs aligned with `LocalServicesThemeServiceProvider::definition()`
  when section keys, presets, or optional integrations change.
