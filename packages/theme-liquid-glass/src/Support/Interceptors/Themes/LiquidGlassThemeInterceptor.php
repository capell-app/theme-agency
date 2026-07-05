<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LiquidGlass\Support\Interceptors\Themes;

use Capell\Core\Contracts\ModelInterceptors\ThemeInterceptorInterface;
use Capell\Core\Models\Theme;

/**
 * Sets Liquid Glass's default header/footer chrome on its own `Theme` row.
 *
 * Registered scoped to `LiquidGlassThemeServiceProvider::THEME_KEY` (see
 * `registerModelInterceptor(Theme::class, self::class, self::THEME_KEY)`),
 * so this only ever runs for a Theme whose `key` is `liquid-glass` — unlike
 * `Capell\FoundationTheme\Support\Interceptors\Themes\FoundationThemeInterceptor`,
 * which is registered unscoped (`$key = null`) and therefore applies to every
 * theme's row. Both run together for a Liquid Glass Theme (an unscoped and a
 * scoped interceptor both match the same key), merged by
 * `HasModelInterceptors::mergeModelInterceptorData()`.
 *
 * `header_file` / `footer_file` are `x-capell::layout.index`'s own
 * documented per-theme chrome override seam (its `<x-dynamic-component>`
 * fallback) — see `LiquidGlassThemeServiceProvider::registerLayoutAreas()`
 * for why this is used instead of a Blade view-chain override of the
 * `capell::header.index` / `capell::footer.index` class-aliased components.
 */
final class LiquidGlassThemeInterceptor implements ThemeInterceptorInterface
{
    public function beforeCreate(array $data): array
    {
        if (! isset($data['meta']) || ! is_array($data['meta'])) {
            $data['meta'] = [];
        }

        $data['meta'] = array_merge([
            'header_file' => 'capell-theme-liquid-glass::header.index',
            'footer_file' => 'capell-theme-liquid-glass::footer',
        ], $data['meta']);

        return $data;
    }

    public function afterCreated(Theme $theme, array $data): void {}
}
