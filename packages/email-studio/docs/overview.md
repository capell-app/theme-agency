# Email Studio

<!-- prettier-ignore-start -->

## What This Plugin Adds

Email Studio is an **Available**, **Schema-owning** Capell package in the **Capell Communications** product group. It ships as `capell-app/email-studio` and extends these surfaces: admin, frontend, console.

Email Studio gives every Capell site a reliable transactional-email core. Packages register static Blade-backed template definitions with declared variables and sample data, while admins can customize site and locale variants, activate versions, preview with sample data, send test emails, and apply branded email themes. Auth email overrides replace Laravel verification and password reset messages by default, with welcome, verified, login, lockout, and reset-success lifecycle emails available as opt-in templates. Every send is rendered to an immutable snapshot, screened against site and global suppression lists, and delivered via provider adapters and queue jobs that record per-recipient outcomes for support and audit.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: communications
- Composer package: `capell-app/email-studio`
- Namespace: `Capell\EmailStudio`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Email Studio is Capell's transactional-email engine and template authoring surface: package-registered static defaults, safe {{ variable }} rendering, editable site and locale overrides, auth email replacements, branded themes, preview/test-send, delivery profiles, suppressions, and a queued audit trail.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Sent emails admin index (admin, required).
- Sent email detail view (admin, required).
- Registered email templates index (admin, optional).
- Email template themes (admin, optional).

## Screenshot Evidence

These captures are the package-owned visual contract for the admin pages, public pages, actions, workflows, and feature surfaces described above. Keep this section aligned with `docs/screenshots.json` whenever the package surface changes.

### Sent emails admin index

![Sent emails admin index](screenshots/sent-emails-index.png)

- Surface: admin · Target: SentEmailResource.
- Documents: A support user reviews sent transactional emails and delivery engagement signals in the Capell admin.
- Capture notes: Capture after seeding MailTracker sent email rows with subjects, sender/recipient addresses, opens, clicks, and stored content state.

### Sent email detail view

![Sent email detail view](screenshots/sent-email-view.png)

- Surface: admin · Target: SentEmailResource/ViewSentEmail.
- Documents: A support user opens an immutable sent email snapshot to inspect metadata, headers, tracked URLs, and stored HTML output.
- Capture notes: Capture after seeding a MailTracker sent email record with headers, stored HTML content, and at least one tracked URL click row.

## Technical Shape

- Service providers: `Capell\EmailStudio\Providers\AuthEmailServiceProvider`, `Capell\EmailStudio\Providers\EmailStudioServiceProvider`, `Capell\EmailStudio\Providers\AdminServiceProvider`, `Capell\EmailStudio\Providers\FrontendServiceProvider`.
- Config files: `packages/email-studio/config/capell-email-studio.php`.
- Migrations: `packages/email-studio/database/migrations/2026_05_10_190847_01_create_email_profiles_table.php`, `packages/email-studio/database/migrations/2026_05_10_190847_02_create_email_templates_table.php`, `packages/email-studio/database/migrations/2026_05_10_190847_03_create_email_template_variants_table.php`, `packages/email-studio/database/migrations/2026_05_10_190847_04_create_email_messages_table.php`, `packages/email-studio/database/migrations/2026_05_10_190847_05_create_email_recipients_table.php`, `packages/email-studio/database/migrations/2026_05_10_190847_06_create_email_events_table.php`, `packages/email-studio/database/migrations/2026_05_10_190847_07_create_email_replies_table.php`, `packages/email-studio/database/migrations/2026_05_10_190847_08_create_email_suppressions_table.php`, `packages/email-studio/database/migrations/2026_05_10_190847_09_create_email_template_registrations_table.php`, `packages/email-studio/database/migrations/2026_05_10_190847_10_create_email_tracking_tokens_table.php`, `packages/email-studio/database/migrations/2026_06_13_000001_add_email_template_authoring_tables.php`.
- Settings migrations: `packages/email-studio/database/settings/2026_06_05_000001_create_email_studio_settings.php`, `packages/email-studio/database/settings/2026_06_13_000001_add_email_template_authoring_settings.php`.
- Settings classes: `EmailStudioSettings`, `EmailStudioSettingsMigrationProvider`.
- Models: `EmailEvent`, `EmailMessage`, `EmailProfile`, `EmailRecipient`, `EmailReply`, `EmailSuppression`, `EmailTemplate`, `EmailTemplateRegistration`, `EmailTemplateTheme`, `EmailTemplateVariant`, `EmailTrackingToken`, `SentEmail`, `SentEmailUrlClicked`.
- Filament classes: `EmailTemplateThemeResource`, `CreateEmailTemplateTheme`, `EditEmailTemplateTheme`, `ListEmailTemplateThemes`, `EmailTemplateVariantResource`, `EditEmailTemplateVariant`, `ListEmailTemplateVariants`, `EmailTemplateResource`, `ListEmailTemplates`, `ListSentEmails`, `ViewSentEmail`, `SentEmailInfolist`, `and 2 more`.
- Route files: `packages/email-studio/routes/web.php`.
- Actions: `ActivateEmailTemplateVariantAction`, `ApplyEmailTemplateThemeAction`, `ApplyMailTrackerSettingsAction`, `BuildAuthEmailMailMessageAction`, `BuildEmailTemplatePreviewAction`, `CaptureEmailTemplateThemeScreenshotAction`, `CheckEmailSuppressionAction`, `CreateDefaultEmailTemplateThemeAction`, `CreateEmailTemplateOverrideAction`, `DeliverEmailMessageAction`, `MarkEmailMessageDeliveryFailedAction`, `PruneEmailBodiesAction`, `and 13 more`.
- Data objects: `EmailAddressData`, `EmailAttachmentData`, `EmailBodyPruneResultData`, `EmailContextData`, `EmailHeaderData`, `EmailTemplateDefinitionData`, `EmailTemplatePreviewData`, `EmailTemplateVariableData`, `InboundEmailReplyData`, `ProviderSendResultData`, `ProviderWebhookEventData`, `RenderedEmailData`, `and 3 more`.
- Jobs: `SendEmailJob`.
- Command signatures: `Capell\EmailStudio\Console\Commands\PurgeTrackedEmailsCommand`, `capell-email-studio:prune-bodies`.
- Console command classes: `PruneEmailBodiesCommand`, `PurgeTrackedEmailsCommand`.
- Manifest contributions: `admin-resource: Capell\EmailStudio\Manifest\SentEmailResourceContribution`, `admin-resource: Capell\EmailStudio\Manifest\TemplateAuthoringResourceContribution`, `model: Capell\EmailStudio\Manifest\SentEmailModelContribution`, `scheduled-job: Capell\EmailStudio\Manifest\TrackedEmailPurgeScheduleContribution`, `setting: Capell\EmailStudio\Manifest\EmailStudioSettingsContribution`.
- Health checks: `Capell\EmailStudio\Health\EmailStudioHealthCheck`.
- Blade views: `packages/email-studio/resources/views/emails/auth-rendered.blade.php`, `packages/email-studio/resources/views/emails/auth/lockout.blade.php`, `packages/email-studio/resources/views/emails/auth/login.blade.php`, `packages/email-studio/resources/views/emails/auth/password-reset-success.blade.php`, `packages/email-studio/resources/views/emails/auth/reset-password.blade.php`, `packages/email-studio/resources/views/emails/auth/verified.blade.php`, `packages/email-studio/resources/views/emails/auth/verify-email.blade.php`, `packages/email-studio/resources/views/emails/auth/welcome.blade.php`, `packages/email-studio/resources/views/emails/themes/default.blade.php`, `packages/email-studio/resources/views/filament/email-templates/preview.blade.php`, `packages/email-studio/resources/views/filament/sent-emails/click-rows.blade.php`, `packages/email-studio/resources/views/filament/sent-emails/content-preview.blade.php`.
- Cache tags: `email-studio`.

