# Frontend Optimizer Overview

Frontend Optimizer records render profiles for public Capell pages and renders profile-aware CSS and JavaScript assets through the `@frontendOptimizerAssets(...)` Blade directive.

Use it when a site needs deterministic asset delivery per layout, widget set, site, locale, or theme context without hard-coding frontend bundles into theme views.

## What It Adds

- `@frontendOptimizerAssets(...)` for rendering the resolved asset profile.
- Critical CSS generation through `CriticalCssGenerator`, backed by Playwright by default.
- Extension settings for viewports, fold depth, generation behavior, and inline-size limits.
- A page type opt-out for pages that should not run above-the-fold CSS generation.
- Render profile actions for resolving, preparing, persisting, and storing profile manifests.
- `frontend_render_profiles` and `frontend_optimization_runs` tables.

## Install Impact

- Requires `capell-app/core` and `capell-app/frontend`.
- Adds one migration file that creates render profile and optimization run storage.
- Registers extension settings and a page type configurator for critical CSS controls.
- Frontend output is visible only where a theme or frontend view calls the Blade directive.

## Admin Surfaces

- Extension settings are registered through `FrontendOptimizerSettings` and `FrontendOptimizerSettingsSchema`.
- Page type critical CSS opt-out is registered through `FrontendOptimizerPageTypeConfigurator`.

## Frontend Surfaces

| Surface                   | Use case                                                                               | Screenshot                               |
| ------------------------- | -------------------------------------------------------------------------------------- | ---------------------------------------- |
| Blade directive output    | Verify that the active render profile emits the expected asset tags for a public page. | `frontend-optimizer-profile-assets`      |
| Critical CSS run artifact | Verify generated critical CSS and manifest metadata for a seeded page profile.         | `frontend-optimizer-critical-css-output` |

## Demo Setup

Install the core baseline plus `capell-app/frontend-optimizer`, run migrations through the host app, seed or render at least one public page, then capture the public page HTML around the `@frontendOptimizerAssets(...)` output. A useful screenshot requires a theme fixture that actually calls the directive.

## Screenshot Coverage

The package needs screenshot coverage for asset output, not for admin navigation. Captures should include the public page and enough response/source inspection to prove the expected profile assets were emitted.

The screenshot runner contract is defined in [screenshots.json](screenshots.json). Those required captures are still pending and are not marketplace screenshots yet. `capell.json` should only list committed marketplace assets under `docs/assets/marketplace/`; future runner output under `docs/screenshots/` must be captured, reviewed, and copied or committed as marketplace assets before the manifest claims it.

## Known Risks

- Empty screenshots are likely unless the demo theme calls the directive.
- Playwright-backed critical CSS generation depends on the host runtime and browser availability.
- Asset profile keys vary by render context; demo data should state the site, locale, layout, and theme used for capture.
- Critical CSS generation should use a representative public URL for the profile; using an unusual page for a shared layout can produce a less useful above-the-fold subset.
- Critical CSS generation completion invalidates through the frontend cache registry without importing HTML Cache internals. The invalidation is intentionally broad today because render profiles do not record exact cached URL coverage.

## Related Guides

- [Critical CSS](critical-css.md)
- [Assets And Render Profiles](assets-and-render-profiles.md)

## Verification

Run package tests from the repository root:

```bash
vendor/bin/pest packages/frontend-optimizer/tests --configuration=phpunit.xml
```
