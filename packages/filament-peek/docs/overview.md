# Capell Filament Peek

Status: **Available, no schema impact** · Kind: **package** · Tier:
**free** · Bundle: **foundation** · Contexts: **admin, frontend** · Product
group: **Capell Foundation**

Capell Filament Peek gives editors a safe preview of unsaved Page changes. It
adds a `Changes` preview action beside the saved `Live page` action, then opens
a signed URL backed by a temporary cache snapshot.

## What This Package Adds

- A Page edit header preview action for unsaved changes.
- Short-lived preview snapshots for Page form state.
- Layout Builder preview state capture for the current admin user.
- A signed frontend preview route.
- A preview error view for invalid, expired, or unavailable snapshots.

## Why It Matters

For a non-technical editor, the benefit is simple: they can see how a page will
look before saving it. That reduces accidental publishing mistakes and keeps the
saved live page separate from temporary editing work.

For developers, the package keeps preview state out of persisted page and
publishing tables. It uses cache snapshots and signed routes instead of turning
preview data into permanent content.

## Runtime Shape

- `FilamentPeekServiceProvider` registers the package config, views, routes, and
  preview behavior.
- `CreatePagePreviewSnapshotAction` creates the short-lived snapshot payload.
- `FindPagePreviewSnapshotAction` reads cached snapshots for signed render
  requests without coupling the controller to the write action.
- `StoreLayoutBuilderPreviewStateAction` records the latest Layout Builder state
  for an admin preview.
- `RenderPagePreviewSnapshotAction` applies the snapshot to frontend rendering.
- `PagePreviewController` serves signed preview requests.

## Configuration

`config/capell-filament-peek.php` exposes:

- `enabled`
- `preview.cache_store`
- `preview.ttl_minutes`
- `preview.route_prefix`
- `preview.middleware`

The default middleware is `web` plus `signed`.

## Data And Persistence

This package owns no database tables and no settings. Snapshot payloads live in
cache for the configured TTL. The package must not persist unsaved preview state
into Pages, Layouts, translations, URLs, workspaces, or block assets.

## Verification

```bash
vendor/bin/pest packages/filament-peek/tests --configuration=phpunit.xml
```

The focused tests cover the preview route, Page preview action wiring, provider
registration, Layout Builder preview block registration, and snapshot action.
