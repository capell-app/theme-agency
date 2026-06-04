# Hero

Hero renders and seeds the default home-page hero widget used by Capell frontend themes.

## At A Glance

- Package: `capell-app/hero`
- Namespace: `Capell\Hero\`
- Surfaces: admin schema extenders, frontend Blade components, console
- Service providers: `packages/hero/src/Providers/HeroServiceProvider.php`
- Capell dependencies: `capell-app/admin`, `capell-app/core`, `capell-app/frontend`, `capell-app/layout-builder`
- Third-party dependencies: `lorisleiva/laravel-actions`, `spatie/laravel-package-tools`

## Why It Helps Your Capell Workflow

- Provides a default Capell home hero widget, rendering, and setup path so new sites start with a useful first-screen component.
- Adds theme, widget, and widget-asset controls for inheritable hero backgrounds and media.
- Helps designers and editors begin from a package-owned hero instead of hard-coding a one-off homepage header.
- Keeps the default hero small and replaceable while Layout Builder and themes own broader composition.

## Best Used With

- [Layout Builder](../layout-builder/README.md)
- [Foundation Theme](../foundation-theme/README.md)
- [Content Sections](../content-sections/README.md)

## What It Adds

- Hero renders and seeds the default home-page hero widget used by Capell frontend themes.
- Blade component: `capell::widget.hero`.
- Admin extenders: theme hero settings, widget hero settings, and widget-asset hero settings.
- Package setup or maintenance commands.

## Technical Shape

- HeroServiceProvider registers the hero view components and setup/demo commands.
- Filament schema extenders register hero background/media controls into host admin forms.
- Hero data objects shape the payload used by the default homepage hero view.
- The package is intentionally small because themes consume it as a shared visual primitive.

## Code Map

| Area      | Path                          | Purpose                                                             |
| --------- | ----------------------------- | ------------------------------------------------------------------- |
| Actions   | `packages/hero/src/Actions`   | Domain operations. Test these directly where possible.              |
| Data      | `packages/hero/src/Data`      | Structured payloads, form state, view models, and integration data. |
| Providers | `packages/hero/src/Providers` | Registration, extension hooks, routes, migrations, and resources.   |
| Resources | `packages/hero/resources`     | Views, translations, assets, and package resources.                 |
| Tests     | `packages/hero/tests`         | Package-level Pest coverage.                                        |

## Commands

- `capell:hero-setup {--force : Rebuild Hero-managed home layout defaults}` (packages/hero/src/Console/Commands/SetupCommand.php)

## Data And Persistence

- Data objects live in `src/Data/`; use them for payloads, form state, and view models.

## Extension Points

- Register Capell extension points, routes, migrations, settings, render hooks, and resources from service providers.

## Install Impact

- Adds default hero rendering support for frontend themes.
- Adds admin form extenders for inheritable hero background and media controls.
- Adds setup/demo commands for home hero content.
- Adds no standalone Filament admin screen, public route, settings screen, or package-owned table.

## Install And Setup

- Install with `composer require capell-app/hero` in the host Capell application.
- Install after Layout Builder when the host setup flow should seed default home-page hero layout data.
- In this repository, verify package changes with `vendor/bin/pest`; do not use `php artisan`.

## Docs

- [docs index](docs/README.md)
- [overview.md](docs/overview.md)
- [screenshots.json](docs/screenshots.json)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/hero/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Put behaviour changes in `src/Actions/`; UI classes, commands, and controllers should call actions instead of owning domain logic.
- Use package `Data` classes at boundaries instead of passing anonymous arrays between layers.
