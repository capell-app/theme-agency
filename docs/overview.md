# Theme Agency

Status: **Available, no schema impact** · Kind: **theme** · Tier: **premium** · Bundle: **themes** · Contexts: **frontend** · Product group: **Capell Themes**

This page is the consolidated implementation overview for the Theme Agency package. It is extracted from the package README, service providers, migrations, config files, routes, resources, models, actions, and the shared Capell ERD notes where available.

## What This Package Adds

Theme Agency is a standalone Capell theme package. It registers the `agency` theme key, extends Foundation Theme, and adds expressive renderer views for studio, portfolio, and brand-led sites.

- Agency theme service provider.
- Theme renderer/views for agency-style theme output.
- Dependency on Foundation Theme.

## Developer Notes

Adds a renderer package that plugs into Foundation Theme rather than changing Capell core rendering contracts.

- AgencyThemeServiceProvider registers the renderer.
- `capell.json` declares `themeKey: "agency"` and `extends: "capell-app/foundation-theme"`.
- Uses Foundation Theme runtime data and standard section keys, while rendering its own page and section Blade views.
- Ships Blade resources for the page wrapper and standard theme sections.
- No migrations, config, routes, models, admin navigation, or package-owned settings are present.

## Operational Notes

Provides an agency-focused visual option for sites managed through the normal Theme admin page and install flow.

- Adds an Agency renderer to theme system.
- No database changes.
- No admin navigation by itself.
- No public routes by itself.

## Data And Retention

- This package does not own data.
- It reads theme runtime data and core page content through Foundation Theme.

## Screenshot Plan

- Themes admin list showing the Agency theme record in the host `ThemeResource`.
- Seeded frontend page at `/theme-agency-demo` rendering navigation, hero, features, proof, content listing, CTA, and footer.
- Temporary signed `capell.admin.theme-preview` output for an authenticated administrator.

## Pitfalls

- Install Foundation Theme before using this renderer.
- Install Layout Builder before running `capell:foundation-theme-setup`; Foundation Theme layout defaults need the `blocks` table.
- Build both frontend and Filament assets in demo apps. The frontend build needs Foundation Theme npm dependencies such as `swiper`, `tippy.js`, `@tailwindcss/typography`, `@awcodes/alpine-floating-ui`, and `@ryangjchandler/alpine-tooltip`.
- Theme Studio settings must use an Agency preset such as `signal`, `gallery`, or `atelier`. A stale preset from another theme, such as `boardroom`, fails at render time.
- Public theme token CSS filenames must stay opaque. Do not expose theme keys or preset keys in cached public HTML.
- Do not install a Studio metapackage; this package installs independently.

## Verification

- `vendor/bin/pest packages/theme-agency/tests --configuration=phpunit.xml` passes.
- `vendor/bin/pest packages/foundation-theme/tests/Unit/ThemeTokenStoreTest.php --configuration=phpunit.xml` passes for opaque public token filenames.
- The isolated harness rendered `/theme-agency-demo` and the signed theme preview without 500s.
- Public HTML for the seeded frontend demo was scanned for `capell-theme`, `data-capell-theme`, `theme-agency`, `signed`, `filament`, `editor`, and `/admin`.

## Package Manifest

- Composer name: `capell-app/theme-agency`
- Theme key: `agency`
- Product group: Capell Themes
- Kind: theme
- Tier: premium
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

Deployment should read [screenshots.json](screenshots.json), install the package with demo data, resolve each admin surface or frontend URL, and write images to `public/docs/screenshots/packages/theme-agency`.

- Themes admin list showing Agency.
- Frontend page rendered with every Agency section.
- Theme preview URL output.
