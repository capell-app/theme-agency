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
- Archive and restore actions for moving controlled documents through the admin lifecycle without bypassing package Actions.
- Publication and acceptance relation managers on each document.
- Actions for registering documents, publishing versioned content, resolving the latest publication, and recording acceptances.
- Publishing Studio revision listener that creates document publications when a matching registered document is published.
- Protected table registration for document and acceptance audit tables.

## Admin Surfaces

| Surface                       | Purpose                                                                                 |
| ----------------------------- | --------------------------------------------------------------------------------------- |
| `DocumentResource` index      | Lists controlled documents with key, title, status, publication count, update time, and archive/restore row actions. |
| `CreateDocument`              | Registers a controlled document through `RegisterDocumentAction`, including key slugging and initial status.          |
| `EditDocument`                | Edits title, status, and metadata for a controlled document.                                                   |
| `PublicationsRelationManager` | Shows version labels, content hashes, publishing revision IDs, and publish times.       |
| `AcceptancesRelationManager`  | Shows accepted versions, hashes, contexts, acceptors, and acceptance times.             |

Archived documents can be restored from the index. Restore returns documents with publications to `active`, and documents without publications to `draft`; publishing a new version still goes through `PublishDocumentAction` or the Publishing Studio auto-publish listener.

## Frontend Surfaces

This package does not register public frontend routes or Blade views in the current implementation, and the package manifest intentionally declares only `admin` and `console` surfaces. Public projects should call package actions from their own consent, legal, or account flows when recording acceptances.

The Customer Portal integration is an authenticated self-service feed for a portal account's own acceptance history. It is not anonymous frontend output and should not be treated as a package-owned public rendering surface.

## Screenshot Coverage

The screenshot contract is stored in [screenshots.json](screenshots.json). The first isolated audit pass expects screenshots for:

- controlled documents index;
- controlled document edit form;
- publications relation manager;
- acceptances relation manager.

Those admin captures are still pending. The marketplace manifest only lists committed marketplace assets from `docs/assets/marketplace/`; it must not point at future runner output under `docs/screenshots/` until those files are captured, reviewed, and committed as marketplace assets.

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
