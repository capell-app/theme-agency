# Access Gate

<!-- prettier-ignore-start -->

## What This Plugin Adds

Access Gate is an **Available**, **Schema-owning** Capell package in the **Capell Operations** product group. It ships as `capell-app/access-gate` and extends these surfaces: admin, frontend, console.

Gate any Capell page, download, or member area behind login, email approval, guest links, schedules, or paid checkout - with full request, grant, and audit management in the admin.

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

**For teams:** Gate any Capell page, download, or member area behind login, email approval, guest links, schedules, or paid checkout - with full request, grant, and audit management in the admin.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

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
- Public announcement bar (frontend, required).

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
- Actions: `ApproveNextRegistrationsAction`, `ApproveRegistrationAction`, `AreaIsCurrentlyGatingAction`, `ConsumeAccessGateClaimTokenAction`, `CreateAccessGateBrowserTokenAction`, `CreateAccessGateClaimTokenAction`, `CreateAccessGateGrantAction`, `CreateRegistrationAction`, `EnsureAccessGateGrantCanIssueTokenAction`, `ExpireRegistrationAction`, `InstallAccessGatePackageAction`, `ListAccessRequestMethodsAction`, `ResolveAccessGateAnnouncementBarAction`, `and 13 more`.
- Data objects: `AccessGateAccessResultData`, `AccessRequestMethodData`, `AnnouncementBarData`, `IssuedAccessGateTokenData`, `RegistrationFieldValue`.
- Command signatures: `capell:access-gate-doctor`, `capell:access-gate-install`, `capell:access-gate-setup`.
- Console command classes: `AccessGateDoctorCommand`, `AccessGateInstallCommand`, `AccessGateSetupCommand`.
- Manifest contributions: `admin-resource: Capell\AccessGate\Manifest\AccessAreaResourceContribution`, `admin-resource: Capell\AccessGate\Manifest\AccessGateEventResourceContribution`, `admin-resource: Capell\AccessGate\Manifest\BrowserTokenResourceContribution`, `admin-resource: Capell\AccessGate\Manifest\ClaimTokenResourceContribution`, `admin-resource: Capell\AccessGate\Manifest\GrantResourceContribution`, `admin-resource: Capell\AccessGate\Manifest\RegistrationResourceContribution`.
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
- Commands: `capell:access-gate-doctor`, `capell:access-gate-install`, `capell:access-gate-setup`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
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

1. Install the package: `composer require capell-app/access-gate`.
2. Run the required setup: `php artisan capell:access-gate-setup`.
3. Open the related Capell admin surface and verify Access Gate appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Customer Portal](../customer-portal/README.md), [Diagnostics](../diagnostics/README.md), [Html Cache](../html-cache/README.md), [Payments](../payments/README.md), [Public Actions](../public-actions/README.md).
- Focused tests: `vendor/bin/pest packages/access-gate/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
