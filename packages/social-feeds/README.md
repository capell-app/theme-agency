# Social Feeds

Social Feeds renders cached social or RSS content through Capell blocks and widgets while keeping public HTML free of credentials, admin URLs, editor markers, and connection internals.

## At A Glance

| Field            | Value                                                                        |
| ---------------- | ---------------------------------------------------------------------------- |
| Composer package | `capell-app/social-feeds`                                                    |
| Namespace        | `Capell\SocialFeeds`                                                         |
| Product group    | Capell Growth                                                                |
| Surfaces         | Admin, frontend, console where host sync jobs call Actions                   |
| Provider         | `Capell\SocialFeeds\Providers\SocialFeedsServiceProvider`                    |
| Key contracts    | `SocialFeedProvider`, `SocialFeedProviderProvider`, `SocialFeedHostResolver` |
| Models           | `SocialFeedConnection`, `SocialFeedItem`, `SocialFeedOAuthState`             |
| Public renderer  | `SocialFeedBlockRenderer`                                                    |

## Why It Helps Your Capell Workflow

Owners can reuse social proof and news updates across pages without embedding third-party widgets directly into public output. Editors configure connection-backed feed blocks with layout options for list, slideshow, carousel, and paginated displays.

Developers get a provider registry, DTO-based render data, and a Block Library renderer. Provider integrations can be added without changing the public Blade surface.

## What It Adds

- Extendable provider registry with provider-provider tagging.
- Built-in provider keys for RSS/Atom, TikTok, YouTube, Bluesky, Instagram, Facebook, LinkedIn, and X.
- Native RSS/Atom sync plus `feed_url` backed sync for configured providers while native API bridges mature.
- Cached `social_feed_items` storage consumed by public rendering.
- `social-feed` Block Library block with provider, connection, limit, pagination, columns, caption, author, date, media, autoplay, transition, and aspect-ratio controls.
- Public DTOs from `FetchSocialFeedRenderDataAction`.

## Boundaries

Social Feeds owns connection records, provider sync, item storage, and public feed render data. It does not own account OAuth product strategy for every provider, nor should it render credentials, OAuth state, provider errors, package names, admin URLs, or raw API payloads into public HTML.

Public views consume hydrated `SocialFeedRenderData`; do not query feed models from Blade.

## Runtime Surface

- Provider: `src/Providers/SocialFeedsServiceProvider.php`
- Contracts: `src/Contracts/`
- Provider drivers: `src/Providers/Drivers/`
- Registry/support: `src/Support/`
- Block renderer/definition: `src/Blocks/`
- Actions: `src/Actions/`
- Data objects: `src/Data/`
- Tests: `packages/social-feeds/tests`

## Docs

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshots contract](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/social-feeds/tests --configuration=phpunit.xml
```

## Troubleshooting

| Symptom                                | Likely cause                                    | Check                                                                 | Fix                                                                                          |
| -------------------------------------- | ----------------------------------------------- | --------------------------------------------------------------------- | -------------------------------------------------------------------------------------------- |
| Feed renders empty                     | Connection has no cached items or sync failed   | Check `social_feed_connections.status` and recent `social_feed_items` | Run `SyncSocialFeedConnectionAction` from the host workflow and inspect the connection error |
| Provider sync reports missing endpoint | Provider needs a configured `feed_url`          | Review the connection provider and endpoint fields                    | Add a valid RSS/Atom/feed URL or implement a native provider driver                          |
| Public output shows stale posts        | Cached render data was not refreshed after sync | Compare item timestamps and frontend cache tags                       | Re-sync the connection and clear the affected frontend cache tag                             |
