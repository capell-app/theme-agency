# Record Switcher

Status: Marketplace-ready first-party extension.

Tier: Free.

Bundle: Capell Foundation.

Record Switcher gives editors a fast way to move between editable records from the Filament page heading. Pages, layouts, sections, navigation records, and Layout Builder widgets can expose searchable suggestions without returning to a table view.

## Surfaces

- Admin: reusable Filament edit-page trait, Blade heading component, CSS, and Alpine/Choices asset.

## Buyer Value

- Speeds up repeated editing workflows across related admin records.
- Keeps the switcher visually aligned with Capell Admin.
- Supports keyboard suggestion acceptance with Tab.
- Removes the third-party record switcher package from Capell installations.

## Setup Notes

Install `capell-app/record-switcher` and use `Capell\RecordSwitcher\Concerns\HasRecordSwitcher` on Filament edit pages that should expose record switching.

## Screenshot Instructions

Capture the edit page heading with suggestions open after typing a short query. The committed SVG assets are interim Marketplace previews until a seeded package harness captures real admin screenshots.

## Safety Caveats

The trait uses the resource query and record title APIs provided by Filament resources. Package resources should keep search columns narrow and eager-load any relations used in option labels.
