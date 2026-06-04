# Editorial Healthcare Theme

Status: **Available, no schema impact** · Kind: **theme** · Tier: **premium** · Bundle: **themes** · Contexts: **frontend** · Product group: **Capell Themes**

This page documents the renderer-only Editorial Healthcare theme package from the code that ships in this repository. The package keeps the existing Composer package and `healthcare` theme key while providing an appointment-led clinical direction.

## What This Package Adds

Editorial Healthcare is a standalone Capell theme package. It registers the `healthcare` theme key, extends Foundation Theme, and adds appointment-led clinical renderer views for clinics and healthcare providers.

- Editorial Healthcare theme service provider.
- Theme renderer/views for Editorial Healthcare page output.
- Section Blade views for utility bar, navigation, appointment hero, service finder, services, clinicians, booking, events, proof, comparison, resources, contact, CTA, and footer.
- Booking, event, and blog teaser renderers that receive optional Form Builder, Events, and Blog availability before Blade renders.
- A public skip-link target, translated event carousel controls, and image loading attributes for hero, clinician, service, and resource card images.
- Dependency on Foundation Theme.

## Developer Notes

Adds a renderer package that uses Foundation Theme runtime contracts while leaving content models unchanged.

- HealthcareThemeServiceProvider registers the Editorial Healthcare renderer.
- `capell.json` declares `themeKey: "healthcare"` and `extends: "capell-app/foundation-theme"`.
- Uses Foundation Theme runtime data and standard section keys, while rendering its own page and section Blade views.
- Ships Blade resources for the page wrapper, service discovery sections, booking panel, event panel, comparison, proof, blog teaser, CTA, and footer views.
- No migrations, config, routes, models, admin navigation, or package-owned settings are present.
- Public theme output must not expose package identifiers, signed admin URLs, Filament/editor markers, or other authoring metadata. The package test renders every section and checks for those leak tokens.
- The rendered page keeps the skip link wired to `#main-content`; image-heavy sections include explicit loading and dimension attributes.

## Operational Notes

Provides an Editorial Healthcare visual option for clinic and service-line sites managed through the normal Theme admin page and install flow.

- Adds the Editorial Healthcare renderer to the theme system.
- No database changes.
- No admin navigation by itself.
- No public routes by itself.

## Data And Retention

- This package does not own data.
- It consumes theme runtime settings and core page content.

## Screenshot Plan

- Theme admin list showing Editorial Healthcare.
- Frontend page rendered with Editorial Healthcare at `/theme-healthcare-demo`.
- Theme preview URL output from `capell.admin.theme-preview`.

## Pitfalls

- Install Layout Builder before Foundation Theme in a disposable harness; Foundation Theme setup expects the layout stack to be available.
- Install Foundation Theme before using this renderer.
- Build both frontend and Filament assets. Missing Foundation Theme manifests make screenshots fail even when the Composer install succeeds.
- Keep Theme Studio settings aligned with `activeTheme: "healthcare"` and the `healthcare` preset. Stale settings from another theme can render the wrong token set.
- Do not install a Studio metapackage; this package installs independently.

## Verification

- Run `vendor/bin/pest packages/theme-healthcare/tests --configuration=phpunit.xml`.
- In a disposable Capell app, install only the core stack, Layout Builder, Foundation Theme, and `capell-app/theme-healthcare`.
- Open `/theme-healthcare-demo` anonymously and scan the response for `capell-theme`, `data-capell-theme`, `theme-healthcare`, `signed`, `filament`, `editor`, and `/admin`.
- Capture the screenshots listed in [screenshots.json](screenshots.json).

## Package Manifest

- Composer name: `capell-app/theme-healthcare`
- Theme key: `healthcare`
- Product group: Capell Themes
- Kind: theme
- Tier: premium
- Bundle: themes
- Contexts: `frontend`
- Requires: `capell-app/foundation-theme`
- Optional dependencies: `capell-app/blog`, `capell-app/events`, `capell-app/form-builder`.

## Admin Surfaces

- Core Themes resource: `ThemeResource:index`. This is a core/admin surface that lists the installed healthcare theme once the package is installed.

## Commands

- Disposable screenshot route used by the audit harness: `/theme-healthcare-demo`. The package itself does not register this route.

## Routes And Config

- None proven in this package directory.

## Permissions And Gates

- None proven in this package directory.

## Migrations

- None proven in this package directory.

## ERD Excerpt

This package has no committed ERD excerpt. Use implementation notes and extension points instead of inventing schema.

## Screenshot Automation

Deployment should read [screenshots.json](screenshots.json), install the package with demo data, resolve each admin surface or frontend URL, and write images to `packages/theme-healthcare/docs/screenshots`.

- Theme admin list showing Editorial Healthcare.
- Frontend page rendered with Editorial Healthcare.
- Theme preview URL output.
