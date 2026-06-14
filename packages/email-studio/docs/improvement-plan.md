# Email Studio - Improvement Plan

- Package: `capell-app/email-studio`
- Status reviewed: 2026-06-14
- Primary audience: Capell package developers and Laravel integrators
- Phase: plan pass only. No implementation has started.

## Snapshot

Email Studio is Capell's package-owned transactional email substrate. It ships as an ordinary Laravel package with Capell admin contributions, Actions, Data objects, models, migrations, settings, queued delivery, MailTracker integration, and package-registered auth/template definitions. That is the right shape for Capell's extension model: packages register capability through service providers and public extension points, while host applications keep control of install, queues, configuration, and frontend output.

Current shipped surfaces:

- **Admin:** `AdminServiceProvider` contributes sent email, registered template, template variant, and template theme Filament resources, plus the Email Studio settings group.
- **Frontend/public:** `routes/web.php` exposes the tokenized provider webhook endpoint at `/mail/provider-events/{token}`. The `FrontendServiceProvider` is currently inert, so the manifest's `frontend` surface means webhook/tracking integration rather than rendered public UI.
- **Console/queues:** `SendEmailJob`, body pruning, MailTracker purge scheduling, queue names, lock TTL, and retention settings are present.
- **Auth email integration:** `AuthEmailServiceProvider` registers static auth templates, replaces Laravel verification/password reset `MailMessage` content by default, and optionally sends lifecycle auth emails through `SendEmailAction`.
- **Template authoring:** package definitions can be registered, previewed, customized into draft variants, activated, themed, and test-sent from admin resources.
- **Delivery and tracking:** `SendEmailAction` creates message and recipient rows, resolves profiles and renderable templates, checks suppressions, stores snapshots and attachments, and queues delivery through provider adapters. MailTracker captures open/click/content records through package-owned models and admin resources.
- **Provider events:** tokenized webhooks resolve an `EmailProfile`, optionally validate an HMAC signature, normalize payloads through the configured adapter, write `EmailEvent` rows idempotently, update recipient state, and auto-suppress bounce/complaint recipients.
- **Public-output safety:** there is no package-owned public Blade page. Public provider webhook responses are JSON/HTTP status only, and package Blade views are email or admin views. Future unsubscribe/reply/tracking routes must preserve the no-authoring-output boundary.

Source areas inspected:

- `capell.json`, `composer.json`, README, docs, screenshot contract, and marketplace assets.
- Providers, routes, Actions, Data objects, models, migrations, settings migrations, Filament resources, views, jobs, commands, support registries, provider adapters, health checks, and tests under `packages/email-studio`.
- Existing screenshots and screenshot contract for sent-email, template, and theme admin surfaces.

## Improvements

- **Refresh docs to match shipped scope.** Several docs still describe provider events as planned and template/profile authoring as future, while `capell.json` and code now ship provider webhooks, template authoring, theme authoring, auth template registration, MailTracker settings, and retention. Update `docs/README.md`, `docs/templates-and-providers.md`, `docs/email-studio-database.md`, and `CHANGELOG.md` before adding more product depth.
- **Clarify auth email audit semantics.** Verification and password reset replacements render themed `MailMessage` content, but they do not create native `EmailMessage` audit rows through `SendEmailAction`. Optional welcome/verified/login/lockout/reset-success lifecycle emails do. Product copy should avoid saying every auth email gets an Email Studio immutable snapshot unless the replacement path is moved onto the send pipeline.
- **Harden provider event recipient matching.** `RecordProviderEventAction` falls back from provider message ID to latest matching email hash within the profile. That can attach a webhook event to the wrong recipient when the same address receives repeated messages. Prefer provider IDs as required for status-changing events, or use a stronger fallback with event timestamp/message metadata.
- **Encrypt or explicitly justify provider secrets.** `EmailProfile.provider_settings` can hold `webhook_secret` and mailer configuration as plaintext JSON. The manifest lists no encrypted fields and no plaintext justification. Add a cast/encrypted value boundary or update the manifest with a deliberate redaction/justification policy.
- **Align suppression Action resolution.** `DeliverEmailMessageAction` resolves `CheckEmailSuppressionAction` through the container, but `SendEmailAction` constructs it directly when creating recipients. Resolve it consistently so tests and host apps can fake/replace the Action cleanly.
- **Constrain default delivery profiles.** `EmailProfileResolver` chooses the first default profile by scope/provider ordering, but the schema does not prevent multiple defaults for the same scope. Add a uniqueness rule or deterministic tie-break tests.
- **Tighten Postmark positioning.** `PostmarkEmailProviderAdapter` is a Symfony mailer adapter that defaults the mailer name to `postmark`; it is not a native Postmark API adapter. Rename/describe it as Postmark-over-Laravel-mailer or build native API sending and webhook verification.
- **Complete screenshot evidence.** Existing image files cover sent-email index/detail only. `docs/screenshots.json` now requires template index and theme captures too. Generate or mark blockers for those captures before marketplace release.
- **Add auth service-provider behavior tests.** Existing tests cover `BuildAuthEmailMailMessageAction`, but not the actual `VerifyEmail::toMailUsing`, `ResetPassword::toMailUsing`, and optional auth event listeners. Add focused provider tests for enabled/disabled settings and fallback behavior.

