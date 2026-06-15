# Password Policy

<!-- prettier-ignore-start -->

## What This Plugin Adds

Password Policy is an **Available**, **Schema-owning** Capell package in the **Capell Operations** product group. It ships as `capell-app/password-policy` and extends these surfaces: admin, console.

Enforce admin password expiry, forced resets, reuse history, and breach (HIBP) checks across your Capell panels - configured from one settings screen, no code.

After install, admins get the Password Policy settings page, forced-change page, Users table/form extenders, health diagnostics, and password maintenance commands inside Capell Admin.

Status details:

- Status: Available
- Tier: premium
- Bundle: operations
- Composer package: `capell-app/password-policy`
- Namespace: `Capell\PasswordPolicy`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Enforce admin password expiry, forced resets, reuse history, and breach (HIBP) checks across your Capell panels - configured from one settings screen, no code.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Password Policy settings page (admin, required).
- Forced password change form (admin, required).
- User table password policy columns and filters (admin, required).

## Technical Shape

- Service providers: `Capell\PasswordPolicy\Providers\PasswordPolicyServiceProvider`.
- Config files: `packages/password-policy/config/capell-password-policy.php`.
- Migrations: `packages/password-policy/database/migrations/2026_05_10_190863_01_add_password_policy_columns_to_users_table.php`, `packages/password-policy/database/migrations/2026_05_10_190863_02_create_password_policy_password_histories_table.php`.
- Settings migrations: `packages/password-policy/database/settings/2026_05_10_190864_01_create_password_policy_settings.php`, `packages/password-policy/database/settings/2026_06_04_000001_01_add_password_complexity_settings.php`.
- Settings classes: `PasswordPolicySettings`.
- Filament classes: `PasswordPolicyPanelExtender`, `PasswordPolicyUserFormExtender`, `PasswordPolicyUserTableExtender`, `ForcedPasswordChangePage`, `PasswordPolicySettingsPage`, `PasswordPolicySettingsSchema`.
- Events: `PasswordChanged`, `PasswordExpired`, `UserMarkedForPasswordChange`.
- Actions: `BuildPasswordSecurityPostureReportAction`, `EvaluatePasswordPolicyAction`, `MarkUserForPasswordChangeAction`, `NotifyPasswordPolicyLifecycleEventAction`, `PrunePasswordHistoryAction`, `RecordPasswordHistoryAction`, `UpdatePasswordAction`, `ValidatePasswordChangeAction`.
- Data objects: `PasswordChangeData`, `PasswordPolicyStatusData`, `PasswordSecurityPostureReportData`, `ResolvedPasswordPolicySettingsData`.
- Command signatures: `capell:password-policy:doctor`.
- Console command classes: `ExpireStalePasswordsCommand`, `PasswordPolicyDoctorCommand`, `PrunePasswordHistoryCommand`, `RequirePasswordChangeCommand`.
- Manifest contributions: `admin-action-extender: Capell\PasswordPolicy\Manifest\PasswordPolicyAdminExtendersContribution`, `admin-page: Capell\PasswordPolicy\Manifest\PasswordPolicyAdminPagesContribution`, `console-command: Capell\PasswordPolicy\Manifest\PasswordPolicyConsoleCommandsContribution`, `health-check: Capell\PasswordPolicy\Manifest\PasswordPolicyHealthContribution`, `setting: Capell\PasswordPolicy\Manifest\PasswordPolicySettingsContribution`.
- Health checks: `Capell\PasswordPolicy\Health\PasswordPolicyHealthCheck`.
- Blade views: `packages/password-policy/resources/views/filament/pages/forced-password-change.blade.php`.

## Data Model

- Migration files: `2026_05_10_190863_01_add_password_policy_columns_to_users_table.php`, `2026_05_10_190863_02_create_password_policy_password_histories_table.php`.
- Required storage: `password_policy_password_histories`, plus `users.password_changed_at` and `users.must_change_password`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds Password Policy settings and forced-change pages when registered.
- Permissions: none declared in `capell.json`.
- Public routes: none detected in package route files.
- Database changes: package migrations are declared.
- Settings: `Capell\PasswordPolicy\Settings\PasswordPolicySettings`.
- Queues or schedules: none declared.
- Cache tags: none declared.
- Commands: `capell:password-policy:expire-stale`, `capell:password-policy:doctor`, `capell:password-policy:prune-history`, `capell:password-policy:require-change`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Configure package settings before testing production-like workflows.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |

## Quick Start

1. Install the package: `composer require capell-app/password-policy`.
2. Run the host application's package install and migration flow.
3. Open the related Capell admin surface and verify Password Policy appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Related packages: [Access Gate](../access-gate/README.md), [Diagnostics](../diagnostics/README.md), [Login Audit](../login-audit/README.md), [Privacy Center](../privacy-center/README.md).
- Focused tests: `vendor/bin/pest packages/password-policy/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
