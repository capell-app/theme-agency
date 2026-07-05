<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\NightShift\Enums;

use Capell\ThemeStudio\NightShift\NightShiftThemeServiceProvider;

/**
 * Night Shift's own bespoke layout-builder widget component keys.
 *
 * These are registered against the shared, cross-package
 * `Capell\Core\Support\Renderables\RenderableRegistry` (type
 * `layout-widget`) by {@see NightShiftThemeServiceProvider}, mirroring the
 * pattern `Capell\ThemeStudio\LiquidGlass\Enums\WidgetComponentEnum`
 * established for the reference layout-native theme conversion.
 *
 * These three are Night Shift's own bespoke, non-foundation sections
 * (changelog/integrations, workflow rails, security proof) — the theme's
 * most distinctive product-UI shells. `navigation` / `footer` are wired
 * separately through `NightShiftThemeInterceptor`'s `header_file` /
 * `footer_file` Theme defaults, not through this enum. `hero` /
 * `system-hero` / `agents-automation` / `planning-roadmap` / `proof` /
 * `content-listing` / `newsletter` / `cta` are NOT given bespoke widget
 * treatment in this conversion — see
 * `NightShiftThemeServiceProvider::registerLayoutAreas()`'s "NOTE on scope"
 * for why, mirroring Liquid Glass's own documented scope decision.
 */
enum WidgetComponentEnum: string
{
    case ChangelogIntegrations = 'capell.widget.night-shift.changelog-integrations';
    case WorkflowRails = 'capell.widget.night-shift.workflow-rails';
    case SecurityProof = 'capell.widget.night-shift.security-proof';
}
