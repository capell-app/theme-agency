# Access Gate

<!-- prettier-ignore-start -->

## What This Plugin Adds

Access Gate is an **Available**, **Schema-owning** Capell package in the **Capell Operations** product group. It ships as `capell-app/access-gate` and extends these surfaces: admin, frontend, console.

Gate any Capell page, download, or member area behind login, email approval, guest links, schedules, or Payments-backed paid checkout - with full request, grant, and audit management in the admin.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: operations
- Composer package: `capell-app/access-gate`
- Namespace: `Capell\AccessGate`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Gate any Capell page, download, or member area behind login, email approval, guest links, schedules, or paid checkout through the Payments package - with full request, grant, and audit management in the admin.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Access areas admin index (admin, required).
- Access area create/edit form (admin, required).
- Registrations admin index with review actions (admin, required).
- Grants admin index (admin, required).
- Claim tokens admin index (admin, required).
- Browser tokens admin index (admin, required).
- Access events admin index (admin, required).
- Public access request form (frontend, required).
- Public gated message (frontend, required).
- Public request CTA component (frontend, required).

## Screenshot Evidence

These captures are the package-owned visual contract for the admin pages, public pages, actions, workflows, and feature surfaces described above. Keep this section aligned with `docs/screenshots.json` whenever the package surface changes.

### Access areas admin index

![Access areas admin index](screenshots/access-areas-admin-index.png)

- Surface: admin · Target: AccessAreaResource.
- Documents: An administrator reviews which protected areas are active and how each area accepts or blocks access requests.
- Capture notes: Requires package migrations plus at least one seeded access area with a known route key, status, registration policy, and token policy.

### Access area create/edit form

![Access area create/edit form](screenshots/access-area-create-edit-form.png)

- Surface: admin · Target: AccessAreaResource.create.
- Documents: An administrator configures a protected area before routing private content through the access-gate middleware.
- Capture notes: Capture the key, status, schedule, registration policy, token policy, approval limit, allowlist, and metadata fields. The batch harness still needs install-flow verification for access-gate migrations.

### Registrations admin index with review actions

![Registrations admin index with review actions](screenshots/registrations-admin-index.png)

- Surface: admin · Target: RegistrationResource.
- Documents: An administrator triages access requests and uses approve, reject, resend claim, expire, or grant actions.
- Capture notes: Requires seeded pending, approved, rejected, and expired registrations tied to an access area so table filters and row actions are visible.

### Grants admin index

![Grants admin index](screenshots/grants-admin-index.png)

- Surface: admin · Target: GrantResource.
- Documents: An administrator audits who currently has access and revokes grants that should no longer unlock protected content.
- Capture notes: Requires seeded active, expired, and revoked grants across at least one subject type.

### Claim tokens admin index

![Claim tokens admin index](screenshots/claim-tokens-admin-index.png)

- Surface: admin · Target: ClaimTokenResource.
- Documents: An administrator checks whether approval links were issued, claimed, or expired.
- Capture notes: Requires seeded pending, consumed, and expired claim tokens linked to registrations or grants.

### Browser tokens admin index

![Browser tokens admin index](screenshots/browser-tokens-admin-index.png)

- Surface: admin · Target: BrowserTokenResource.
- Documents: An administrator reviews browser-bound access and revokes a token after device loss or policy change.
- Capture notes: Requires seeded active, expired, and revoked browser tokens tied to grants.

### Access events admin index

![Access events admin index](screenshots/access-events-admin-index.png)

- Surface: admin · Target: AccessGateEventResource.
- Documents: An administrator investigates the history of an access request or failed protected-page visit.
- Capture notes: Requires seeded request, approval, rejection, claim, grant, revoke, and middleware-denied events.

### Public access request form

![Public access request form](screenshots/public-access-request-form.png)

- Surface: frontend · Target: capell-access-gate.request.
- Documents: A visitor requests access to a protected area by submitting the configured public registration fields.
- Capture notes: Capture anonymously against a seeded area that allows public registration. Verify the HTML contains no authoring markers, editor URLs, model IDs, or admin-only labels.

### Public gated message

![Public gated message](screenshots/public-gated-message.png)

- Surface: frontend · Target: capell-access-gate::message.
- Documents: A visitor without an active grant sees the blocked state instead of the protected content.
- Capture notes: Requires a seeded protected route using `access-gate:{area}` middleware and no active grant for the current visitor.

