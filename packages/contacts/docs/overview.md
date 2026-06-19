# Contacts

<!-- prettier-ignore-start -->

## What This Plugin Adds

Contacts is an **Available**, **Schema-owning** Capell package in the **Capell Content** product group. It ships as `capell-app/contacts` and extends these surfaces: admin.

Contacts gives your Capell site a single, GDPR-aware customer record that fills itself. Every form submission, newsletter signup, event registration, comment, campaign conversion, and Shopify customer is matched to one encrypted contact by email, phone, or source identity, and enriched with a timeline of leads and activity. Admins get read-through CRM resources and an at-a-glance dashboard, while privacy export and anonymization actions support subject-access and erasure requests out of the box. Built as the shared data spine for the Capell content suite, it powers smarter segmentation across Newsletter, Campaign Studio, and Form Builder.

After install, admins get package-owned management or reporting surfaces inside Capell.

Status details:

- Status: Available
- Tier: premium
- Bundle: content-product
- Composer package: `capell-app/contacts`
- Namespace: `Capell\Contacts`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** The unified CRM layer for Capell - automatically captures contacts, organisations, leads, and activity from your forms, newsletter, events, comments, and Shopify store into one privacy-safe, encrypted record.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Contacts admin index (admin, required).
- Contact activities admin index (admin, required).

## Technical Shape

- Service providers: `Capell\Contacts\Providers\ContactsServiceProvider`, `Capell\Contacts\Providers\AdminServiceProvider`.
- Config files: `packages/contacts/config/capell-contacts.php`.
- Migrations: `packages/contacts/database/migrations/2026_05_31_000001_create_contacts_table.php`, `packages/contacts/database/migrations/2026_05_31_000002_create_contact_organisations_table.php`, `packages/contacts/database/migrations/2026_05_31_000003_create_contact_organisation_memberships_table.php`, `packages/contacts/database/migrations/2026_05_31_000004_create_contact_leads_table.php`, `packages/contacts/database/migrations/2026_05_31_000005_create_contact_activities_table.php`, `packages/contacts/database/migrations/2026_05_31_000006_add_source_identity_to_contacts_table.php`, `packages/contacts/database/migrations/2026_06_06_000001_add_site_last_seen_index_to_contacts_table.php`, `packages/contacts/database/migrations/2026_06_07_000001_create_contact_tags_tables.php`.
- Models: `Contact`, `ContactActivity`, `ContactTag`, `Lead`, `Organisation`.
- Filament classes: `ContactActivityResource`, `ListContactActivities`, `ContactResource`, `ListContacts`, `ViewContact`, `LeadResource`, `ListLeads`, `OrganisationResource`, `ListOrganisations`, `ContactsOverviewStatsWidget`.
- Policies: `AbstractContactsResourcePolicy`, `ContactActivityPolicy`, `ContactPolicy`, `LeadPolicy`, `OrganisationPolicy`.
- Listeners: `RunsQueuedContactSourceSync`, `SyncContactFromAccessGateRegistration`, `SyncContactFromCampaignConversion`, `SyncContactFromComment`, `SyncContactFromEventRegistration`, `SyncContactFromFormSubmission`, `SyncContactFromShopifyCustomer`.
- Actions: `AnonymizeContactAction`, `AnonymizeContactWithAuditAction`, `AuditContactPrivacyExportAction`, `BuildContactPrivacyExportAction`, `BuildContactsOverviewStatsAction`, `CoercesContactSourceValues`, `FindOrCreateContactAction`, `MergeContactsAction`, `RecordContactActivityAction`, `SyncAccessGateRegistrationContactAction`, `SyncCampaignConversionContactAction`, `SyncCommentContactAction`, `and 6 more`.
- Data objects: `ContactActivityData`, `ContactIdentityData`, `ContactSourceRecordData`, `ContactSourceSyncResultData`.
- Command signatures: `capell-contacts:privacy`.
- Console command classes: `ContactPrivacyCommand`.
- Manifest contributions: `admin-resource: Capell\Contacts\Manifest\ContactsAdminResourcesContribution`, `dashboard-widget: Capell\Contacts\Manifest\ContactsOverviewWidgetContribution`, `model: Capell\Contacts\Manifest\ContactsModelsContribution`.
- Health checks: `Capell\Contacts\Health\ContactsHealthCheck`.
- Blade views: `packages/contacts/resources/views/filament/contacts/activity-timeline.blade.php`.
- Cache tags: `contacts`.

## Data Model

- Required tables: `contacts`, `contact_tag_memberships`, `contact_tags`, `contact_organisations`, `contact_organisation_memberships`, `contact_leads`, `contact_activities`.
- Models: `Contact`, `ContactActivity`, `ContactTag`, `Lead`, `Organisation`.
- Migration files: `2026_05_31_000001_create_contacts_table.php`, `2026_05_31_000002_create_contact_organisations_table.php`, `2026_05_31_000003_create_contact_organisation_memberships_table.php`, `2026_05_31_000004_create_contact_leads_table.php`, `2026_05_31_000005_create_contact_activities_table.php`, `2026_05_31_000006_add_source_identity_to_contacts_table.php`, `2026_06_06_000001_add_site_last_seen_index_to_contacts_table.php`, `2026_06_07_000001_create_contact_tags_tables.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `ExportPrivacy:Contact`, `AnonymizePrivacy:Contact`, `View:Contact`, `View:Organisation`, `View:Lead`, `View:ContactActivity`.
- Public routes: none detected in package route files.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `contacts`.
- Commands: `capell-contacts:privacy`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |

## Quick Start

1. Install the package: `composer require capell-app/contacts`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Contacts appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Access Gate](../../access-gate/README.md), [Campaign Studio](../../campaign-studio/README.md), [Comments](../../comments/README.md), [Events](../../events/README.md), [Form Builder](../../form-builder/README.md), [Newsletter](../../newsletter/README.md), [Shopify Commerce](../../shopify-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/contacts/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
