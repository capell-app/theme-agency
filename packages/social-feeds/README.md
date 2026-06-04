# Social Feeds

Social Feeds is Capell's premium social-feed widget package. It renders cached social content through Block Library with configurable list, slideshow, carousel, and paginated layouts while keeping public HTML free of credentials, admin URLs, editor markers, and connection internals.

## What Ships

- Extendable `SocialFeedProvider` bridge with tagged provider registration.
- Built-in provider keys for RSS / Atom, TikTok, YouTube, Bluesky, Instagram, Facebook, LinkedIn, and X.
- Working RSS / Atom sync provider for the first executable integration path.
- Cached `social_feed_items` table used by public rendering.
- `social-feed` Block Library block with provider, connection, limit, page size, columns, caption, author, date, media, autoplay, transition, aspect-ratio, and empty-state controls.
- Public Blade rendering that consumes hydrated DTOs only.

## Extension

Custom packages can add providers by tagging a `SocialFeedProviderProvider` implementation:

```php
$this->app->tag([MySocialFeedProviderProvider::class], \Capell\SocialFeeds\Contracts\SocialFeedProviderProvider::TAG);
```

The provider-provider receives `SocialFeedProviderRegistry` and may register one or more `SocialFeedProvider` implementations.

## Verification

```bash
vendor/bin/pest packages/social-feeds/tests --configuration=phpunit.xml
composer validate packages/social-feeds/composer.json --no-check-publish
```
