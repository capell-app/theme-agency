# Capell Contacts

Contacts is the shared CRM record layer for Capell package integrations. It captures contacts, organisations, leads, and activity from first-party package events into privacy-safe encrypted records.

## What It Provides

- Contact, organisation, lead, and activity models with package-owned tables.
- Read-only Filament resources for reviewing captured CRM records.
- Contact identity matching by email, phone, package source identity, or source model.
- Source sync actions for Form Builder submissions, Newsletter subscribers, Comments authors, Access Gate registrations, Event attendees, Shopify customers, and Campaign Studio conversions.
- Queued first-party event listeners that isolate source-sync failures from the originating workflow.
- Dashboard overview stats for contacts, organisations, open leads, and activities.
- Subject-access export and anonymization workflows through domain actions, Contact admin row actions, and the `capell-contacts:privacy` command.

## Source Sync

Packages can sync contact-like records through `SyncContactSourceRecordAction` with a `ContactSourceRecordData` payload. The action finds or creates a contact using non-reversible identity hashes, stores source metadata in encrypted profile data, applies normalized tags, creates an optional lead, and records activity against the source model.

First-party listeners are optional and only run when the source package classes are available. They implement Laravel queue contracts and report adapter failures without breaking form submissions, comments, registrations, webhooks, or campaign events.

## Privacy Workflows

Operators can run privacy workflows from the Contact admin table or the host application's console:

```bash
php artisan capell-contacts:privacy 123 --export --json
php artisan capell-contacts:privacy --email=person@example.test --site-id=1 --anonymize
```

`AuditContactPrivacyExportAction` returns a decrypted subject-access export and records a non-PII audit activity. `AnonymizeContactWithAuditAction` clears sensitive contact, lead, and activity fields, then records a non-PII anonymization audit event.

## Marketplace Evidence

The package includes Capell screenshot-runner captures for:

- Contacts admin index.
- Contact Activities admin index.

Dashboard widget, organisation, and lead screenshots remain follow-up evidence items until the screenshot runner can target those surfaces reliably.
