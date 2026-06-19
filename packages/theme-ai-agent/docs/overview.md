# Theme AI Agent

<!-- prettier-ignore-start -->

## What This Plugin Adds

Theme AI Agent is an **Available**, **No schema impact** Capell theme in the **Capell Themes** product group. It ships as `capell-app/theme-ai-agent` and extends these surfaces: frontend.

AI Agent extends the default Capell frontend with a ai agent visual direction, portable demo content, cache-safe public Blade rendering, and theme tokens tuned for ai support agents, ops automation products.

After install, admins can select the theme through the core theme management surface. Editors keep using normal Capell content workflows while the package controls public presentation.

Status details:

- Status: Available
- Tier: premium
- Bundle: themes
- Composer package: `capell-app/theme-ai-agent`
- Namespace: `Capell\ThemeStudio\AiAgent`
- Theme key: `ai-agent`

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** A premium Capell theme for ai support agents, ops automation products.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- AI Agent Homepage (frontend, required).
- AI Agent Directory (frontend, required).
- AI Agent Detail (frontend, required).
- AI Agent Contact (frontend, required).
- AI Agent Empty State (frontend, required).
- AI Agent 404 State (frontend, required).
- AI Agent Conversion CTA (frontend, required).
- AI Agent Use Cases (frontend, required).
- AI Agent Integrations (frontend, required).
- AI Agent ROI Calculator (frontend, required).

## Technical Shape

- Service providers: `Capell\ThemeStudio\AiAgent\AiAgentThemeServiceProvider`.
- Actions: `InstallAiAgentThemeDemoAction`.
- Command signatures: `capell:theme-ai-agent-demo`.
- Console command classes: `DemoCommand`.
- Manifest contributions: `admin-page: Capell\ThemeStudio\AiAgent\Manifest\ThemeManagementPageContribution`.
- Health checks: `Capell\ThemeStudio\AiAgent\Health\ThemeAiAgentHealthCheck`.
- Blade views: `packages/theme-ai-agent/resources/views/livewire/page/page.blade.php`, `packages/theme-ai-agent/resources/views/page.blade.php`, `packages/theme-ai-agent/resources/views/sections/agent-in-action.blade.php`, `packages/theme-ai-agent/resources/views/sections/content-listing.blade.php`, `packages/theme-ai-agent/resources/views/sections/cta.blade.php`, `packages/theme-ai-agent/resources/views/sections/features.blade.php`, `packages/theme-ai-agent/resources/views/sections/footer.blade.php`, `packages/theme-ai-agent/resources/views/sections/hero.blade.php`, `packages/theme-ai-agent/resources/views/sections/integrations-grid.blade.php`, `packages/theme-ai-agent/resources/views/sections/navigation.blade.php`, `packages/theme-ai-agent/resources/views/sections/outcome-metrics.blade.php`, `packages/theme-ai-agent/resources/views/sections/proof.blade.php`, `and 2 more`.
- Cache tags: `theme-ai-agent`.

## Data Model

This theme has no schema impact. It relies on core Capell site, page, locale, and theme records instead of declaring package-owned tables.

## Install Impact

- Admin navigation: contributes admin extension points through `capell.json`.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: no package migrations declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `theme-ai-agent`.
- Commands: `capell:theme-ai-agent-demo`.

## Common Pitfalls

- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/theme-ai-agent`.
2. Run the required setup: `php artisan capell:theme-ai-agent-demo`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Focused tests: `vendor/bin/pest packages/theme-ai-agent/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
