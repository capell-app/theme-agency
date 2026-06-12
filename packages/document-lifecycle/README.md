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
- Stored publication content snapshots with JSON diff downloads between versions.
- Acceptance records stored in or extending the `legal_acceptances` table.
- Signed JSON certificate downloads for individual acceptance records.
- Review-due and expiry dates for controlled documents, with a daily archive-expired command.
- Protected table registration for document and acceptance audit data.

Use this package when a site needs evidence that a controlled document was published and accepted. It is not a general file manager; media and downloadable assets stay in the media packages.

## Admin Surface

- Resource: `DocumentResource`.
- Pages: `ListDocuments`, `EditDocument`.
- Relation managers: `PublicationsRelationManager`, `AcceptancesRelationManager`.

## Data And Persistence

- Models: `Document`, `DocumentPublication`, `DocumentAcceptance`.
- Migrations: `document_lifecycle_documents`, `document_lifecycle_publications`, and `legal_acceptances` extension.
- Actions: register, publish, resolve latest publication, compute content hash, record acceptance, archive expired documents.
- Command: `capell:document-lifecycle:archive-expired`.

## Boundaries

Document Lifecycle owns controlled document registration, publication evidence, acceptance evidence, and expiry/archive workflows. It is not a general media manager or public file delivery package.

Publishing Studio owns revision workflow. Media packages own file storage and downloads. Public or customer-facing acceptance surfaces should call package Actions and must not expose admin URLs, publication internals, raw hashes beyond the certificate contract, or authoring state.

## Runtime Surface

- Provider: `src/Providers/DocumentLifecycleServiceProvider.php`
- Admin resource: `src/Filament/Resources/Documents/`
- Actions: `src/Actions/`
- Data objects: `src/Data/`
- Models: `src/Models/`
- Command: `src/Console/Commands/ArchiveExpiredDocumentsCommand.php`
- Manifest contributions: `src/Manifest/`
- Tests: `packages/document-lifecycle/tests`

## Install And Setup

In a host Capell app, install with:

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

## Troubleshooting

| Symptom                                     | Likely cause                                               | Check                                                                                                | Fix                                                                                  |
| ------------------------------------------- | ---------------------------------------------------------- | ---------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| Expired documents remain active             | The host scheduler has not run the archive command         | In a host app, run `php artisan schedule:list` and check `capell:document-lifecycle:archive-expired` | Enable the scheduler or run the command in the host app after confirming due dates   |
| Certificate download cannot be verified     | The acceptance record is missing or the signature is stale | Confirm the acceptance exists in `legal_acceptances` and request a fresh signed certificate URL      | Regenerate the signed URL through the package surface instead of reusing an old link |
| Publication evidence does not match content | A document was changed without creating a new publication  | Compare the latest publication hash and stored snapshot for the document                             | Publish a new controlled document version through the package Action/admin workflow  |
