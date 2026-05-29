# Theme Corporate

Status: **Available, no schema impact** · Kind: **theme** · Tier: **free** · Bundle: **themes** · Contexts: **frontend** · Product group: **Capell Themes**

This page is the consolidated implementation overview for the Theme Corporate package. It is extracted from the package README, service providers, migrations, config files, routes, resources, models, actions, and the shared Capell ERD notes where available.

## What This Package Adds

Theme Corporate is a standalone Capell theme package. It registers the `corporate` theme key, extends Foundation Theme, and adds restrained renderer views for B2B, public sector, and professional-service sites. Treat it as a polished basic/business preset unless a future rewrite gives it a deeper enterprise workflow; if it remains only a standard section stack, fold the best restrained defaults into Foundation.

- Corporate theme service provider.
- Theme renderer/views for corporate theme output.
- Dependency on Foundation Theme.

## Developer Notes

Adds a renderer package that plugs into Foundation Theme contracts and runtime settings.

- CorporateThemeServiceProvider registers the renderer.
- `capell.json` declares `themeKey: "corporate"` and `extends: "capell-app/foundation-theme"`.
- Uses Foundation Theme runtime data and standard section keys, while rendering its own page and section Blade views.
- Ships Blade resources for the page wrapper and standard theme sections.
- No migrations, config, routes, models, admin navigation, or package-owned settings are present.

## Operational Notes

Provides a corporate visual option for sites that need restrained, trust-focused presentation through the normal Theme admin page and install flow.

- Adds a Corporate renderer to theme system.
- No database changes.
- No admin navigation by itself.
- No public routes by itself.

## Data And Retention

- This package does not own data.
- It consumes theme runtime settings and core page content.

## Screenshot Plan

- Themes admin list showing the Corporate theme record in the host `ThemeResource`.
- Seeded frontend page at `/theme-corporate-demo` rendering navigation, hero, features, proof, content listing, CTA, and footer.
- Temporary signed `capell.admin.theme-preview` output for an authenticated administrator.

## Pitfalls

- Install Foundation Theme before using this renderer.
- Install Layout Builder before running `capell:foundation-theme-setup`; Foundation Theme layout defaults need the `blocks` table.
- Build both frontend and Filament assets in demo apps. The frontend build needs Foundation Theme npm dependencies such as `swiper`, `tippy.js`, `@tailwindcss/typography`, `@awcodes/alpine-floating-ui`, and `@ryangjchandler/alpine-tooltip`.
- Theme Studio settings must use a Corporate preset such as `boardroom`, `civic`, or `advisory`. A preset from another theme fails at render time.
- Public theme token CSS filenames must stay opaque. Do not expose theme keys or preset keys in cached public HTML.
- Do not install a Studio metapackage; this package installs independently.

## Verification

- `vendor/bin/pest packages/theme-corporate/tests --configuration=phpunit.xml` passes.
- The isolated harness rendered `/theme-corporate-demo` and the signed theme preview without 500s.
- Public HTML for the seeded frontend demo was scanned for `capell-theme`, `data-capell-theme`, `theme-corporate`, `signed`, `filament`, `editor`, and `/admin`.

## Package Manifest

- Composer name: `capell-app/theme-corporate`
- Theme key: `corporate`
- Product group: Capell Themes
- Kind: theme
- Tier: free
- Bundle: themes
- Contexts: `frontend`
- Requires: `capell-app/foundation-theme`
- Optional dependencies: None listed.

## Admin Surfaces

- None proven in this package directory.

## Commands

- None proven in this package directory.

## Routes And Config

- None proven in this package directory.

## Permissions And Gates

- None proven in this package directory.

## Migrations

- None proven in this package directory.

## ERD Excerpt

This package has no committed ERD excerpt. Use implementation notes and extension points instead of inventing schema.

## Screenshot Automation

Deployment should read [screenshots.json](screenshots.json), install the package with demo data, resolve each admin surface or frontend URL, and write images to `public/docs/screenshots/packages/theme-corporate`.

- Themes admin list showing Corporate.
- Frontend page rendered with every Corporate section.
- Theme preview URL output.
