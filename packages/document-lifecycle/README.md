# Document Lifecycle

Controlled document registration, publication history, and acceptance tracking for Capell.

## At A Glance

- Package: `capell-app/document-lifecycle`
- Namespace: `Capell\DocumentLifecycle\`
- Surfaces: Filament admin, database
- Service providers: `packages/document-lifecycle/src/Providers/DocumentLifecycleServiceProvider.php`
- Capell dependencies: `capell-app/admin`, `capell-app/core`, `capell-app/publishing-studio`

## Why It Helps Your Capell Workflow

- Adds controlled document registry, publication metadata, hashes, and acceptance evidence for compliance-heavy Capell sites.
- Helps owners prove which document version was published or accepted without inventing a custom audit layer.
- Fits publishing workflows where documents need clearer state and evidence than ordinary page content.

## Best Used With

- [Publishing Studio](../publishing-studio/README.md)
- [Diagnostics](../diagnostics/README.md)
- [Password Policy](../password-policy/README.md)

## What It Adds

- A Controlled documents admin resource.
- Document registration and publication actions.
- Publication records linked to Publishing Studio revisions.
- Acceptance records stored in or extending the `legal_acceptances` table.
- Signed JSON certificate downloads for individual acceptance records.
- Protected table registration for document and acceptance audit data.

Use this package when a site needs evidence that a controlled document was published and accepted. It is not a general file manager; media and downloadable assets stay in the media packages.

## Admin Surface

- Resource: `DocumentResource`.
- Pages: `ListDocuments`, `EditDocument`.
- Relation managers: `PublicationsRelationManager`, `AcceptancesRelationManager`.

## Data And Persistence

- Models: `Document`, `DocumentPublication`, `DocumentAcceptance`.
- Migrations: `document_lifecycle_documents`, `document_lifecycle_publications`, and `legal_acceptances` extension.
- Actions: register, publish, resolve latest publication, compute content hash, record acceptance.

## Install And Setup

Install with:

```bash
composer require capell-app/document-lifecycle
```

Run migrations through the host application package install flow.

## Docs

- [docs index](docs/README.md)
- [overview.md](docs/overview.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/document-lifecycle/tests --configuration=phpunit.xml
```
