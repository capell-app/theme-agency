# Newsletter

<!-- prettier-ignore-start -->

## What This Plugin Adds

Newsletter is an **Available**, **Schema-owning** Capell package in the **Capell Marketing** product group. It ships as `capell-app/newsletter` and extends these surfaces: admin, frontend.

Capture, confirm, and segment newsletter subscribers on every Capell site - with double opt-in, a public preference center, GDPR-grade consent evidence, and one-click sync to Mailchimp, Kit, and Campaign Monitor.

After install, admins get package-owned subscriber, segment, provider, import, and send management surfaces; public users get the subscription, confirmation, unsubscribe, webhook, and preference-center routes declared in the package manifest.

Status details:

- Status: Available
- Tier: premium
- Bundle: newsletter
- Composer package: `capell-app/newsletter`
- Namespace: `Capell\Newsletter`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Capture, confirm, and segment newsletter subscribers on every Capell site - with double opt-in, a public preference center, GDPR-grade consent evidence, and one-click sync to Mailchimp, Kit, and Campaign Monitor.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Subscribers admin index (admin, required).
- Create/edit subscriber form (admin, required).
- Provider connections admin index and form (admin, required).
- Provider audiences admin index and form (admin, required).
- Provider interest mappings admin index and form (admin, required).
- Form mappings admin index and form (admin, required).
- Newsletter tags admin index and form (admin, required).
- Segments admin index and form (admin, required).
- Import batches admin index (admin, required).
- Sync attempts admin index (admin, required).
- Newsletter overview stats (admin, required).
- Subscription confirmation route response (frontend, required).
- Unsubscribe route response (frontend, required).

## Technical Shape

- Service providers: `Capell\Newsletter\Providers\NewsletterServiceProvider`, `Capell\Newsletter\Providers\AdminServiceProvider`.
- Config files: `packages/newsletter/config/capell-newsletter.php`.
- Migrations: `packages/newsletter/database/migrations/2026_05_10_190861_01_create_newsletter_provider_connections_table.php`, `packages/newsletter/database/migrations/2026_05_10_190861_02_create_newsletter_subscribers_table.php`, `packages/newsletter/database/migrations/2026_05_10_190861_03_create_newsletter_provider_audiences_table.php`, `packages/newsletter/database/migrations/2026_05_10_190861_04_create_newsletter_consent_events_table.php`, `packages/newsletter/database/migrations/2026_05_10_190861_05_create_newsletter_provider_interest_mappings_table.php`, `packages/newsletter/database/migrations/2026_05_10_190861_06_create_newsletter_provider_subscribers_table.php`, `packages/newsletter/database/migrations/2026_05_10_190861_07_create_newsletter_public_tokens_table.php`, `packages/newsletter/database/migrations/2026_05_10_190861_08_create_newsletter_segments_table.php`, `packages/newsletter/database/migrations/2026_05_10_190861_09_create_newsletter_sync_attempts_table.php`, `packages/newsletter/database/migrations/2026_05_10_190861_10_create_newsletter_form_mappings_table.php`, `packages/newsletter/database/migrations/2026_05_10_190861_11_create_newsletter_import_batches_table.php`, `packages/newsletter/database/migrations/2026_05_10_190861_12_create_newsletter_processed_webhook_events_table.php`, `packages/newsletter/database/migrations/2026_05_31_120000_13_create_newsletter_sends_table.php`.
- Settings classes: `NewsletterSettings`.
- Models: `ConsentEvent`, `FormMapping`, `ImportBatch`, `NewsletterSend`, `ProviderAudience`, `ProviderConnection`, `ProviderInterestMapping`, `ProviderSubscriber`, `PublicToken`, `Segment`, `Subscriber`, `SyncAttempt`.
- Filament classes: `ScopesNewsletterResourcesToAssignedSites`, `FormMappingResource`, `CreateFormMapping`, `EditFormMapping`, `ListFormMappings`, `ImportBatchResource`, `ListImportBatches`, `NewsletterSendResource`, `CreateNewsletterSend`, `EditNewsletterSend`, `ListNewsletterSends`, `NewsletterTagResource`, `and 27 more`.
- Route files: `packages/newsletter/routes/web.php`.
- Policies: `AbstractNewsletterResourcePolicy`, `FormMappingPolicy`, `ImportBatchPolicy`, `NewsletterSendPolicy`, `ProviderAudiencePolicy`, `ProviderConnectionPolicy`, `ProviderInterestMappingPolicy`, `SegmentPolicy`, `SubscriberPolicy`, `SyncAttemptPolicy`.
- Events: `SubscriberConfirmed`, `SubscriberUnsubscribed`.
- Listeners: `SubscribeFromFormSubmission`.
- Actions: `ApplyNewsletterTagsAction`, `BuildDueNewsletterSendsAction`, `BuildListUnsubscribeHeadersAction`, `BuildNewsletterHealthDiagnosticsAction`, `BuildNewsletterSendHandoffPayloadAction`, `ConfirmSubscriberAction`, `CreatePreferenceCenterTokenAction`, `CreateUnsubscribeTokenAction`, `EvaluateNewsletterSegmentAction`, `ExportSubscribersAction`, `HandleProviderWebhookAction`, `ImportSubscribersAction`, `and 17 more`.
- Data objects: `ConsentEvidenceData`, `FormMappingData`, `PreferenceCenterData`, `PreferenceCenterSegmentData`, `PreferenceCenterUpdateData`, `ProviderAudienceData`, `ProviderInterestData`, `ProviderSubscriberData`, `ProviderSyncResultData`, `ProviderWebhookEventData`, `SubscriberData`, `UtmAttributionData`.
- Jobs: `SyncSubscriberToProviderJob`.
- Console command classes: `RequeueDueProviderSyncAttemptsCommand`.
- Manifest contributions: `admin-resource: Capell\Newsletter\Manifest\NewsletterAdminResourcesContribution`, `console-command: Capell\Newsletter\Manifest\NewsletterConsoleCommandsContribution`, `dashboard-widget: Capell\Newsletter\Manifest\NewsletterOverviewWidgetContribution`, `health-check: Capell\Newsletter\Manifest\NewsletterHealthContribution`, `model: Capell\Newsletter\Manifest\NewsletterModelsContribution`, `overview-stat: Capell\Newsletter\Manifest\NewsletterOverviewStatsContribution`, `route: Capell\Newsletter\Manifest\NewsletterFrontendRoutesContribution`, `scheduled-job: Capell\Newsletter\Manifest\NewsletterSyncRetryScheduleContribution`, `setting: Capell\Newsletter\Manifest\NewsletterSettingsContribution`.
- Health checks: `Capell\Newsletter\Health\NewsletterHealthCheck`.
- Blade views: `packages/newsletter/resources/views/preference-center.blade.php`.
- Cache tags: `newsletter`.

