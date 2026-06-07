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

Install `capell-app/record-switcher`. The package registers itself through Capell Admin's edit-record heading bridge, so supported Filament edit pages expose record switching without importing package traits in the consuming resource.

## Screenshot Instructions

The committed gallery uses light and dark PNG captures from the Capell screenshot runner. Regenerate the edit-page heading capture with suggestions open when the admin heading bridge or switcher styling changes.

## Safety Caveats

The trait uses the resource query and record title APIs provided by Filament resources. Package resources should keep search columns narrow and eager-load any relations used in option labels.
