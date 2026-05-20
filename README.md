# Theme Agency

Expressive agency theme for Capell.

## At A Glance

- Package: `capell-app/theme-agency`
- Namespace: `Capell\ThemeStudio\Agency\`
- Capell dependencies: `capell-app/core`, `capell-app/foundation-theme`

## What It Adds

- Expressive agency theme for Capell.

## Why It Matters

**For developers:** Adds a renderer package that plugs into Foundation Theme rather than changing Capell core rendering contracts.

**For teams:** Provides an agency-focused visual option for sites managed through the normal Theme admin page and install flow.

## Built With

This package makes its Composer dependencies visible because they are part of the value proposition, not just plumbing. When an upstream package has a public repository, its linked preview card points readers back to the maintainers so their work gets proper credit.

**Capell packages used here**

- [Capell Core](https://github.com/capell-app/core)
- [Capell Foundation Theme](../foundation-theme/README.md)

**Open-source packages used here**

- No extra third-party Composer package beyond the Capell package stack is required here.

## Screens And Workflow

Screenshots are generated from [docs/screenshots.json](docs/screenshots.json) during package deployment.

- Themes admin list showing Agency.
- Frontend page rendered with Agency theme sections.
- Theme preview URL output from the host signed preview route.

## Technical Shape

- AgencyThemeServiceProvider registers the renderer.
- `capell.json` declares `themeKey: "agency"` and `extends: "capell-app/foundation-theme"`.
- Uses Foundation Theme runtime data and standard section keys, while rendering its own page and section Blade views.
- Ships Blade resources for the page wrapper and standard theme sections.
- No migrations, config, routes, models, admin navigation, or package-owned settings are present.

## Code Map

| Area      | Path                              | Purpose                                                |
| --------- | --------------------------------- | ------------------------------------------------------ |
| Resources | `packages/theme-agency/resources` | Blade page wrapper, section views, and Boost guidance. |
| Tests     | `packages/theme-agency/tests`     | Package-level Pest coverage.                           |

## Data And Persistence

- This package does not own data.
- It reads theme runtime data and core page content through Foundation Theme.

## Install Impact

- Adds an Agency renderer to theme system.
- No database changes.
- No admin navigation by itself.
- No public routes by itself.

## Install And Setup

- Install with `composer require capell-app/theme-agency` in the host Capell application.
- In this repository, verify package changes with `vendor/bin/pest`; do not use `php artisan`.

## Admin And Access

- None proven in this package directory.

## Common Pitfalls

- Install Foundation Theme before using this renderer.
- Install Layout Builder before running `capell:foundation-theme-setup`; Foundation Theme layout defaults need `blocks`.
- Build frontend and Filament assets in demo apps. Foundation Theme frontend CSS needs the npm dependencies published by the theme stack.
- Keep Theme Studio's active preset aligned with this theme (`signal`, `gallery`, or `atelier`). A preset from another theme will fail when Agency renders.
- Public output must not expose package/theme identifiers. Token CSS filenames are intentionally opaque.
- Do not install a Studio metapackage; this package installs independently.

## Docs

- [credits-and-acknowledgements.md](docs/credits-and-acknowledgements.md)
- [overview.md](docs/overview.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-agency/tests --configuration=phpunit.xml
```

The public token filename guard is covered in Foundation Theme:

```bash
vendor/bin/pest packages/foundation-theme/tests/Unit/ThemeTokenStoreTest.php --configuration=phpunit.xml
```

## Maintenance Notes

- Theme output is public output. Keep admin-only metadata and editor hooks out of rendered markup.