### Public request CTA component

![Public request CTA component](screenshots/public-request-cta.png)

- Surface: frontend · Target: capell-access-gate::components.request-cta.
- Documents: A visitor follows an inline request CTA from a protected page or teaser block.
- Capture notes: Capture direct access-gate submission in the base package. Capture the Public Actions variant only in a separate run with `capell-app/public-actions` installed.

## Technical Shape

- Service providers: `Capell\AccessGate\Providers\AccessGateServiceProvider`.
- Config files: `packages/access-gate/config/access-gate.php`.
- Migrations: `packages/access-gate/database/migrations/2026_05_10_190838_01_create_access_gate_areas_table.php`, `packages/access-gate/database/migrations/2026_05_10_190838_02_create_access_gate_registrations_table.php`, `packages/access-gate/database/migrations/2026_05_10_190838_03_create_access_gate_grants_table.php`, `packages/access-gate/database/migrations/2026_05_10_190838_04_create_access_gate_claim_tokens_table.php`, `packages/access-gate/database/migrations/2026_05_10_190838_05_create_access_gate_browser_tokens_table.php`, `packages/access-gate/database/migrations/2026_05_10_190838_06_create_access_gate_events_table.php`, `packages/access-gate/database/migrations/2026_06_07_000001_add_claim_landing_url_to_access_gate_areas_table.php`, `packages/access-gate/database/migrations/2026_06_14_000001_add_announcement_bar_fields_to_access_gate_areas_table.php`.
- Models: `AccessGateModel`, `Area`, `BrowserToken`, `ClaimToken`, `Event`, `Grant`, `Registration`.
- Filament classes: `AccessAreaResource`, `CreateAccessArea`, `EditAccessArea`, `ListAccessAreas`, `BrowserTokenResource`, `ListBrowserTokens`, `ClaimTokenResource`, `ListClaimTokens`, `AccessGateFilamentOptions`, `AccessGateEventResource`, `ListAccessGateEvents`, `GrantResource`, `and 4 more`.
- Route files: `packages/access-gate/routes/web.php`.
- Policies: `AbstractAccessGateResourcePolicy`, `AccessAreaPolicy`, `AccessGateEventPolicy`, `BrowserTokenPolicy`, `ClaimTokenPolicy`, `GrantPolicy`, `RegistrationPolicy`.
- Events: `RegistrationApproved`.
- Listeners: `NotifyAdminsOfAccessRequest`.
- Actions: `ApproveNextRegistrationsAction`, `ApproveRegistrationAction`, `AreaIsCurrentlyGatingAction`, `ConsumeAccessGateClaimTokenAction`, `CreateAccessGateBrowserTokenAction`, `CreateAccessGateClaimTokenAction`, `CreateAccessGateGrantAction`, `CreatePaidAccessCheckoutForRegistrationAction`, `CreateRegistrationAction`, `EnsureAccessGateGrantCanIssueTokenAction`, `ExpireRegistrationAction`, `InstallAccessGatePackageAction`, `and 15 more`.
- Data objects: `AccessGateAccessResultData`, `AccessRequestMethodData`, `AnnouncementBarData`, `CreatePaidAccessCheckoutData`, `IssuedAccessGateTokenData`, `RegistrationFieldValue`.
- Command signatures: `capell:access-gate-audit-export`, `capell:access-gate-doctor`, `capell:access-gate-install`, `capell:access-gate-prune`, `capell:access-gate-setup`.
- Console command classes: `AccessGateAuditExportCommand`, `AccessGateDoctorCommand`, `AccessGateInstallCommand`, `AccessGatePruneCommand`, `AccessGateSetupCommand`.
- Manifest contributions: `admin-resource: Capell\AccessGate\Manifest\AccessAreaResourceContribution`, `admin-resource: Capell\AccessGate\Manifest\AccessGateEventResourceContribution`, `admin-resource: Capell\AccessGate\Manifest\BrowserTokenResourceContribution`, `admin-resource: Capell\AccessGate\Manifest\ClaimTokenResourceContribution`, `admin-resource: Capell\AccessGate\Manifest\GrantResourceContribution`, `admin-resource: Capell\AccessGate\Manifest\RegistrationResourceContribution`, `console-command: Capell\AccessGate\Manifest\AccessGateConsoleCommandsContribution`, `dashboard-widget: Capell\AccessGate\Manifest\PendingAccessRequestsWidgetContribution`, `health-check: Capell\AccessGate\Manifest\AccessGateHealthContribution`, `model: Capell\AccessGate\Manifest\AccessGateModelsContribution`, `route: Capell\AccessGate\Manifest\AccessGateRoutesContribution`. Paid access checkout creation is declared under manifest actions as `CreatePaidAccessCheckoutForRegistrationAction` with `PaidAccessCheckoutCreationContribution` traceability.
- Health checks: `Capell\AccessGate\Health\AccessGateHealthCheck`.
- Blade views: `packages/access-gate/resources/views/claimed.blade.php`, `packages/access-gate/resources/views/components/announcement-bar.blade.php`, `packages/access-gate/resources/views/components/request-cta.blade.php`, `packages/access-gate/resources/views/message.blade.php`, `packages/access-gate/resources/views/request.blade.php`.

