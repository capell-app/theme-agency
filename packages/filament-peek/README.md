# Capell Filament Peek

Capell Filament Peek adds private previews for unsaved Capell page changes so
editors can inspect draft form and Layout Builder state before saving.

## At A Glance

- Package: `capell-app/filament-peek`
- Namespace: `Capell\FilamentPeek\`
- Product group: Capell Foundation
- Tier: free
- Surfaces: admin, frontend
- Service provider:
  `Capell\FilamentPeek\Providers\FilamentPeekServiceProvider`
- Routes: signed preview route under `capell-filament-peek`
- Required third-party package: `pboivin/filament-peek`
- Database impact: none

## Why It Helps Your Capell Workflow

- Editors can preview unsaved page edits without publishing or saving a work in
  progress.
- Content teams can compare the current live page with a temporary "Changes"
  preview from the Page edit header.
- Developers get a focused preview package that stores short-lived snapshots
  instead of mixing preview state into Pages, Layouts, translations, or
  publishing workflow records.

## Best Used With

- [Foundation Theme](../foundation-theme/README.md)
- [Frontend Authoring](../frontend-authoring/README.md)
- [Publishing Studio](../publishing-studio/README.md)

## What It Adds

- A `Changes` action in the Page edit header `Preview` group.
- A private signed preview URL for the current unsaved admin state.
- Short-lived cache snapshots containing the current Page form state and latest
  Layout Builder editor state for the current admin user.
- Page preview rendering through `PagePreviewController` and
  `RenderPagePreviewSnapshotAction`.

## What It Does Not Save

The preview flow does not persist Pages, Layouts, translations, URLs,
workspaces, or block assets. Publishing Studio drafts remain the persisted
workflow record; Peek snapshots are private, signed, short-lived, and
non-persistent.

## Runtime Surface

| Area                 | Path                                                                                 |
| -------------------- | ------------------------------------------------------------------------------------ |
| Config               | `config/capell-filament-peek.php`                                                    |
| Routes               | `routes/web.php`                                                                     |
| Preview create       | `src/Actions/CreatePagePreviewSnapshotAction.php`                                    |
| Preview lookup       | `src/Actions/FindPagePreviewSnapshotAction.php`                                      |
| Render action        | `src/Actions/RenderPagePreviewSnapshotAction.php`                                    |
| Layout Builder state | `src/Actions/StoreLayoutBuilderPreviewStateAction.php`                               |
| Preview data         | `src/Data/PagePreviewSnapshotData.php`, `src/Data/LayoutBuilderPreviewStateData.php` |
| Error view           | `resources/views/preview-error.blade.php`                                            |
| Provider             | `src/Providers/FilamentPeekServiceProvider.php`                                      |

## Install Impact

- Adds no migrations, settings, models, or admin navigation.
- Registers signed frontend preview routes.
- Uses cache for preview snapshots. Configure
  `CAPELL_FILAMENT_PEEK_CACHE_STORE` and `CAPELL_FILAMENT_PEEK_TTL_MINUTES` when
  the host app needs a specific store or TTL.

## Docs

- [Docs index](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot manifest](docs/screenshots.json)
- [Package documentation standard](../../docs/package-documentation-standard.md)

## Testing

Run focused tests with:

```bash
vendor/bin/pest packages/filament-peek/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Keep preview URLs signed and short-lived.
- Keep preview snapshots out of persistent publishing state.
- Keep snapshot creation and snapshot lookup separate so render paths do not
  depend on write actions.
- Keep the `Live page` action separate from the unsaved `Changes` preview so
  editors understand what is already public and what is temporary.
