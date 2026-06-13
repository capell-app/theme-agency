# Social Feeds

<!-- prettier-ignore-start -->

## What This Plugin Adds

Social Feeds is an **Available**, **Schema-owning** Capell package in the **Capell Growth** product group. It ships as `capell-app/social-feeds` and extends these surfaces: admin, frontend.

Premium social feed widgets for Capell with cached public rendering, provider extensibility, and configurable list, slideshow, carousel, and paginated layouts.

After install, the package contributes admin-facing extension points and may affect public output or routes. Docs gap: no concrete Filament resource or page was detected.

Status details:

- Status: Available
- Tier: premium
- Bundle: growth
- Composer package: `capell-app/social-feeds`
- Namespace: `Capell\SocialFeeds`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Place branded, cached social feeds anywhere in Capell with RSS, TikTok, YouTube, Bluesky, and custom provider support.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Social feed carousel widget (frontend, required).
- RSS feed sync and cached items (admin, required).
- Provider registry extension surface (shared, required).

## Technical Shape

- Service providers: `Capell\SocialFeeds\Providers\SocialFeedsServiceProvider`, `AbstractConfiguredProvider`, `BlueskyFeedProvider`, `FacebookFeedProvider`, `InstagramFeedProvider`, `LinkedInFeedProvider`, `RssFeedProvider`, `TikTokFeedProvider`, `XFeedProvider`, `YouTubeFeedProvider`.
- Config files: `packages/social-feeds/config/capell-social-feeds.php`.
- Migrations: `packages/social-feeds/database/migrations/2026_06_04_000001_create_social_feed_connections_table.php`, `packages/social-feeds/database/migrations/2026_06_04_000002_create_social_feed_items_table.php`, `packages/social-feeds/database/migrations/2026_06_04_000003_create_social_feed_oauth_states_table.php`.
- Models: `SocialFeedConnection`, `SocialFeedItem`, `SocialFeedOAuthState`.
- Actions: `FetchSocialFeedRenderDataAction`, `RedactSocialFeedSyncErrorAction`, `SyncSocialFeedConnectionAction`, `UpsertSocialFeedItemsAction`.
- Data objects: `ResolvedFeedEndpointData`, `SocialFeedPostData`, `SocialFeedRenderData`, `SocialFeedRenderItemData`, `SocialFeedWidgetConfigData`.
- Manifest contributions: `frontend-component: Capell\SocialFeeds\Manifest\SocialFeedWidgetContribution`, `model: Capell\SocialFeeds\Manifest\SocialFeedConnectionModelContribution`, `model: Capell\SocialFeeds\Manifest\SocialFeedItemModelContribution`.
- Health checks: `Capell\SocialFeeds\Health\SocialFeedsHealthCheck`.
- Blade views: `packages/social-feeds/resources/views/blocks/social-feed.blade.php`.
- Cache tags: `social-feeds`.

## Data Model

- Required tables: `social_feed_connections`, `social_feed_items`, `social_feed_oauth_states`.
- Models: `SocialFeedConnection`, `SocialFeedItem`, `SocialFeedOAuthState`.
- Migration files: `2026_06_04_000001_create_social_feed_connections_table.php`, `2026_06_04_000002_create_social_feed_items_table.php`, `2026_06_04_000003_create_social_feed_oauth_states_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: admin-facing extension points are declared, but no concrete Filament class was detected.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `social-feeds`.
- Commands: none declared.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/social-feeds`.
2. Run the required setup: `php artisan migrate`.
3. Verify the package provider is registered and the related frontend, command, or extension point is active.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Block Library](../../block-library/README.md).
- Focused tests: `vendor/bin/pest packages/social-feeds/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
