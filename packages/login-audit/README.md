# Login Audit

<!-- prettier-ignore-start -->

## What This Plugin Adds

Login Audit is an **Available**, **Schema-owning** Capell package in the **Capell Operations** product group. It ships as `capell-app/login-audit` and extends these surfaces: admin.

Login Audit gives Capell operators a complete, immutable record of who accessed the admin and storefront, when, from which IP and device, and whether each attempt succeeded. A dedicated Filament resource, dashboard widget, and per-user access summary make it easy to spot unusual activity and respond to incidents, while configurable retention keeps the log lean and aligned with your data-protection policy. IP capture can be disabled or resolved through your CDN (Cloudflare and similar), and the audit log is read-only by design so records can't be quietly altered. Built on the battle-tested Laravel authentication-log foundation and wired into Capell's settings, permissions, and Diagnostics.

After install, admins get package-owned management or reporting surfaces inside Capell.

Status details:

- Status: Available
- Tier: premium
- Bundle: operations
- Composer package: `capell-app/login-audit`
- Namespace: `Capell\LoginAudit`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, models, and Filament classes instead of pushing this behaviour into core or application code.

**For teams:** Tamper-proof access logging for Capell - track every login, failed attempt, logout, device, and session, with retention controls and an admin audit trail built for security and compliance reviews.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Authentication logs admin index (admin, required).
- Authentication log table filters (admin, required).
- Dashboard widget (admin, required).
- Authentication log settings screen (admin, required).
- User edit access summary (frontend, required).
- User authentication logs relation manager (frontend, required).

## Technical Shape

- Service providers: `Capell\LoginAudit\Providers\LoginAuditServiceProvider`, `Capell\LoginAudit\Providers\AdminServiceProvider`.
- Config files: `packages/login-audit/config/login-audit.php`.
- Migrations: `packages/login-audit/database/migrations/2026_05_10_190857_01_create_login_audit_table.php`.
- Settings migrations: `packages/login-audit/database/settings/2026_05_10_190858_01_add_login_audit_settings.php`, `packages/login-audit/database/settings/2026_06_06_000001_add_login_audit_suspicious_detection_settings.php`, `packages/login-audit/database/settings/2026_06_06_000002_add_login_audit_alert_settings.php`, `packages/login-audit/database/settings/2026_06_06_000003_add_login_audit_geo_location_setting.php`.
- Settings classes: `LoginAuditSettings`.
- Models: `LoginAudit`.
- Filament classes: `LoginAuditAdminPanelExtender`, `LoginAuditResource`, `LoginAuditsTable`, `LoginAuditsRelationManager`, `LoginAuditDashboardSettingsContributor`, `LoginAuditSettingsSchema`, `LoginAuditsWidget`.
- Policies: `LoginAuditPolicy`.
- Listeners: `DetectSuspiciousLoginFromAuthEvent`.
- Actions: `ApplyLoginAuditSettingsAction`, `BuildLoginAuditsCsvAction`, `BuildLoginAuditsQueryAction`, `DetectSuspiciousLoginAction`, `RecordLoginAuditPurgeAction`, `ResolveLoginAuditIpAddressAction`, `SendLoginAuditAdminAlertAction`, `ShouldTrackAdminActivityAction`, `ShouldTrackUserIpAddressesAction`, `UpdateLastSeenForActorAction`.
- Health checks: `Capell\LoginAudit\Health\LoginAuditHealthCheck`.

## Data Model

- Models: `LoginAudit`.
- Migration files: `2026_05_10_190857_01_create_login_audit_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: package migrations are declared.
- Settings: settings classes or settings migrations exist; verify the install flow registers them.
- Queues or schedules: none detected in standard package paths.
- Cache tags: none declared.
- Commands: none declared.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |

## Quick Start

1. Install the package: `composer require capell-app/login-audit`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Login Audit appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Password Policy](../password-policy/README.md), [Privacy Center](../privacy-center/README.md), [Diagnostics](../diagnostics/README.md), [Access Gate](../access-gate/README.md).
- Focused tests: `vendor/bin/pest packages/login-audit/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
