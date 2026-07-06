<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CallOut\Support\Interceptors\Themes;

use Capell\Core\Contracts\ModelInterceptors\ThemeInterceptorInterface;
use Capell\Core\Models\Theme;

/**
 * Sets Call Out's default header/footer chrome on its own `Theme` row.
 *
 * Registered scoped to `CallOutThemeServiceProvider::THEME_KEY`, mirroring
 * `Capell\ThemeStudio\NightShift\Support\Interceptors\Themes\NightShiftThemeInterceptor`.
 * Call Out has no bespoke header/footer views of its own, so `header_file`/
 * `footer_file` point at Foundation's own default chrome components —
 * `x-capell::layout.index`'s per-theme override seam only falls through to
 * those defaults when a Theme's `header`/`footer` layout meta is explicitly
 * null, which a freshly-seeded layout-native Theme does not guarantee
 * without this interceptor.
 *
 * `footer_file` is deliberately `'capell::footer'`, not `'capell::footer.index'`
 * -- it must match `ThemeChromeRegistry::registerFooter()`'s canonical
 * identifier for Foundation's default footer (see
 * `FoundationThemeServiceProvider::registerThemeChromeComponents()`) exactly,
 * because `x-capell::layout.index`'s footer branch only takes its fast,
 * literal `<x-capell::footer.index />` tag path when `footer_file ===
 * 'capell::footer'`; any other string -- including the seemingly-equivalent
 * `'capell::footer.index'` -- falls through to `<x-dynamic-component>`,
 * which cannot resolve `'capell::footer.index'` (a `Blade::component()`
 * class alias) the way it resolves `'capell::header.index'` (an
 * `anonymousComponentPath()` view). `header_file` has no equivalent fast
 * path and does resolve correctly through `<x-dynamic-component>`, so it is
 * left as `'capell::header.index'`.
 */
final class CallOutThemeInterceptor implements ThemeInterceptorInterface
{
    public function beforeCreate(array $data): array
    {
        if (! isset($data['meta']) || ! is_array($data['meta'])) {
            $data['meta'] = [];
        }

        $data['meta'] = array_merge([
            'header_file' => 'capell::header.index',
            'footer_file' => 'capell::footer',
        ], $data['meta']);

        return $data;
    }

    public function afterCreated(Theme $theme, array $data): void {}
}
