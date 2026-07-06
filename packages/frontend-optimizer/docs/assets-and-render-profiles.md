# Assets And Render Profiles

Frontend Optimizer turns Capell public asset manifests into render profiles. A render profile is a stable hash built from the asset list, optimization scope, and context.

Use this package for public frontend CSS and JavaScript delivery. Do not use it for admin assets.

## Main Surfaces

| Surface                                  | Purpose                                                                |
| ---------------------------------------- | ---------------------------------------------------------------------- |
| `FrontendAssetSet`                       | Fluent builder for CSS and JavaScript asset definitions.               |
| `CapellFrontendAssetManifestRenderer`    | Converts Capell public asset manifests into optimizer render profiles. |
| `ResolveRenderProfileAction`             | Merges asset sets and creates the profile hash/signature.              |
| `StoreRenderProfileManifestAction`       | Persists the generated manifest.                                       |
| `GenerateCriticalCssAction`              | Runs the configured `CriticalCssGenerator`.                            |
| `@frontendOptimizerAssets($profileHash)` | Blade directive that renders stored assets for a profile.              |

See [Critical CSS](critical-css.md) for the URL-based Playwright generation flow, settings, fallbacks, and page type opt-out.

## Capell Frontend Manifest Integration

When the package is installed, `FrontendOptimizerServiceProvider` binds Capell's `FrontendAssetManifestRenderer` contract to `CapellFrontendAssetManifestRenderer`. Public frontend head rendering continues to receive the standard `FrontendAssetManifestData`, but the optimizer converts eager CSS and JavaScript requirements into a layout-scoped `FrontendAssetSet`.

The Foundation theme stylesheet (`theme-foundation:css`) is marked as critical-CSS eligible and rendered with a deferred loading strategy once generated critical CSS exists. Until that file exists, the renderer falls back to a blocking stylesheet link for the eligible stylesheet so public pages do not flash unstyled content. Other manifest CSS remains blocking, and JavaScript remains deferred/module output.

Render profile hashes are scoped to the layout/theme asset graph. Equivalent pages using the same layout, theme, and asset set reuse the same generated profile and critical CSS. If profile preparation or rendering cannot run safely, the package falls back to Capell frontend's default renderer.

## Build and Render a Profile

```php
use Capell\FrontendOptimizer\Actions\ResolveRenderProfileAction;
use Capell\FrontendOptimizer\Enums\OptimizationScope;
use Capell\FrontendOptimizer\Support\FrontendAssetSet;

$profile = ResolveRenderProfileAction::run(
    scope: OptimizationScope::Layout,
    context: [
        'layout' => 'marketing-page',
        'language' => 'en',
    ],
    assetSets: [
        FrontendAssetSet::make()->css('marketing-layout', 'vendor/capell/marketing/layout.css'),
    ],
    label: 'Marketing page',
);
```

Render stored assets from Blade after the manifest has been persisted:

```blade
@frontendOptimizerAssets ($profileHash)
```

## Config Keys

| Key                                                | Use                                           |
| -------------------------------------------------- | --------------------------------------------- |
| `capell-frontend-optimizer.enabled`                | Enables optimization behavior.                |
| `capell-frontend-optimizer.scope`                  | Default optimization scope.                   |
| `capell-frontend-optimizer.paths.manifests`        | Storage path for render manifests.            |
| `capell-frontend-optimizer.paths.critical_css`     | Storage path for generated critical CSS.      |
| `capell-frontend-optimizer.playwright.node_binary` | Node binary used by the Playwright generator. |
| `capell-frontend-optimizer.playwright.script`      | Critical CSS script path.                     |
| `capell-frontend-optimizer.playwright.timeout`     | Generator timeout in seconds.                 |
| `capell-frontend-optimizer.playwright.viewports`   | Viewports used for critical CSS extraction.   |

`CAPELL_FRONTEND_OPTIMIZER_NODE` overrides the Node binary in local or deployed environments.

Critical CSS runtime settings are stored under the `frontend_optimizer` settings group. They include `enable_critical_css`, `automatic_generation`, `profile_scope`, `viewports`, `fold_multiplier`, `extra_fold_pixels`, `playwright_wait_strategy`, `playwright_timeout`, and `max_inline_css_bytes`.

Successful critical CSS generation updates the render profile and asks the frontend cache invalidation registry to clear affected cached output. Without exact profile-to-URL coverage, the current dependency flushes the frontend cache tag so cached first renders can be refreshed after CSS generation.

## Verification

```bash
vendor/bin/pest packages/frontend-optimizer/tests --configuration=phpunit.xml
```
