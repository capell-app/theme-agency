# Package Documentation Gaps

These notes are intentionally internal. Public READMEs should describe proven behaviour and link to package docs instead of exposing drafting notes.

## Current Gap Definition

A package counts as missing substantive docs when it has a `composer.json` but its `packages/<package>/docs/` directory contains only generated support files such as:

- `credits-and-acknowledgements.md`
- `screenshots.json`

The package README still counts as package documentation, but it is not enough for this tracker. Each package should also have at least `docs/overview.md`; schema-owning or workflow-heavy packages should add focused docs next to it.

## Packages Missing Substantive Docs

No first-party package is currently missing a substantive public docs file. Every package with a `composer.json` now has at least a README plus either `docs/overview.md` or a more specific package guide.

## Recently Closed Gaps

| Package             | Product group     | Contexts        | Added docs    | Notes                                                                |
| ------------------- | ----------------- | --------------- | ------------- | -------------------------------------------------------------------- |
| `dashboard-reports` | Capell Operations | admin           | `overview.md` | Covers dashboard widgets, data providers, and page-derived reports.  |
| `ga4-reports`       | Capell Growth     | admin, console  | `overview.md` | Covers GA4 settings, sync command, local snapshots, and data client. |
| `password-policy`   | Capell Operations | admin, console  | `overview.md` | Covers settings, Actions, password history, and enforcement flow.    |
| `demo-kit`          | Capell Foundation | admin, frontend | `overview.md` | Covers demo commands, repeatable plans, seeds, and package dispatch. |

## Implementation Plan

Use [Package Documentation Rebuild Plan](../superpowers/plans/2026-05-23-package-documentation-rebuild.md) to add these docs. The plan keeps the first pass focused on package-owned docs and README links, then verifies that every package has at least one substantive docs file.

## Historical Notes To Re-check Later

These older notes may still matter, but they are separate from the current “no substantive docs” audit:

- Some screenshot manifests mention admin screens where no Filament resource/page class was found.
- Some schema-owning packages need ERD excerpts once the shared ERD source is refreshed.
- Theme packages may need a second docs pass for screenshot manifest accuracy after the current docs coverage gap is closed.