## Data Model

- Required tables: `email_template_themes`, `sent_emails`, `sent_emails_url_clicked`.
- Models: `EmailEvent`, `EmailMessage`, `EmailProfile`, `EmailRecipient`, `EmailReply`, `EmailSuppression`, `EmailTemplate`, `EmailTemplateRegistration`, `EmailTemplateTheme`, `EmailTemplateVariant`, `EmailTrackingToken`, `SentEmail`, `SentEmailUrlClicked`.
- Migration files: `2026_05_10_190847_01_create_email_profiles_table.php`, `2026_05_10_190847_02_create_email_templates_table.php`, `2026_05_10_190847_03_create_email_template_variants_table.php`, `2026_05_10_190847_04_create_email_messages_table.php`, `2026_05_10_190847_05_create_email_recipients_table.php`, `2026_05_10_190847_06_create_email_events_table.php`, `2026_05_10_190847_07_create_email_replies_table.php`, `2026_05_10_190847_08_create_email_suppressions_table.php`, `2026_05_10_190847_09_create_email_template_registrations_table.php`, `2026_05_10_190847_10_create_email_tracking_tokens_table.php`, `2026_06_13_000001_add_email_template_authoring_tables.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `ViewAny:SentEmail`, `View:SentEmail`, `ViewAny:EmailTemplateRegistration`, `View:EmailTemplateRegistration`, `ViewAny:EmailTemplateVariant`, `View:EmailTemplateVariant`, `Update:EmailTemplateVariant`, `ViewAny:EmailTemplateTheme`, `View:EmailTemplateTheme`, `Create:EmailTemplateTheme`, `Update:EmailTemplateTheme`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: `Capell\EmailStudio\Settings\EmailStudioSettings`, `Capell\EmailStudio\Manifest\EmailStudioSettingsContribution`.
- Queues or schedules: review package jobs or schedules before install.
- Cache tags: `email-studio`.
- Commands: `Capell\EmailStudio\Console\Commands\PurgeTrackedEmailsCommand`, `capell-email-studio:prune-bodies`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Configure package settings before testing production-like workflows.
- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
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

1. Install the package: `composer require capell-app/email-studio`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Email Studio appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Campaign Studio](../../campaign-studio/README.md), [Form Builder](../../form-builder/README.md), [Insights](../../insights/README.md).
- Focused tests: `vendor/bin/pest packages/email-studio/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
