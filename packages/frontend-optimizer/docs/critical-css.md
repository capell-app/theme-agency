# Critical CSS

Frontend Optimizer generates above-the-fold CSS from the public page URL. It does not use a separate PHP critical-CSS package. The default generator is `PlaywrightCriticalCssGenerator`, which runs `resources/js/generate-critical-css.mjs` with the package-local `playwright` dependency and opens the page in Chromium.

Install the package in a host Capell app with Composer:

```bash
composer require capell-app/frontend-optimizer
```

For local package development, point Composer at `../packages/capell/capell-packages-4/packages/frontend-optimizer` as a path repository, then require the same package name.

## Generation Flow

1. `PrepareRenderProfileAction` receives the resolved asset sets, render context, optimization scope, and a representative public URL.
2. `ResolveRenderProfileAction` builds a profile signature from assets, context, scope, and critical CSS settings. The hash changes when relevant viewport, fold, or inline-size settings change.
3. The profile is persisted and `GenerateCriticalCssJob` is queued when automatic generation is enabled and no generated CSS exists.
4. `GenerateCriticalCssAction` calls `CriticalCssGenerator`; the default binding is `PlaywrightCriticalCssGenerator`.
5. The generator writes a JSON payload containing the target URL, eligible stylesheet paths, configured viewports, fold settings, wait strategy, timeout, and max inline CSS size.
6. The Playwright script opens the real URL in Chromium for each viewport and inspects `document.styleSheets` against the rendered DOM.
7. CSS rules are kept when their selectors match visible elements intersecting the configured fold. Matching `@media` and `@supports` rules are preserved around their matching nested rules. `@font-face` and keyframes are included.
8. The generated CSS is stored on the local disk under `capell/frontend-optimizer/critical-css/{profile-hash}.css`.
9. Later renders inline that file as `<style data-critical-css>` before profile asset tags.

This URL-based flow is deliberate. It lets the generator see the final Blade output, Layout Builder content, theme markup, package render hooks, responsive CSS, and browser layout instead of guessing from source templates.

## Fold And Viewports

The configured viewports come from `FrontendOptimizerSettings::viewports`, falling back to `capell-frontend-optimizer.playwright.viewports`. The current defaults are:

```php
[
    ['width' => 390, 'height' => 844],
    ['width' => 1440, 'height' => 900],
]
```

For each viewport, the script treats an element as above the fold when its visible rectangle intersects:

```text
effectiveFoldHeight = viewportHeight * fold_multiplier + extra_fold_pixels
```

Use `fold_multiplier` to include more or less than one screenful. Use `extra_fold_pixels` for a fixed buffer below the viewport.

## Eligible Stylesheets

Only CSS assets are considered. A stylesheet is eligible when the asset signature marks it as `critical_eligible`, or when its slot/loading strategy indicates it can affect initial rendering:

- `base`, `head`, or `above_fold` slots unless the loading strategy is lazy, idle, or interaction.
- `critical`, `blocking`, or `preload` loading strategies.

If the profile has no eligible stylesheet paths, the browser-side collector falls back to inspecting all same-origin readable stylesheets.

## Fallbacks And Failures

Public rendering must not block on generation. Until CSS exists, `RenderProfileAssetRenderer` renders normal stylesheet output for the profile. If generation fails, `GenerateCriticalCssAction` records the failed run and leaves rendering on the fallback path.

Generated CSS is only inlined when all of these are true:

- critical CSS is enabled in `FrontendOptimizerSettings`
- the page type has not opted out
- the stored CSS file exists
- the file is within `max_inline_css_bytes`

If any check fails, the renderer keeps normal asset tags and omits `data-critical-css`.

## Page Type Opt-Out

Some page types are not worth optimizing. The package adds a page type setting:

```text
meta.frontend_optimizer.disable_critical_css
```

When this value is `true`, pages using that page type do not queue generation and do not inline generated critical CSS. The normal profile asset output still renders.

## Settings

The extension settings page is backed by `FrontendOptimizerSettings` and `FrontendOptimizerSettingsSchema`.

| Setting                                       | Purpose                                                                     |
| --------------------------------------------- | --------------------------------------------------------------------------- |
| `frontend_optimizer.enable_critical_css`      | Enables inline generated CSS.                                               |
| `frontend_optimizer.automatic_generation`     | Queues generation when a profile is missing CSS.                            |
| `frontend_optimizer.profile_scope`            | Default optimization scope.                                                 |
| `frontend_optimizer.viewports`                | Viewport width/height pairs for extraction.                                 |
| `frontend_optimizer.fold_multiplier`          | Multiplies viewport height for fold detection.                              |
| `frontend_optimizer.extra_fold_pixels`        | Adds a fixed buffer below the fold.                                         |
| `frontend_optimizer.playwright_wait_strategy` | Browser wait strategy: `load`, `domcontentloaded`, or `networkidle`.        |
| `frontend_optimizer.playwright_timeout`       | Process timeout in seconds.                                                 |
| `frontend_optimizer.max_inline_css_bytes`     | Maximum generated CSS size allowed for inline output.                       |
| `frontend_optimizer.debug_query_support`      | Allows non-production debug query support for isolated critical CSS checks. |

The Node binary still comes from config and can be overridden with `CAPELL_FRONTEND_OPTIMIZER_NODE`.

## Verification

Run the package tests from the repository root:

```bash
vendor/bin/pest packages/frontend-optimizer/tests --configuration=phpunit.xml
```
