Status: **Available, schema-owning** · Kind: **package** · Tier: **premium** · Contexts: **admin**

# Contacts

Contacts provides the shared CRM record layer for Capell package integrations.

## Current Scope

- Central contact, organisation, lead, and activity models.
- Read-only Filament admin resources for CRM records.
- Contact identity matching by email, phone, package source identity, or model source.
- Source record sync action for packages such as Form Builder, Newsletter, Comments, Access Gate, Events, Shopify Commerce, and Campaign Studio.
- Event listeners for Form Builder submissions, Access Gate approvals, Event RSVPs, Comments, and Campaign Studio conversions when those packages are installed.
- Contact tagging and activity recording actions.

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

## Testing

Run package tests with:

```bash
vendor/bin/pest packages/contacts/tests --configuration=phpunit.xml
```
