<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MainStage\Support\Interceptors\Themes;

use Capell\Core\Contracts\ModelInterceptors\ThemeInterceptorInterface;
use Capell\Core\Models\Theme;

/**
 * Sets Main Stage's default header/footer chrome on its own `Theme` row.
 *
 * Registered scoped to `MainStageThemeServiceProvider::THEME_KEY` (see
 * `registerModelInterceptor(Theme::class, self::class, self::THEME_KEY)`),
 * so this only ever runs for a Theme whose `key` is `main-stage` — mirrors
 * `Capell\ThemeStudio\NightShift\Support\Interceptors\Themes\NightShiftThemeInterceptor`
 * exactly.
 *
 * `header_file` / `footer_file` are `x-capell::layout.index`'s own
 * documented per-theme chrome override seam (its `<x-dynamic-component>`
 * fallback).
 */
final class MainStageThemeInterceptor implements ThemeInterceptorInterface
{
    public function beforeCreate(array $data): array
    {
        if (! isset($data['meta']) || ! is_array($data['meta'])) {
            $data['meta'] = [];
        }

        $data['meta'] = array_merge([
            'header_file' => 'capell-theme-main-stage::header.index',
            'footer_file' => 'capell-theme-main-stage::footer',
        ], $data['meta']);

        return $data;
    }

    public function afterCreated(Theme $theme, array $data): void {}
}
