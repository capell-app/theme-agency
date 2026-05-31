# Capell Filament Peek Docs

Capell Filament Peek gives editors a temporary preview of unsaved Page edits
without saving or publishing the page.

## Read Next

- [Overview](overview.md)
- [Package README](../README.md)
- [Package documentation standard](../../../docs/package-documentation-standard.md)

## Developer Starting Points

| Need                          | Start Here                                                                                          |
| ----------------------------- | --------------------------------------------------------------------------------------------------- |
| Register the preview action   | `src/Providers/FilamentPeekServiceProvider.php`                                                     |
| Create preview snapshots      | `src/Actions/CreatePagePreviewSnapshotAction.php`                                                   |
| Store Layout Builder state    | `src/Actions/StoreLayoutBuilderPreviewStateAction.php`                                              |
| Render signed preview URLs    | `src/Actions/RenderPagePreviewSnapshotAction.php`, `src/Http/Controllers/PagePreviewController.php` |
| Tune cache and route settings | `config/capell-filament-peek.php`                                                                   |
| Verify behavior               | `tests/Feature/PagePreviewRouteTest.php`, `tests/Feature/PeekPagePreviewActionTest.php`             |

## Safety Checklist

- Preview routes must stay signed.
- Snapshot cache entries must stay short-lived.
- Unsaved preview state must not be written back to Pages, Layouts,
  translations, URLs, workspaces, or block assets.
- The saved `Live page` action must remain separate from the unsaved `Changes`
  preview.
