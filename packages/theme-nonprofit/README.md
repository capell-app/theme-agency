# Theme Nonprofit

Theme Nonprofit is a Capell theme for charities, civic organisations, campaigns,
and community teams that need impact stories, supporter journeys, and donation
or volunteer calls to action.

## At A Glance

- Package: `capell-app/theme-nonprofit`
- Namespace: `Capell\ThemeStudio\Nonprofit\`
- Theme key: `nonprofit`
- Surfaces: frontend, console
- Service provider:
  `Capell\ThemeStudio\Nonprofit\NonprofitThemeServiceProvider`
- Demo command:
  `capell:theme-nonprofit-demo {--url=} {--languages=} {--sites=} {--force}`
- Manifest extends: `default`
- Runtime extends: `default`
- Database impact: none

## Why It Helps Your Capell Workflow

- Site owners can present impact, campaigns, events, stories, and supporter
  actions in a site structure built for trust and conversion.
- Editors can build campaign and community pages using normal Capell page/theme
  tools instead of maintaining bespoke campaign templates.
- Developers get a renderer package with optional Campaign Studio, Form Builder,
  Events, and Blog checks for campaign, donation, event, and story sections.

## Best Used With

- [Capell Frontend](https://github.com/capell-app/frontend)
- [Campaign Studio](../campaign-studio/README.md) for campaign-led pages.
- [Form Builder](../form-builder/README.md) for volunteer or donation interest
  forms.
- [Events](../events/README.md) for community events.
- [Blog](../blog/README.md) for stories and updates.

## What It Adds

- Registers the `nonprofit` theme definition and preset.
- Ships an impact-led page wrapper, reachable skip-link target, and theme CSS.
- Adds section renderers for impact, campaigns, volunteer/donate, events,
  stories, contact, proof, CTA, and supporting content blocks.
- Uses Core `ViewSectionRenderer` extra view data for optional Campaign Studio, Form Builder,
  Events, and Blog sections.
- Adds a demo install command backed by `InstallNonprofitThemeDemoAction`.
- Adds `ThemeNonprofitHealthCheck` and a Theme management page contribution.

## Runtime Surface

| Area                | Path                                               |
| ------------------- | -------------------------------------------------- |
| Provider            | `src/NonprofitThemeServiceProvider.php`            |
| Demo command        | `src/Console/Commands/DemoCommand.php`             |
| Demo action         | `src/Actions/InstallNonprofitThemeDemoAction.php`  |
| Renderer            | Core `ViewSectionRenderer` extra view data         |
| Theme management    | `src/Manifest/ThemeManagementPageContribution.php` |
| Health check        | `src/Health/ThemeNonprofitHealthCheck.php`         |
| Views               | `resources/views/page.blade.php`                   |
| CSS                 | `resources/css/theme-nonprofit.css`                |
| Screenshot manifest | `docs/screenshots.json`                            |

## Install Impact

- Adds a frontend theme renderer and console demo command.
- Adds no migrations, settings, models, package-owned routes, or admin
  resources.
- Depends on Capell Frontend default theme and reads normal Capell page/theme runtime data.
- Optional sections stay guarded when Campaign Studio, Form Builder, Events, or
  Blog are not installed.

## Docs

- [Docs index](docs/README.md)
- [Overview](docs/overview.md)
- [Creating a Capell theme](../../docs/creating-a-theme.md)
- [Package Screenshot Automation](../../docs/package-screenshot-automation.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-nonprofit/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Keep public theme output free of admin URLs, signed preview URLs, editor
  selectors, theme internals, model IDs, and permission metadata.
- Keep optional package checks inside the service provider/renderer layer, not
  public Blade.
- Keep the skip link and `main-content` target together when changing the page
  shell.
- Keep the docs aligned with `NonprofitThemeServiceProvider::definition()` when
  section keys, presets, or optional integrations change.