## Data Model

- Required tables: `newsletter_provider_connections`, `newsletter_subscribers`, `newsletter_provider_audiences`, `newsletter_consent_events`, `newsletter_provider_interest_mappings`, `newsletter_provider_subscribers`, `newsletter_public_tokens`, `newsletter_segments`, `newsletter_sync_attempts`, `newsletter_form_mappings`, `newsletter_import_batches`, `newsletter_processed_webhook_events`, `newsletter_sends`.
- Models: `ConsentEvent`, `FormMapping`, `ImportBatch`, `NewsletterSend`, `ProviderAudience`, `ProviderConnection`, `ProviderInterestMapping`, `ProviderSubscriber`, `PublicToken`, `Segment`, `Subscriber`, `SyncAttempt`.
- Migration files: `2026_05_10_190861_01_create_newsletter_provider_connections_table.php`, `2026_05_10_190861_02_create_newsletter_subscribers_table.php`, `2026_05_10_190861_03_create_newsletter_provider_audiences_table.php`, `2026_05_10_190861_04_create_newsletter_consent_events_table.php`, `2026_05_10_190861_05_create_newsletter_provider_interest_mappings_table.php`, `2026_05_10_190861_06_create_newsletter_provider_subscribers_table.php`, `2026_05_10_190861_07_create_newsletter_public_tokens_table.php`, `2026_05_10_190861_08_create_newsletter_segments_table.php`, `2026_05_10_190861_09_create_newsletter_sync_attempts_table.php`, `2026_05_10_190861_10_create_newsletter_form_mappings_table.php`, `2026_05_10_190861_11_create_newsletter_import_batches_table.php`, `2026_05_10_190861_12_create_newsletter_processed_webhook_events_table.php`, `2026_05_31_120000_13_create_newsletter_sends_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds subscriber, segment, provider, form mapping, import, sync attempt, and send Filament resources when registered.
- Permissions: none declared in `capell.json`.
- Public routes: subscription, confirmation, unsubscribe, one-click unsubscribe, provider webhook, and preference-center routes are declared in `capell.json`.
- Database changes: package migrations are declared.
- Settings: `NewsletterSettings` is declared and registered under the `newsletter` settings group.
- Queues or schedules: provider sync jobs and the `newsletter:sync-retry-due` scheduled command must run through the host scheduler/queue worker.
- Cache tags: `newsletter`.
- Commands: `newsletter:sync-retry-due` requeues due provider sync attempts.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/newsletter`.
2. Run the host application's package install and migration flow.
3. Open the related Capell admin surface and verify Newsletter appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Form Builder](../form-builder/README.md), [Tags](../tags/README.md), [Contacts](../contacts/README.md), [Customer Portal](../customer-portal/README.md).
- Focused tests: `vendor/bin/pest packages/newsletter/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
