# Record Switcher

Record Switcher adds searchable, keyboard-friendly record navigation to supported Capell Filament edit pages.

## At A Glance

- Package: `capell-app/record-switcher`
- Namespace: `Capell\RecordSwitcher\`
- Surfaces: Filament admin
- Service provider: `Capell\RecordSwitcher\Providers\RecordSwitcherServiceProvider`
- Capell dependencies: `capell-app/admin`, `capell-app/core`
- Conflicts with: `howdu/filament-record-switcher`

## Why It Helps Your Capell Workflow

For editors, Record Switcher removes the table-view round trip when moving between related records such as pages, layouts, sections, navigation items, and widget records.

For developers, it keeps the heading switcher in a first-party package with Capell-owned assets, Livewire behavior, and manifest coverage instead of relying on a third-party Filament extension.

## What It Adds

- A reusable Filament edit-page heading extender.
- A Livewire `RecordSwitcher` component.
- First-party admin JavaScript/CSS asset contribution.
- Searchable suggestions and Tab-to-accept keyboard flow.
- Light and dark marketplace screenshots for the admin heading workflow.

## Boundaries

- Record Switcher owns the edit-heading switcher UI and assets.
- Package resources still own their record queries, labels, authorization, and relation eager loading.
- Search columns should stay narrow; option labels should not lazy-load expensive relationships.
- The package has no migrations, settings, routes, frontend output, or console commands.

## Runtime Surface

- Provider: `src/Providers/RecordSwitcherServiceProvider.php`
- Heading extender: `src/Filament/RecordSwitcherHeadingExtender.php`
- Livewire component: `src/Livewire/RecordSwitcher.php`
- Asset contribution: `src/Manifest/RecordSwitcherAssetsContribution.php`
- Tests: `packages/record-switcher/tests`

## Docs

- [docs index](docs/README.md)
- [overview.md](docs/overview.md)
- [screenshots.json](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/record-switcher/tests --configuration=phpunit.xml
```
