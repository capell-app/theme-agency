<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ReadingRoom\Support\Interceptors\Themes;

use Capell\Core\Contracts\ModelInterceptors\ThemeInterceptorInterface;
use Capell\Core\Models\Theme;

/**
 * Sets Reading Room's default header/footer chrome on its own `Theme` row.
 *
 * Registered scoped to `ReadingRoomThemeServiceProvider::THEME_KEY` (see
 * `registerModelInterceptor(Theme::class, self::class, self::THEME_KEY)`), so
 * this only ever runs for a Theme whose `key` is `reading-room` — mirrors
 * `Capell\ThemeStudio\NightShift\Support\Interceptors\Themes\NightShiftThemeInterceptor`
 * exactly.
 *
 * `header_file` / `footer_file` are `x-capell::layout.index`'s own
 * documented per-theme chrome override seam (its `<x-dynamic-component>`
 * fallback) — see `ReadingRoomThemeServiceProvider::registerLayoutAreas()`
 * for why this is used instead of a Blade view-chain override of the
 * `capell::header.index` / `capell::footer.index` class-aliased components.
 */
final class ReadingRoomThemeInterceptor implements ThemeInterceptorInterface
{
    public function beforeCreate(array $data): array
    {
        if (! isset($data['meta']) || ! is_array($data['meta'])) {
            $data['meta'] = [];
        }

        $data['meta'] = array_merge([
            'header_file' => 'capell-theme-reading-room::header.index',
            'footer_file' => 'capell-theme-reading-room::footer',
        ], $data['meta']);

        return $data;
    }

    public function afterCreated(Theme $theme, array $data): void {}
}