## Data Model

- Required tables: `access_gate_areas`, `access_gate_registrations`, `access_gate_grants`, `access_gate_claim_tokens`, `access_gate_browser_tokens`, `access_gate_events`.
- Models: `AccessGateModel`, `Area`, `BrowserToken`, `ClaimToken`, `Event`, `Grant`, `Registration`.
- Migration files: `2026_05_10_190838_01_create_access_gate_areas_table.php`, `2026_05_10_190838_02_create_access_gate_registrations_table.php`, `2026_05_10_190838_03_create_access_gate_grants_table.php`, `2026_05_10_190838_04_create_access_gate_claim_tokens_table.php`, `2026_05_10_190838_05_create_access_gate_browser_tokens_table.php`, `2026_05_10_190838_06_create_access_gate_events_table.php`, `2026_06_07_000001_add_claim_landing_url_to_access_gate_areas_table.php`, `2026_06_14_000001_add_announcement_bar_fields_to_access_gate_areas_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `View:Area`, `Create:Area`, `Update:Area`, `Delete:Area`, `View:Registration`, `Update:Registration`, `View:Grant`, `Update:Grant`, `View:BrowserToken`, `Update:BrowserToken`, `View:ClaimToken`, `View:Event`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: none declared.
- Commands: `capell:access-gate-audit-export`, `capell:access-gate-doctor`, `capell:access-gate-install`, `capell:access-gate-prune`, `capell:access-gate-setup`.
- Paid checkout: requires `capell-app/payments`; Access Gate creates a `gated_access` checkout session for a registration and Payments fulfills the completed checkout into an approved grant.

## Public Cache And Privacy Boundaries

Public gated output is cacheable only when it stays generic. It may describe the gate, show public pricing, or offer a request-access CTA, but it must not include grant IDs, pending registration IDs, buyer emails, payment references, browser tokens, signed admin URLs, permissions, editor metadata, or private Customer Portal state.

Resolve personalized access after the public page load through authenticated package endpoints or Customer Portal surfaces. Static HTML should vary by site, language, route, access method, and public gate state, not by raw user IDs or private grant records.

Payment-gated flows should render public CTAs and create checkout or approval sessions through throttled POST Actions. Keep provider references and session tokens out of cached markup.

Audit exports are available through `capell:access-gate-audit-export`. Use `--area`, `--type`, `--from`, `--to`, and `--limit` to narrow evidence for a support case, and pass `--path=/absolute/path/access-gate-audit.csv` when the CSV should be written to disk instead of stdout.

When Customer Portal is installed, Access Gate contributes self-service items for active grants, pending access requests, and active browser sessions. Browser-token items are scoped by the portal account email and site, and expired or revoked sessions are excluded.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep active grants, browser tokens, pending registrations, and payment references out of public cached HTML.
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

1. Install the package: `composer require capell-app/access-gate`.
2. Run the required setup: `php artisan capell:access-gate-setup`.
3. Open the related Capell admin surface and verify Access Gate appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Customer Portal](../../customer-portal/README.md), [Diagnostics](../../diagnostics/README.md), [Html Cache](../../html-cache/README.md), [Payments](../../payments/README.md), [Public Actions](../../public-actions/README.md).
- Focused tests: `vendor/bin/pest packages/access-gate/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
