# SaaS Theme

Product-led SaaS theme for Capell, shipped under the existing `saas` theme key.

## At A Glance

- Package: `capell-app/theme-saas`
- Namespace: `Capell\ThemeStudio\Saas\`
- Capell dependencies: `capell-app/core`, `capell-app/foundation-theme`

## Why It Helps Your Capell Workflow

- Provides SaaS renderer views for software and subscription sites built on Capell.
- Helps owners launch product-led pages with feature discovery, proof, comparison, calculator, and insight surfaces.
- Gives developers a focused theme package that reuses Foundation Theme conventions instead of hard-coding product layouts into content.

## Best Used With

- [Foundation Theme](../foundation-theme/README.md)
- [Theme Agency](../theme-agency/README.md)
- [Theme Corporate](../theme-corporate/README.md)

## What It Adds

- Product-led SaaS theme for Capell.
- Public views for navigation, hero, features, proof, content listing, comparison, calculator, CTA, footer, and blog sections.
- Blog index and article views that render custom insight markup when Blog is installed and marketing-safe fallback cards when it is not.

## Why It Matters

**For developers:** Adds a renderer package that uses Foundation Theme runtime contracts while leaving content models unchanged.

**For teams:** Provides a SaaS-oriented visual option for product sites managed through the normal Theme admin page and install flow.

## Built With

This package makes its Composer dependencies visible because they are part of the value proposition, not just plumbing. When an upstream package has a public repository, its linked preview card points readers back to the maintainers so their work gets proper credit.

**Capell packages used here**

- [Capell Core](https://github.com/capell-app/core)
- [Capell Foundation Theme](../foundation-theme/README.md)

**Open-source packages used here**

- No extra third-party Composer package beyond the Capell package stack is required here.

## Screens And Workflow

Screenshots are generated from [docs/screenshots.json](docs/screenshots.json) during package deployment.

- Theme admin list showing SaaS.
- Frontend page rendered with SaaS theme.
- Theme preview URL output.

## Technical Shape

- SaasThemeServiceProvider registers the SaaS renderer.
- `capell.json` declares `themeKey: "saas"` and `extends: "capell-app/foundation-theme"`.
- Uses Foundation Theme runtime data and standard section keys, while rendering its own page and section Blade views.
- Ships Blade resources for the page wrapper, standard theme sections, comparison, calculator, blog section, and blog page views.
- No migrations, config, routes, models, admin navigation, or package-owned settings are present.
- Public theme output must stay free of package identifiers, signed admin URLs, Filament/editor markers, and other authoring metadata.

## Code Map

| Area      | Path                            | Purpose                                             |
| --------- | ------------------------------- | --------------------------------------------------- |
| Resources | `packages/theme-saas/resources` | Views, translations, assets, and package resources. |
| Tests     | `packages/theme-saas/tests`     | Package-level Pest coverage.                        |

## Data And Persistence

- This package does not own data.
- It consumes theme runtime settings and core page content.

## Install Impact

- Adds the SaaS renderer to theme system.
- No database changes.
- No admin navigation by itself.
- No public routes by itself.

## Install And Setup

- Install with `composer require capell-app/theme-saas` in the host Capell application.
- Seed the SaaS preview pages with `php artisan capell:theme-saas-demo --url=https://demo.test --sites=Demo --languages=en --force`.
- The Extensions installer demo checkbox and full Capell demo install use the same manifest demo command path.
- In this repository, verify package changes with `vendor/bin/pest`; do not use `php artisan`.
- For screenshots, use a disposable Capell app with the core stack, Layout Builder, Foundation Theme, and only this theme package installed.

## Admin And Access

- The package appears through the core Themes resource after install. It does not add a package-owned admin page.

## Common Pitfalls

- Install Layout Builder before Foundation Theme in the disposable harness.
- Install Foundation Theme before using this renderer.
- Build both frontend and Filament assets before browser capture.
- Keep Theme Studio settings aligned with the `saas` preset; stale settings from another theme can make screenshots misleading.
- Do not install a Studio metapackage; this package installs independently.

## Docs

- [docs index](docs/README.md)
- [credits-and-acknowledgements.md](docs/credits-and-acknowledgements.md)
- [overview.md](docs/overview.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-saas/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Theme output is public output. Keep admin-only metadata and editor hooks out of rendered markup.
