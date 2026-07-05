<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\LiquidGlass\Enums;

use Capell\ThemeStudio\LiquidGlass\LiquidGlassThemeServiceProvider;

/**
 * Liquid Glass's own bespoke layout-builder widget component keys.
 *
 * These are registered against the shared, cross-package
 * `Capell\Core\Support\Renderables\RenderableRegistry` (type
 * `layout-widget`) by {@see LiquidGlassThemeServiceProvider},
 * mirroring the established pattern `Capell\Blog\Providers\BlogServiceProvider`
 * and `Capell\LayoutBuilder\Support\LayoutBuilderCoreRegistrar` already use
 * for their own widget component enums: a `Widget` row's `meta.component`
 * (or `component` column) is set to one of these case *values*, and
 * `Widget::getComponent()` resolves that key to the real Blade view through
 * `RenderableRegistry` before `<x-dynamic-component>` renders it.
 *
 * These three are Liquid Glass's own bespoke, non-foundation sections (cta,
 * showcase, presets) — see LiquidGlassThemeServiceProvider's widget mapping
 * docblock for why navigation/footer/hero/features/proof/content-listing do
 * not need bespoke component keys of their own.
 */
enum WidgetComponentEnum: string
{
    case Cta = 'capell.widget.liquid-glass.cta';
    case Showcase = 'capell.widget.liquid-glass.showcase';
    case Presets = 'capell.widget.liquid-glass.presets';
}
