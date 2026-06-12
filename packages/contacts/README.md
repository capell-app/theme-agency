# Capell Contacts

Contacts is the shared CRM record layer for Capell package integrations, collecting contacts, organisations, leads, and activity from first-party package events into privacy-aware records.

## At A Glance

| Field            | Value                                                                                                 |
| ---------------- | ----------------------------------------------------------------------------------------------------- |
| Composer package | `capell-app/contacts`                                                                                 |
| Namespace        | `Capell\Contacts`                                                                                     |
| Product group    | Capell Customer/Data                                                                                  |
| Surfaces         | Admin, console, queued listeners                                                                      |
| Providers        | `Capell\Contacts\Providers\ContactsServiceProvider`, `Capell\Contacts\Providers\AdminServiceProvider` |
| Admin resources  | Contacts, organisations, leads, contact activities                                                    |
| Command          | `capell-contacts:privacy` in a host Capell app                                                        |
| Source adapters  | Form Builder, Newsletter, Comments, Access Gate, Events, Shopify, Campaign Studio                     |

## Why It Helps Your Capell Workflow

Owners get one CRM-style layer for first-party activity instead of scattered package-specific identities. Admin users can review captured contacts, organisations, leads, activities, and dashboard stats from Capell.

Developers get source-sync Actions, queued listeners, identity DTOs, privacy export/anonymisation Actions, and tagging/merge workflows that keep package integrations out of UI code.

## What It Adds

- Contact, organisation, lead, tag, and activity models.
- Filament resources and a contacts overview widget.
- Source sync Actions for first-party package events and source records.
- Contact identity matching by email, phone, package source identity, or source model.
- Merge, tag, lead-status, and activity recording Actions.
- Privacy export/anonymisation Actions and `capell-contacts:privacy`.

## Boundaries

Contacts owns CRM aggregation and privacy workflows for its own records. Source packages still own their operational data and should emit events or call `SyncContactSourceRecordAction` rather than writing Contacts tables directly.

Public surfaces must not expose contact IDs, identity hashes, encrypted profile data, source metadata, package internals, or admin URLs.

## Runtime Surface

- Providers: `src/Providers/`
- Admin resources/widgets: `src/Filament/`
- Actions: `src/Actions/`
- Data objects: `src/Data/`
- Listeners: `src/Listeners/`
- Models: `src/Models/`
- Command: `src/Console/Commands/ContactPrivacyCommand.php`
- Tests: `packages/contacts/tests`

## Docs

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Improvement plan](docs/improvement-plan.md)
- [Screenshots contract](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/contacts/tests --configuration=phpunit.xml
```

## Troubleshooting

| Symptom                                     | Likely cause                                                                     | Check                                                                                              | Fix                                                                                              |
| ------------------------------------------- | -------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------ |
| Source event does not create a contact      | Optional source package class/listener is unavailable or queued listener failed  | Check queue failures and the relevant `SyncContactFrom...` listener                                | Install the source package, restart the worker, or call `SyncContactSourceRecordAction` directly |
| Duplicate contacts appear                   | Source identity data does not include a stable email, phone, or source model key | Inspect `ContactSourceRecordData` payloads                                                         | Add stable identity fields and merge existing records through `MergeContactsAction`              |
| Privacy export command cannot find a record | Email/site filters do not match stored identity hashes                           | In a host app, run `php artisan capell-contacts:privacy --email=... --site-id=... --export --json` | Use the correct site/email or locate the contact by admin resource first                         |
