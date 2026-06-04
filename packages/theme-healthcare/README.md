# Editorial Healthcare Theme

Appointment-led healthcare theme for Capell, shipped under the existing `healthcare` theme key.

## At A Glance

- Package: `capell-app/theme-healthcare`
- Namespace: `Capell\ThemeStudio\Healthcare\`
- Capell dependencies: `capell-app/core`, `capell-app/foundation-theme`

## Why It Helps Your Capell Workflow

- Provides Editorial Healthcare renderer views for clinics and healthcare providers built on Capell.
- Helps owners launch healthcare pages with service finding, clinician cards, booking panels, care pathway comparison, trust proof, resources, and location surfaces.
- Gives developers a focused theme package that reuses Foundation Theme conventions instead of hard-coding product layouts into content.

## Best Used With

- [Foundation Theme](../foundation-theme/README.md)
- [Blog](../blog/README.md)
- [Events](../events/README.md)
- [Theme Agency](../theme-agency/README.md)
- [Theme Corporate](../theme-corporate/README.md)

## What It Adds

- Editorial Healthcare theme for Capell.
- Public views for utility bar, navigation, appointment hero, service finder, services, clinicians, booking, events, proof, comparison, resources, contact, CTA, and footer sections.
- Booking, event, and blog teaser renderers receive optional Form Builder, Events, and Blog availability from the service provider, then render enhanced or neutral panels without querying from Blade.
- The page wrapper includes a real skip-link target, event carousel controls use package translations, and rendered images include explicit loading, decoding, and dimension attributes.

## Why It Matters

**For developers:** Adds a renderer package that uses Foundation Theme runtime contracts while leaving content models unchanged.

**For teams:** Provides a healthcare-oriented visual option for clinic and service-line sites managed through the normal Theme admin page and install flow.

## Built With

This package makes its Composer dependencies visible because they are part of the value proposition, not just plumbing. When an upstream package has a public repository, its linked preview card points readers back to the maintainers so their work gets proper credit.

**Capell packages used here**

- [Capell Core](https://github.com/capell-app/core)
- [Capell Foundation Theme](../foundation-theme/README.md)
- [Capell Blog](../blog/README.md)
- [Capell Events](../events/README.md)
- [Capell Form Builder](../form-builder/README.md)

**Open-source packages used here**

- No extra third-party Composer package beyond the Capell package stack is required here.

## Screens And Workflow

Screenshots are generated from [docs/screenshots.json](docs/screenshots.json) during package deployment.

- Theme admin list showing Editorial Healthcare.
- Frontend page rendered with Editorial Healthcare theme.
- Theme preview URL output.

## Technical Shape

- HealthcareThemeServiceProvider registers the Editorial Healthcare renderer.
- `capell.json` declares `themeKey: "healthcare"` and `extends: "capell-app/foundation-theme"`.
- Uses Foundation Theme runtime data and standard section keys, while rendering its own page and section Blade views.
- Ships Blade resources for the page wrapper, service discovery sections, booking panel, event panel, comparison, proof, blog teaser, CTA, and footer views.
- No migrations, config, routes, models, admin navigation, or package-owned settings are present.
- Public theme output must stay free of package identifiers, signed admin URLs, Filament/editor markers, and other authoring metadata.
- Keep the skip link target, translated controls, and image loading attributes in place when changing public section views.

## Code Map

| Area      | Path                                  | Purpose                                             |
| --------- | ------------------------------------- | --------------------------------------------------- |
| Resources | `packages/theme-healthcare/resources` | Views, translations, assets, and package resources. |
| Tests     | `packages/theme-healthcare/tests`     | Package-level Pest coverage.                        |

## Data And Persistence

- This package does not own data.
- It consumes theme runtime settings and core page content.

## Install Impact

- Adds the Editorial Healthcare renderer to theme system.
- No database changes.
- No admin navigation by itself.
- No public routes by itself.

## Install And Setup

- Install with `composer require capell-app/theme-healthcare` in the host Capell application.
- Seed the Editorial Healthcare preview pages with `php artisan capell:theme-healthcare-demo --url=https://demo.test --sites=Demo --languages=en --force`.
- The Extensions installer demo checkbox and full Capell demo install use the same manifest demo command path.
- In this repository, verify package changes with `vendor/bin/pest`; do not use `php artisan`.
- For screenshots, use a disposable Capell app with the core stack, Layout Builder, Foundation Theme, and only this theme package installed.

## Admin And Access

- The package appears through the core Themes resource after install. It does not add a package-owned admin page.

## Common Pitfalls

- Install Layout Builder before Foundation Theme in the disposable harness.
- Install Foundation Theme before using this renderer.
- Build both frontend and Filament assets before browser capture.
- Keep Theme Studio settings aligned with the `healthcare` preset; stale settings from another theme can make screenshots misleading.
- Do not install a Studio metapackage; this package installs independently.

## Docs

- [docs index](docs/README.md)
- [credits-and-acknowledgements.md](docs/credits-and-acknowledgements.md)
- [overview.md](docs/overview.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/theme-healthcare/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Theme output is public output. Keep admin-only metadata and editor hooks out of rendered markup.
