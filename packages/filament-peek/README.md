# Capell Filament Peek

Adds a `Changes` action to the Page edit header `Preview` group. The action opens `pboivin/filament-peek` with a private, signed preview URL for the current unsaved admin state.

Preview data is stored in short-lived cache snapshots. The package overlays the current unsaved Page form state, the active publishing workspace context when present, and the latest Layout Builder editor state captured for the current admin user. It does not save Pages, Layouts, translations, URLs, workspaces, or block assets.

The Page edit `Preview` group keeps live and unsaved previews separate:

- `Changes` renders a temporary Peek snapshot of the current unsaved form and Layout Builder state.
- `Live page` opens the saved public page URL.
- Publishing Studio drafts remain persisted workflow records; Peek snapshots are private, signed, short-lived, and non-persistent.

Run focused tests with:

```bash
vendor/bin/pest packages/filament-peek/tests --configuration=phpunit.xml
```
