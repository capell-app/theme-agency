# Social Feeds Overview

Status: **Available, schema-owning** · Kind: **package** · Tier: **premium** · Bundle: **growth** · Contexts: **admin, frontend**

Social Feeds gives Capell sites a premium social-feed widget that renders from cached feed items instead of calling third-party APIs during public page requests. Editors can place the `social-feed` block and choose list, slideshow, carousel, or paginated layouts with controls for limits, page size, columns, media, captions, authors, dates, autoplay, transition speed, aspect ratio, and empty-state behavior.

## Extension Surface

- `SocialFeedProvider` is the provider contract for RSS, TikTok, YouTube, Bluesky, Instagram, Facebook, LinkedIn, X, and custom providers.
- RSS / Atom is the native v1 provider. Other built-in social provider keys sync from configured `feed_url` credentials until native API bridges are completed.
- `SocialFeedProviderProvider::TAG` lets another package register providers through `SocialFeedProviderRegistry`.
- `SocialFeedWidgetContribution` declares the public widget contribution in `capell.json`.
- `SocialFeedConnectionModelContribution` and `SocialFeedItemModelContribution` document the schema-owned records exposed by the package.

## Marketplace

The marketplace gallery keeps the extension card artwork in `docs/assets/marketplace` and uses route-backed PNG captures from the deployment screenshot contract in [screenshots.json](screenshots.json). The screenshot runner installs the package, seeds an RSS connection with cached posts, renders the public block, and captures the required PNGs into `packages/social-feeds/docs/screenshots`.

![Social Feeds extension card](assets/marketplace/extension-card.svg)

![Social Feeds carousel widget](screenshots/social-feed-carousel-widget.png)

![Social Feeds RSS sync](screenshots/social-feed-rss-sync.png)

![Social Feeds provider registry](screenshots/social-feed-provider-registry.png)

## Safety

Public rendering consumes `SocialFeedRenderData` only. The Blade view must not query the database, lazy-load relationships, output credentials, expose connection IDs, leak package internals, or include authoring/admin markers.
