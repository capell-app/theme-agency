<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\MainStage\Enums;

use Capell\ThemeStudio\MainStage\MainStageThemeServiceProvider;

/**
 * Main Stage's own bespoke layout-builder widget component keys.
 *
 * These are registered against the shared, cross-package
 * `Capell\Core\Support\Renderables\RenderableRegistry` (type
 * `layout-widget`) by {@see MainStageThemeServiceProvider}, mirroring the
 * pattern `Capell\ThemeStudio\NightShift\Enums\WidgetComponentEnum`
 * established for the reference layout-native theme conversion.
 *
 * All eight cases are Main Stage's own bespoke, events-conference sections
 * (Part 2 §E "theme-main-stage (events-conference)") — the theme's most
 * distinctive, FOMO-vocabulary product surfaces. `navigation` / `footer` are
 * wired separately through `MainStageThemeInterceptor`'s `header_file` /
 * `footer_file` Theme defaults, not through this enum.
 */
enum WidgetComponentEnum: string
{
    case AgendaGrid = 'capell.widget.main-stage.agenda-grid-days-tracks-rooms';
    case SpeakerWall = 'capell.widget.main-stage.speaker-wall-hover-bios';
    case TicketTierComparison = 'capell.widget.main-stage.ticket-tier-comparison';
    case CountdownBand = 'capell.widget.main-stage.countdown-band';
    case VenueTravelPanels = 'capell.widget.main-stage.venue-travel-panels';
    case SponsorTierWalls = 'capell.widget.main-stage.sponsor-tier-walls';
    case LiveNowReplayState = 'capell.widget.main-stage.live-now-replay-state';
    case PastEditionsArchive = 'capell.widget.main-stage.past-editions-archive';
}
