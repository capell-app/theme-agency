# Theme Liquid Glass - Improvement & Growth Plan

> Package: capell-app/theme-liquid-glass · Kind: theme · Tier: free · Product group: Capell Foundation · Bundle: foundation · Status: Active

## 1. Snapshot

Theme Liquid Glass is a free Blade child theme for modern launch, editorial, directory, search, and contact pages. It registers theme key `liquid-glass`, runtime inheritance `extends: default`, three presets, a package page wrapper, seven section renderers, a demo command, and a critical health check that verifies the theme definition, required views, and marketplace media paths. The theme has no migrations, models, routes, permissions, settings, or admin resources. It already has public-output safety tests for authoring metadata, inline scripts, and database query calls in public Blade. Marketplace media now promotes committed route-rendered PNG captures from the theme-demo fixture set for homepage, landing sections, listing, detail/search-style, and contact surfaces.

## 2. Improvements (existing functionality)

1. **Require Foundation Theme explicitly.** The demo Action and command import `Capell\FoundationTheme\...` classes, and the theme extends the default renderer, but `composer.json` and `capell.json` only require Core and Frontend. Add `capell-app/foundation-theme` to Composer/manifest dependencies and update tests/docs so standalone package installs cannot miss the demo dependency. Evidence: `InstallLiquidGlassThemeDemoAction`, `DemoCommand`, `capell.json dependencies.requires`, `composer.json require`. - **S** - **Shipped**

2. **Fix skip/CTA anchor targets.** `page.blade.php` renders a skip link to `#main-content`; current tests pass that id through fixture content, but the package wrapper does not enforce the target. Hero and CTA fallback actions point to `#content`, while `content-listing.blade.php` has no `id="content"`. Add deterministic anchors or adjust fallback links so keyboard users and CTA clicks always land on real public sections. Evidence: `resources/views/page.blade.php`, `sections/hero.blade.php`, `sections/cta.blade.php`, `sections/content-listing.blade.php`. - **S** - **Shipped**

3. **Shipped: replace static marketplace SVGs with route-backed captures.** The screenshot contract now requires committed PNG captures under `docs/screenshots/`, and `capell.json` promotes those runner-backed outputs while keeping the extension card SVG as listing artwork. Evidence: `docs/screenshots.json`, `capell.json marketplace.screenshots`, `tests/Packages/Fixtures/theme-demo-layout-screenshots/liquid-glass/*.png`. - **M**

4. **Update setup docs for package and host context.** README/overview tell readers to run `php artisan capell:theme-liquid-glass-demo`; repo-local package workflow forbids `php artisan`, and host-app setup should be explicit about where the command runs. Rewrite docs to separate package tests from installed Capell app demo commands, and include the Foundation Theme dependency. Evidence: `README.md`, `docs/overview.md`, repository AGENTS instructions. - **S** - **Shipped**

5. **Strengthen health checks for install dependencies and assets.** The current health check verifies registry definition, view files, and marketplace media file existence. It should also catch missing demo dependency classes, missing CSS asset path, and mismatch between `definition()->includedSections` and registered section renderers. Evidence: `ThemeLiquidGlassHealthCheck`, `LiquidGlassThemeServiceProvider::definition()`, `sectionRenderers()`. - **S**

6. **Align cache metadata with public-output safety.** Manifest public-output safety says the theme is cache-safe, but `performance.cacheSafety.cacheable` is `false` and invalidation sources are empty. Decide whether Liquid Glass should inherit Foundation cacheability or explicitly document why this theme is not cacheable. Evidence: `capell.json performance.cacheSafety`, `PublicOutputSafetyTest`. - **S** - **Done 2026-06-16:** manifest metadata now marks public theme output cacheable by site/locale with core page, URL, domain, and translation invalidation sources; README/overview document host-owned content/theme invalidation and no package-owned invalidation queue.

7. **Add darker/mobile visual proof around the presets.** CSS defines a dark surface override and three presets, but tests mostly cover source strings and one rendered shell. Add fixture/screenshot coverage proving primary/accent/surface tokens remain legible across Clarity, Prism, and Graphite, including mobile navigation. Evidence: `resources/css/theme-liquid-glass.css`, `LiquidGlassThemeServiceProvider::definition()`. - **M**

## 3. Missing Features (gaps)

Capabilities declared: `theme-liquid-glass`, `theme-liquid-glass-frontend`.

- **Shipped: route-backed marketplace proof.** Homepage, landing sections, listing, detail/search-style, and contact captures now use committed route-rendered PNGs.
- **Demo content is generic.** Demo install delegates to Foundation's generic `ThemeDemoPageInstaller`, so the package does not yet prove Liquid Glass-specific homepage, landing, listing, search, and contact content depth.
- **No enforced main-content target.** Accessibility depends on render content providing an id that the shell links to.
- **No preset/browser visual QA.** Current tests do not render the theme in real browser viewports or assert dark/mobile readability.
- **No explicit cache decision.** The manifest marks output safe but not cacheable, which leaves site owners without a clear performance expectation.

