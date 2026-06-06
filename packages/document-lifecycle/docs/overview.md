---
title: 'Document Lifecycle Overview'
description: 'How the Capell Document Lifecycle package tracks controlled documents, publication versions, and acceptances.'
---

# Document Lifecycle Overview

Document Lifecycle tracks controlled documents across publication and acceptance workflows. It is built for legal, policy, terms, and operational documents where the system needs to prove which version was published and accepted.

## Hard Dependencies

- `capell-app/admin`
- `capell-app/core`
- `capell-app/publishing-studio`

## What It Adds

- `DocumentResource` in the admin Websites navigation group.
- Register/edit access to controlled document records.
- Publish, archive, restore, and manual admin acceptance actions for moving controlled documents through the admin lifecycle without bypassing package Actions.
- Publication and acceptance relation managers on each document.
- CSV export for acceptance evidence, with optional per-publication/version filtering.
- Re-acceptance detection and an outstanding acceptances CSV report when a newer publication supersedes prior acceptances.
- Actions for registering documents, publishing versioned content, resolving the latest publication, and recording acceptances.
- Publishing Studio revision listener that creates document publications when a matching registered document is published.
- Protected table registration for document and acceptance audit tables.

## Admin Surfaces

| Surface                       | Purpose                                                                                                                                                       |
| ----------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `DocumentResource` index      | Lists controlled documents with key, title, status, publication count, update time, and publish/version, record-acceptance, archive, and restore row actions. |
| `CreateDocument`              | Registers a controlled document through `RegisterDocumentAction`, including key slugging and initial status.                                                  |
| `EditDocument`                | Edits title, status, and metadata for a controlled document.                                                                                                  |
| `PublicationsRelationManager` | Shows version labels, content hashes, publishing revision IDs, and publish times.                                                                             |
| `AcceptancesRelationManager`  | Shows accepted versions, hashes, contexts, acceptors, and acceptance times, with CSV export for the full ledger, one publication, or outstanding stale acceptances. |

Archived documents can be restored from the index. Restore returns documents with publications to `active`, and documents without publications to `draft`. Manual admin publishes call `PublishDocumentAction` with pasted content, an optional version label, the authenticated admin as publishing actor, and an optional admin note. Manual admin acceptances call `RecordDocumentAcceptanceAction` for the authenticated admin against the latest publication; documents without publications do not show the acceptance action.

## Frontend Surfaces

This package does not register public frontend routes or Blade views in the current implementation, and the package manifest intentionally declares only `admin` and `console` surfaces. Public projects should call package actions from their own consent, legal, or account flows when recording acceptances.

The Customer Portal integration is an authenticated self-service feed for a portal account's own acceptance history. It is not anonymous frontend output and should not be treated as a package-owned public rendering surface.

## Screenshot Coverage

The screenshot contract is stored in [screenshots.json](screenshots.json). The first isolated audit pass expects screenshots for:

- controlled documents index;
- controlled document edit form;
- publications relation manager;
- acceptances relation manager.

Those admin captures are committed under `docs/screenshots/` and promoted into the marketplace manifest alongside the extension card.

## Install And Verify

Install in a Capell app with Publishing Studio:

```bash
composer require capell-app/document-lifecycle
```

Then verify package behaviour from this repository:

```bash
vendor/bin/pest packages/document-lifecycle/tests --configuration=phpunit.xml
```

## Known Audit Notes

Document Lifecycle is admin/database-only in this pass. It has no package-owned frontend output, so public screenshots should only be added when a host package wires these actions into a visible consent or legal-document flow.
