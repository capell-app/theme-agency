<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\ReadingRoom\Enums;

use Capell\ThemeStudio\ReadingRoom\ReadingRoomThemeServiceProvider;

/**
 * Reading Room's own bespoke layout-builder widget component keys.
 *
 * Registered against the shared, cross-package
 * `Capell\Core\Support\Renderables\RenderableRegistry` (type
 * `layout-widget`) by {@see ReadingRoomThemeServiceProvider}, mirroring the
 * pattern `Capell\ThemeStudio\NightShift\Enums\WidgetComponentEnum`
 * established for the reference layout-native theme conversion.
 *
 * These seven are Reading Room's signature docs/knowledge-base widgets (Wave
 * 6, Part 2 §E): a collapsible doc-tree sidebar, an in-article scroll-spy
 * table of contents, a search-first hero, version/changelog surfaces, an API
 * reference parameter table, an admonition callout system, and a
 * "was this helpful" feedback footer.
 */
enum WidgetComponentEnum: string
{
    case DocTreeSidebar = 'capell.widget.reading-room.doc-tree-sidebar';
    case InArticleTocScrollSpy = 'capell.widget.reading-room.in-article-toc-scroll-spy';
    case SearchSpotlightHero = 'capell.widget.reading-room.search-spotlight-hero';
    case VersionChangelogSurfaces = 'capell.widget.reading-room.version-changelog-surfaces';
    case ApiReferenceParameterTable = 'capell.widget.reading-room.api-reference-parameter-table';
    case CalloutAdmonitionSystem = 'capell.widget.reading-room.callout-admonition-system';
    case FeedbackFooter = 'capell.widget.reading-room.feedback-footer';
}