## Missing Features

- **Inbound reply ingestion.** `EmailReply`, `InboundEmailReplyData`, and adapter normalizers exist, but there is no route/action that records inbound replies.
- **One-click unsubscribe.** Suppressions are server-side only. There is no signed unsubscribe route, suppression self-service flow, or `List-Unsubscribe` / `List-Unsubscribe-Post` header support.
- **Profile and suppression management.** Template and theme authoring are present, but there is no native EmailProfile or EmailSuppression admin resource. Integrators must seed/configure profiles outside the advertised admin workflow.
- **Native provider depth.** SMTP and fake adapters are real. Postmark currently rides Laravel's mailer abstraction. There are no native HTTP clients, timeout policies, provider-specific webhook timestamp checks, or provider-specific signature formats.
- **Idempotent send requests.** `SendEmailData` has trigger metadata, but no idempotency key or dedupe guard for listener retries/double-fired events.
- **Scheduled sends.** Sends dispatch now or run synchronously; there is no `sendAt`/delayed transactional scheduling API.
- **Aggregate reporting.** Admin users can inspect sent MailTracker records, opens, clicks, and stored content, but there is no aggregate dashboard or Insights contribution.
- **A/B testing and variant analytics.** Variant versioning exists, but there is no weighted split, assignment, or winner selection.
- **Public/email compliance helpers.** Bounce/complaint suppression exists. Deliverability compliance still needs unsubscribe headers, domain/profile validation guidance, and provider-specific setup docs.

## Issues / Risks

- **P1 - Plaintext webhook secrets.** `provider_settings.webhook_secret` is stored in a regular JSON cast and the manifest has no plaintext justification. This is not public output, but it is sensitive operational configuration. Recommended fix: introduce encrypted profile settings or move secrets into a redacted settings/secret boundary.
- **P1 - Webhook fallback matching can mutate the wrong recipient.** If a provider webhook lacks `message_id`, the package updates the latest recipient for the same email hash under the profile. Repeated sends to one address make that ambiguous. Recommended fix: avoid status mutation without provider message ID unless the adapter can provide a stronger correlation key.
- **P2 - Marketplace/docs overclaim audit coverage for auth replacements.** Auth verification/reset replacements render Email Studio templates but do not create native `EmailMessage` audit rows. Recommended fix: either document the distinction or route replacements through the send pipeline.
- **P2 - Documentation drift can mislead integrators.** The docs still contain planned/future wording for shipped provider webhook behavior and stale gaps around template authoring/retention. Recommended fix: docs alignment should be the first implementation slice.
- **P2 - Missing profile/suppression admin surface weakens "studio" completeness.** Templates/themes are editable, but delivery profiles and suppressions still need developer/database setup. Recommended fix: add conservative admin resources with permissions, redaction, and tests.
- **P2 - Default profile ambiguity.** Multiple default profiles per site scope can produce surprising provider selection. Recommended fix: add a migration-level constraint where supported, plus resolver tests.
- **P3 - Navigation grouping is split between CapellAdmin contribution group and resource translation.** Resources return translated navigation groups, but `AdminServiceProvider` contributes them with literal `EmailStudio`. Recommended fix: use the package translation key or verify contribution grouping behavior in admin tests.
- **P3 - Docs mention reserved rate-limit/tolerance settings.** Provider webhook rate limiting is active through `capell-email-studio-provider-events`, while `webhook_tolerance_seconds` remains unused. Recommended fix: document which settings are live and which are reserved.
- **P3 - Changelog is behind newer template authoring work.** `CHANGELOG.md` stops at provider events and older copy alignment. Recommended fix: add concise unreleased entries for template authoring, auth replacements, theme editing, attachments, MailTracker purge, and screenshot contract changes.

Public/cache safety risk is currently low because Email Studio has no public rendered package page and package views do not query the database directly. The risk rises if unsubscribe, reply ingestion, preview links, or public tracking-token pages are added; those routes must return generic responses and never expose admin/editor metadata, model IDs, field paths, package internals, permissions, signed editor URLs, or authoring selectors.

## Marketplace & Positioning

For package developers and Laravel integrators, Email Studio should be positioned as the shared email layer that keeps transactional mail out of core and out of one-off application glue. The proof points are ordinary Laravel package mechanics: Composer install, package service providers, Filament admin resources, Actions/Data boundaries, model ownership, tests, and a provider adapter contract.

Recommended honest positioning:

