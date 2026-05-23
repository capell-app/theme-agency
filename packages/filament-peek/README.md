# Capell Filament Peek

Adds a Page edit header action that opens `pboivin/filament-peek` with a private, signed preview URL.

Preview data is stored in short-lived cache snapshots. The package overlays the current unsaved Page form state, the active publishing workspace context when present, and the latest Layout Builder editor state captured for the current admin user. It does not save Pages, Layouts, translations, URLs, workspaces, or block assets.

Run focused tests with:

```bash
vendor/bin/pest packages/filament-peek/tests --configuration=phpunit.xml
```
