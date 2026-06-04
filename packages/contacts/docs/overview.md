Status: **Available, schema-owning** · Kind: **package** · Tier: **premium** · Contexts: **admin**

# Contacts

Contacts provides the shared CRM record layer for Capell package integrations, including source identity matching and first-party adapters for contact-like records.

## Current Scope

- Central contact, organisation, lead, and activity models.
- Read-only Filament admin resources for CRM records.
- Contact identity matching by email, phone, package source identity, or model source.
- Source record sync action for packages such as Form Builder submissions, Newsletter subscribers, Comments authors, Access Gate registrations, Event attendees, Shopify customers, and Campaign Studio conversions.
- Event listeners for Form Builder submissions, Access Gate approvals, Event RSVPs, Comments, Campaign Studio conversions, and Shopify customer syncs when those packages are installed.
- Newsletter calls the shared sync action directly from subscriber lifecycle actions so subscribers are represented in the same central CRM record layer.
- Contact tagging and activity recording actions.
- Dashboard overview stats for contacts, organisations, open leads, and activities. Callers can pass a site id to `BuildContactsOverviewStatsAction` for site-scoped totals; omitting the site id intentionally returns global package totals.
- Privacy export and anonymization workflows through domain actions, Contact admin row actions, and the `capell-contacts:privacy` command.

## Source Sync Boundary

Packages should call `SyncContactSourceRecordAction` with `ContactSourceRecordData` when they have contact-like data from submissions, subscribers, registrations, attendees, customers, or conversions.

Contacts also registers optional listeners for supported first-party package events. These adapters are no-ops unless the source package class is available.

The action:

- finds or creates the contact using safe identity hashes;
- stores the package source key and source identifier;
- merges source metadata into the encrypted contact profile;
- applies normalized tags;
- optionally creates a lead;
- optionally records an activity linked to the source model.

## Privacy Workflows

Operators can run subject-access and erasure workflows from the Contact admin table or from the host application's console:

```bash
php artisan capell-contacts:privacy 123 --export --json
php artisan capell-contacts:privacy --email=person@example.test --site-id=1 --anonymize
```

`AuditContactPrivacyExportAction` returns the decrypted subject-access export and records a non-PII `privacy_export` activity with section counts and requester metadata. `AnonymizeContactWithAuditAction` runs the existing anonymizer, clears sensitive contact, lead, and activity fields, then records a non-PII `privacy_anonymization` activity so operators have an audit trail without retaining erased personal data.

## Testing

Run package tests with:

```bash
vendor/bin/pest packages/contacts/tests --configuration=phpunit.xml
```
