# Editorial Commerce Theme

Status: **Available, no schema impact** · Kind: **theme** · Tier: **premium** · Bundle: **themes** · Contexts: **frontend** · Product group: **Capell Themes**

This page documents the renderer-only Editorial Commerce theme package from the code that ships in this repository. The package keeps the existing Composer package and `commerce` theme key while replacing the old visual layer with the Editorial Commerce product-led direction.

## What This Package Adds

Editorial Commerce is a standalone Capell theme package. It registers the `commerce` theme key, extends Foundation Theme, and adds product-focused renderer views for catalog and retail sites.

- Editorial Commerce theme service provider.
- Theme renderer/views for Editorial Commerce page output.
- Section Blade views for navigation, hero, product finder, collections, product grid, comparison, catalog, proof, blog teaser, CTA, and footer.
- Catalog and blog teaser renderers that receive optional Shopify Commerce and Blog availability before Blade renders.
- Hero trust badges can be supplied by page render data, with translated retail fallback badges.
- Dependency on Foundation Theme.

## Developer Notes

Adds a renderer package that uses Foundation Theme runtime contracts while leaving content models unchanged.

- CommerceThemeServiceProvider registers the Editorial Commerce renderer.
- `capell.json` declares `themeKey: "commerce"` and `extends: "capell-app/foundation-theme"`.
- Uses Foundation Theme runtime data and standard section keys, while rendering its own page and section Blade views.
- Ships Blade resources for the page wrapper, product discovery sections, catalog panel, comparison, proof, blog teaser, CTA, and footer views.
- Translates hero badge and catalog highlight copy through `capell-theme-commerce::generic`.
- No migrations, config, routes, models, admin navigation, or package-owned settings are present.
- Public theme output must not expose package identifiers, signed admin URLs, Filament/editor markers, or other authoring metadata. The package test renders every section and checks for those leak tokens.

## Operational Notes

Provides a Editorial Commerce visual option for product sites managed through the normal Theme admin page and install flow.

- Adds the Editorial Commerce renderer to the theme system.
- No database changes.
- No admin navigation by itself.
- No public routes by itself.

## Data And Retention

- This package does not own data.
- It consumes theme runtime settings and core page content.

## Screenshot Plan

- Theme admin list showing Editorial Commerce.
- Frontend page rendered with Editorial Commerce at `/theme-commerce-demo`.
- Theme preview URL output from `capell.admin.theme-preview`.

## Pitfalls

- Install Layout Builder before Foundation Theme in a disposable harness; Foundation Theme setup expects the layout stack to be available.
- Install Foundation Theme before using this renderer.
- Build both frontend and Filament assets. Missing Foundation Theme manifests make screenshots fail even when the Composer install succeeds.
- Keep Theme Studio settings aligned with `activeTheme: "commerce"` and the `commerce` preset. Stale settings from another theme can render the wrong token set.
- Do not install a Studio metapackage; this package installs independently.

## Verification

- Run `vendor/bin/pest packages/theme-commerce/tests --configuration=phpunit.xml`.
- In a disposable Capell app, install only the core stack, Layout Builder, Foundation Theme, and `capell-app/theme-commerce`.
- Open `/theme-commerce-demo` anonymously and scan the response for `capell-theme`, `data-capell-theme`, `theme-commerce`, `signed`, `filament`, `editor`, and `/admin`.
- Capture the screenshots listed in [screenshots.json](screenshots.json).

## Package Manifest

- Composer name: `capell-app/theme-commerce`
- Theme key: `commerce`
- Product group: Capell Themes
- Kind: theme
- Tier: premium
- Bundle: themes
- Contexts: `frontend`
- Requires: `capell-app/foundation-theme`
- Optional dependencies: `capell-app/blog`, `capell-app/campaign-studio`, `capell-app/media-library`, `capell-app/shopify-commerce`.

## Admin Surfaces

- Core Themes resource: `ThemeResource:index`. This is a core/admin surface that lists the installed commerce theme once the package is installed.

## Commands

- Disposable screenshot route used by the audit harness: `/theme-commerce-demo`. The package itself does not register this route.

## Routes And Config

- None proven in this package directory.

## Permissions And Gates

- None proven in this package directory.

## Migrations

- None proven in this package directory.

## ERD Excerpt

This package has no committed ERD excerpt. Use implementation notes and extension points instead of inventing schema.

## Screenshot Automation

Deployment should read [screenshots.json](screenshots.json), install the package with demo data, resolve each admin surface or frontend URL, and write images to `packages/theme-commerce/docs/screenshots`.

- Theme admin list showing Editorial Commerce.
- Frontend page rendered with Editorial Commerce.
- Theme preview URL output.