## 4. Issues / Risks

1. **Important gap: package dependencies are incomplete.** Demo command use can fail in a standalone package install because Foundation Theme is not required. Recommended fix: add the dependency to Composer/manifest overlays and assert it in manifest tests. - **P2**

2. **Important gap: default anchors can be broken.** Skip and CTA navigation should not rely on host content conventions or missing section ids. Recommended fix: add the target id(s) in package-owned views and test rendered output. - **P2**

3. **Closed: marketplace screenshots were placeholders.** The manifest now promotes committed route-rendered PNG captures and tests require the docs screenshot outputs to exist. - **P2**

4. **Improvement: health check does not cover assets/dependencies.** Diagnostics can pass while CSS or demo dependency wiring is broken. Recommended fix: add dependency, asset, and renderer alignment checks. - **P3**

5. **Improvement: docs blur package repo and host app commands.** Developers need clear commands for package tests versus installed-site demo setup. Recommended fix: rewrite setup docs with explicit context. - **P3**

## 5. Marketplace & Positioning

Liquid Glass belongs in the free foundation lane: it should be the modern visual alternative to plain Foundation without adding data-model commitments. For site owners, the promise is a polished launch-ready look with standard Capell content. For developers, the value is a package-owned child theme that stays cache-safe, editor-free, and easy to extend through Theme Studio tokens.

**Current summary:** "A free glass interface theme for modern Capell sites with translucent panels, sharp content rhythm, and three token-driven presets."

**Improved summary:** "A free modern glass theme for launch, editorial, directory, search, and contact pages, powered by Theme Studio tokens."

**Improved description:** "Theme Liquid Glass gives Capell sites a modern translucent interface without adding package-owned schema or custom public routes. It extends the default Blade renderer, ships seven standard section views, and uses Theme Studio presets for bright launch pages, editorial glass layouts, and darker graphite surfaces. Install it when a site needs a polished free visual system that keeps public HTML cache-safe and free of authoring metadata."

**Media status:** Route-rendered PNG captures are committed and promoted for homepage, landing sections, listing, detail/search-style, and contact pages. Package-view captures now add Clarity, Prism, and Graphite preset proof, including a Graphite mobile navigation screenshot.

**Cross-sell:** Requires Foundation Theme. Complements Frontend, Layout Builder, Search, Form Builder, Blog, and SEO Suite depending on site content.

**Keywords/tags:** `liquid-glass`, `free-theme`, `foundation`, `blade`, `theme-studio`, `launch`, `editorial`, `directory`, `search`, `contact`.

## 6. Prioritized Roadmap

| Item                                                                                         | Bucket | Effort | Impact | Section ref |
| -------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Add explicit Foundation Theme dependency in Composer/manifest/tests                          | Done   | S      | High   | §2.1, §4.1  |
| Fix skip link and default CTA anchor targets                                                 | Done   | S      | High   | §2.2, §4.2  |
| Rewrite README/overview with package-repo versus host-app command context                    | Done   | S      | Medium | §2.4, §4.5  |
| Strengthen health check coverage for CSS, dependency classes, and section renderer alignment | Done   | S      | Medium | §2.5, §4.4  |
| Convert static SVG screenshot contract to required route-backed PNG captures                 | Done   | M      | High   | §2.3, §4.3  |
| Clarify cacheability metadata and invalidation expectations                                  | Done   | S      | Medium | §2.6        |
| Add preset/dark/mobile visual proof                                                          | Done   | M      | Medium | §2.7        |
| Add Liquid Glass-specific demo content depth for route-backed screenshots                    | Later  | M      | Medium | §3, §5      |

## 7. Verification

Plan-writing review only; no commands were run for this package yet. First implementation slice should start with:

```bash
vendor/bin/pest packages/theme-liquid-glass/tests --configuration=phpunit.xml
```

For renderer or dependency changes, include:

```bash
vendor/bin/pest packages/foundation-theme/tests packages/layout-builder/tests --configuration=phpunit.xml
```

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, views, and tests.
- [x] Comprehensive local review pass completed for theme definition, demo command, public views, accessibility, screenshots, docs, health checks, and tests.
- [x] Capell audience pass completed for site owners, package developers, and frontend/theme developers.
- [x] Approved implementation slices shipped for dependency alignment, skip/CTA anchors, and setup docs.
- [x] Focused Theme Liquid Glass verification passed.
- [x] Package tests passed.
- [ ] Repo preflight passed for changed files. Focused Pint and Composer path checks passed; full changed-file preflight remains open because the worktree has unrelated dirty files.
