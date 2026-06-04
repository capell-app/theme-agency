# Theme Corporate

Trust-led corporate theme for Capell.

## At A Glance

- Package: `capell-app/theme-corporate`
- Namespace: `Capell\ThemeStudio\Corporate\`
- Capell dependencies: `capell-app/core`, `capell-app/foundation-theme`

## Why It Helps Your Capell Workflow

- Provides restrained renderer views for B2B, public-sector, and professional-service Capell sites.
- Helps owners launch a trust-led site with a theme direction that fits formal content and service pages.
- Renders translated corporate defaults for hero credibility stats, proof/gallery controls, and content listing labels while allowing page render data to supply real stats.
- Gives developers a corporate theme package to customize without weakening the shared Foundation Theme base.

## Best Used With

- [Foundation Theme](../foundation-theme/README.md)
- [Theme Agency](../theme-agency/README.md)
- [Theme SaaS](../theme-saas/README.md)

## What It Adds

- Trust-led corporate theme for Capell.

## Why It Matters

**For developers:** Adds a renderer package that plugs into Foundation Theme contracts and runtime settings.

**For teams:** Provides a corporate visual option for sites that need restrained, trust-focused presentation through the normal Theme admin page and install flow.

## Built With

This package makes its Composer dependencies visible because they are part of the value proposition, not just plumbing. When an upstream package has a public repository, its linked preview card points readers back to the maintainers so their work gets proper credit.

**Capell packages used here**

- [Capell Core](https://github.com/capell-app/core)
- [Capell Foundation Theme](../foundation-theme/README.md)

**Open-source packages used here**

- No extra third-party Composer package beyond the Capell package stack is required here.

## Screens And Workflow

Screenshots are generated from [docs/screenshots.json](docs/screenshots.json) during package deployment.

- Themes admin list showing Corporate.
- Frontend page rendered with Corporate theme sections.
- Theme preview URL output from the host signed preview route.

## Technical Shape

- CorporateThemeServiceProvider registers the renderer.
- `capell.json` declares `themeKey: "corporate"` and `extends: "capell-app/foundation-theme"`.
- Uses Foundation Theme runtime data and standard section keys, while rendering its own page and section Blade views.
- Hero credibility stats can be supplied through section render data; translated corporate defaults are used when no custom stats exist.
- Proof/gallery controls and content-listing labels use package translations so public UI copy stays localisable.
- Ships Blade resources for the page wrapper and standard theme sections.
- No migrations, config, routes, models, admin navigation, or package-owned settings are present.

## Code Map

| Area      | Path                                 | Purpose                                                |
| --------- | ------------------------------------ | ------------------------------------------------------ |
| Resources | `packages/theme-corporate/resources` | Blade page wrapper, section views, and Boost guidance. |
| Tests     | `packages/theme-corporate/tests`     | Package-level Pest coverage.                           |

## Data And Persistence

- This package does not own data.
- It consumes theme runtime settings and core page content.

## Install Impact

- Adds a Corporate renderer to theme system.
- No database changes.
- No admin navigation by itself.
- No public routes by itself.

## Install And Setup

- Install with `composer require capell-app/theme-corporate` in the host Capell application.
- Seed the Corporate preview pages with `php artisan capell:theme-corporate-demo --url=https://demo.test --sites=Demo --languages=en --force`.
- The Extensions installer demo checkbox and full Capell demo install use the same manifest demo command path.
- In this repository, verify package changes with `vendor/bin/pest`; do not use `php artisan`.

## Admin And Access

- None proven in this package directory.

## Common Pitfalls

- Install Foundation Theme before using this renderer.
- Install Layout Builder before running `capell:foundation-theme-setup`; Foundation Theme layout defaults need `blocks`.
- Build frontend and Filament assets in demo apps. Foundation Theme frontend CSS needs the npm dependencies published by the theme stack.
- Keep Theme Studio's active preset aligned with this theme (`boardroom`, `civic`, `advisory`, `integrity`, `enterprise-trust`, or `public-ledger`). A preset from another theme will fail when Corporate renders.
- Public output must not expose package/theme identifiers. Token CSS filenames are intentionally opaque.
- Do not install a Studio metapackage; this package installs independently.

## Docs

- [docs index](docs/README.md)
- [credits-and-acknowledgements.md](docs/credits-and-acknowledgements.md)
- [overview.md](docs/overview.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-corporate/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Theme output is public output. Keep admin-only metadata and editor hooks out of rendered markup.