> Email Studio gives Capell packages a shared transactional email layer: registered static templates, editable site and locale variants, branded themes, auth email replacements, queued provider delivery, suppressions, provider event ingestion, and MailTracker-backed sent-email visibility.

Use this distinction in marketplace copy:

- **Template authoring is shipped.** Admins can customize template variants and themes from package-registered defaults.
- **Delivery audit is shipped for `SendEmailAction`.** It creates Email Studio message/recipient records and queued provider delivery.
- **Auth verification/reset replacement is shipped as rendered `MailMessage` content.** It should not be sold as native Email Studio audit unless the send path changes.
- **Provider events are shipped.** Inbound replies are not.
- **Postmark support is Laravel-mailer based.** Do not imply native Postmark API/webhook semantics until built.

Target buyer:

- Agencies and Laravel teams running multi-site Capell installs who want one package-owned transactional email layer for form confirmations, auth messages, account notices, and future campaign/insights integration.

Keywords/tags:

- transactional-email
- email-templates
- template-authoring
- auth-email-overrides
- email-themes
- delivery-profiles
- provider-webhooks
- mail-tracking
- suppressions
- queued-email
- multi-site-email
- laravel-package

## Prioritized Roadmap

| Priority | Item | Effort | Impact | Notes |
| --- | --- | --- | --- | --- |
| P0 | Align docs, changelog, manifest language, screenshot contract, and current feature map | S | High | Fix stale "planned" wording before new implementation. |
| P0 | Add missing template/theme screenshots or mark runner blockers | S | High | Marketplace evidence currently trails the required screenshot contract. |
| P1 | Encrypt/redact `EmailProfile.provider_settings` secrets or document plaintext justification | M | High | Sensitive package tier needs a deliberate secret boundary. |
| P1 | Require strong provider-event recipient correlation before mutating recipient state | M | High | Prevent wrong-recipient status changes for repeated sends. |
| P1 | Add auth provider hook tests for verification/reset replacements and optional lifecycle emails | M | High | Protects default auth behavior and fallback paths. |
| P1 | Make profile default selection deterministic and constrained | S-M | Med | Avoid accidental provider drift in multi-profile installs. |
| P1 | Resolve `CheckEmailSuppressionAction` through the container inside `SendEmailAction` | S | Med | Keeps Action boundaries fakeable and consistent. |
| P2 | Add delivery profile admin management with redacted secret handling | M-L | High | Closes the biggest operator workflow gap. |
| P2 | Add suppression admin management and audit-safe release workflow | M | High | Needed for support teams before high-volume use. |
| P2 | Add one-click unsubscribe plus `List-Unsubscribe` headers | M | High | Important for compliance and bulk-adjacent transactional sends. |
| P2 | Build inbound reply ingestion route/action | M | Med | Existing data and adapter contracts are ready for this slice. |
| P2 | Build native Postmark API adapter or relabel current adapter | M | Med | Avoids provider overclaiming and improves webhook semantics. |
| P3 | Add idempotency keys and delayed send support | M | Med | Reduces duplicate sends and enables scheduled workflows. |
| P3 | Add aggregate delivery/open/click dashboard or Insights contribution | L | Med | Turns audit records into operator reporting. |
| P3 | Add variant split testing and winner selection | L | Low-Med | Useful after template usage volume grows. |

## Verification

Plan-pass inspection performed:

- Read package README, docs, manifest, composer metadata, screenshot contract, changelog, providers, routes, migrations, settings, Actions, Data objects, models, Filament resources, views, jobs, commands, provider adapters, health checks, and focused tests.
- Searched package views/routes for public Blade database access patterns. No public package view queries or lazy model lookups were found.
- Searched docs/manifest/source for shipped-vs-planned drift around provider events, template authoring, retention, secrets, and sensitive output.
- Confirmed this worktree has no `composer.local.json` and no host `vendor/bin/pest`; use the Docker harness or install dependencies for executable package tests.

Recommended verification before implementation slices:

```bash
./capell up
./capell pest packages/email-studio/tests --configuration=phpunit.xml
./capell preflight
```

For docs-only edits in this phase, the smallest useful checks are:

```bash
git diff --check -- packages/email-studio/docs/improvement-plan.md
python3 -m json.tool packages/email-studio/capell.json >/dev/null
python3 -m json.tool packages/email-studio/docs/screenshots.json >/dev/null
```

## Completion Checklist

- [x] Single-agent plan pass completed.
- [x] Existing dirty work checked for `packages/email-studio` before editing.
- [x] Source, docs, manifest, migrations/settings, tests, screenshots, providers, Actions/Data boundaries, admin/queue/email surfaces, and sensitive-output safety inspected.
- [x] Current shipped features separated from missing features and product-depth candidates.
- [x] Package-developer/Laravel-integrator positioning included.
- [x] Focused/static verification run after this document refresh.
- [x] Focused docs commit created if verification is clean and branch state is safe.
- [x] No implementation code changes made in this phase.
