# Login Audit

Login Audit records Capell user access history. It wraps Rappasoft's authentication log package with Capell settings, a Filament resource, a dashboard widget, user-resource bridge fields, and IP retention controls.

## At A Glance

- Package: `capell-app/login-audit`
- Namespace: `Capell\LoginAudit\`
- Surfaces: Filament admin, database
- Service providers: `packages/login-audit/src/Providers/AdminServiceProvider.php`, `packages/login-audit/src/Providers/LoginAuditServiceProvider.php`
- Capell dependencies: `capell-app/admin`
- Third-party dependencies: `rappasoft/laravel-authentication-log`, `tapp/filament-authentication-log`

## Why It Helps Your Capell Workflow

- Records login, failed login, logout, and throttled admin/user activity metadata so operators can investigate account activity from Capell.
- Helps owners spot authentication patterns without adding a bespoke security report to each project.
- Pairs with password policy and diagnostics to make account safety visible as an operational workflow.

## Best Used With

- [Password Policy](../password-policy/README.md)
- [Diagnostics](../diagnostics/README.md)
- [Dashboard Reports](../dashboard-reports/README.md)

## What It Adds

Login Audit records login, failed login, logout, and last-activity metadata for Capell users.

- Filament resource for authentication logs.
- Dashboard widget for recent access activity.
- Settings schema for retention, IP tracking, admin activity tracking, resource visibility, and user-resource bridge fields.
- Persistent admin middleware and frontend middleware alias for throttled activity tracking.
- User edit sidebar summary and relation manager when the bridge is enabled.

## Why It Matters

**For developers:** The package keeps vendor logging in place but routes Capell-specific behaviour through Actions, settings, bridges, and resource configurators.

**For teams:** Operators can review access history, failed logins, IP addresses, user agents, and recent activity without opening database records.

## Built With

This package makes its Composer dependencies visible because they are part of the value proposition, not just plumbing. When an upstream package has a public repository, its linked preview card points readers back to the maintainers so their work gets proper credit.

**Capell packages used here**

- [Capell Admin](https://github.com/capell-app/admin)

**Open-source packages used here**

- [Laravel Authentication Log](https://github.com/rappasoft/laravel-authentication-log) - authentication event storage for login, logout, IP, and user-agent history.
- [Filament Authentication Log](https://github.com/TappNetwork/filament-authentication-log) - the Filament UI layer for reviewing authentication activity inside the admin panel.

**Linked package previews**

[![Laravel Authentication Log GitHub preview](https://opengraph.githubassets.com/capell-readme/rappasoft/laravel-authentication-log)](https://github.com/rappasoft/laravel-authentication-log)

[![Filament Authentication Log GitHub preview](https://opengraph.githubassets.com/capell-readme/TappNetwork/filament-authentication-log)](https://github.com/TappNetwork/filament-authentication-log)

## Screens And Workflow

Screenshots are generated from [docs/screenshots.json](docs/screenshots.json) during package deployment.

- Authentication logs admin index.
- Authentication log table filters.
- Dashboard widget.
- Authentication log settings screen.
- User edit access summary.
- User authentication logs relation manager.

## Technical Shape

- `LoginAuditServiceProvider` registers config, translations, migrations, settings, protected table metadata, the `frontend.activity` middleware alias, and the `LoginAudit` model override.
- `AdminServiceProvider` registers the admin bridge, Filament resource, dashboard widget, settings contributor, persistent admin middleware, and daily `authentication-log:purge` schedule.
- `LoginAuditResource` extends `Tapp\FilamentAuthenticationLog\Resources\AuthenticationLogResource` and replaces the table with `LoginAuditsTable`.
- `AdminActivityMiddleware` and `UserActivityMiddleware` update matching audit rows without changing unrelated vendor audit state.

## Code Map

| Area      | Path                                 | Purpose                                                           |
| --------- | ------------------------------------ | ----------------------------------------------------------------- |
| Actions   | `packages/login-audit/src/Actions`   | Domain operations. Test these directly where possible.            |
| Models    | `packages/login-audit/src/Models`    | Eloquent records owned by the package.                            |
| Filament  | `packages/login-audit/src/Filament`  | Admin resources, pages, widgets, and settings UI.                 |
| HTTP      | `packages/login-audit/src/Http`      | Controllers, middleware, and request handling.                    |
| Providers | `packages/login-audit/src/Providers` | Registration, extension hooks, routes, migrations, and resources. |
| Resources | `packages/login-audit/resources`     | Views, translations, assets, and package resources.               |
| Config    | `packages/login-audit/config`        | Package configuration and publishable config.                     |
| Database  | `packages/login-audit/database`      | Migrations, seeders, and settings migrations.                     |
| Tests     | `packages/login-audit/tests`         | Package-level Pest coverage.                                      |

## Admin Surface

- Resources: `LoginAuditResource`.
- Widgets: `LoginAuditsWidget`.
- Settings: `LoginAuditSettingsSchema`.
- User resource bridge: `LoginAuditUserSchemaExtender` adds access summary state and `LoginAuditsRelationManager` when the host user model supports authentication logs.

## Session Boundary

Login Audit reports active sessions and device context from authentication-log rows. It does not terminate sessions directly: logout-other-devices and per-device revocation depend on the host app's session driver, guard setup, and remember-token policy. Pair this package with the host auth stack, Password Policy, Access Gate, or a session-management package when destructive session controls are required.

## Data And Persistence

- login_audit stores authenticatable type/id, IP address, user agent, login time, and logout time.
- Records belong polymorphically to authenticatable users.
- Config purge value defaults to 365 days and is overridden by the Login Audit retention setting before the daily purge runs.
- `ApplyLoginAuditSettingsAction` applies retention and IP tracking settings before the scheduled purge runs.
- `last_purged_at` records the last successful scheduled purge callback for operator visibility.
- `ResolveLoginAuditIpAddressAction` reads the configured CDN header when `login-audit.behind_cdn` is enabled; otherwise it uses the request IP.

- Models: `LoginAudit`.
- Migrations: `2026_05_10_190857_01_create_login_audit_table.php`.
- Config: `packages/login-audit/config/login-audit.php`.

## Extension Points

- Register Capell extension points, routes, migrations, settings, render hooks, and resources from service providers.

## Install Impact

- Adds login_audit table.
- Adds settings migration.
- Adds authentication log admin resource and widget.
- Listens to Laravel auth events configured in login-audit.php.
- May send new-device or failed-login notifications depending on config.

## Install And Setup

- Install with `composer require capell-app/login-audit` in the host Capell application.
- Run migrations through the host application package install flow.
- In this repository, verify package changes with `vendor/bin/pest`; do not use `php artisan`.

### Laravel 13 dependency note

Until `rappasoft/laravel-authentication-log` merges Laravel 13 support from [PR #140](https://github.com/rappasoft/laravel-authentication-log/pull/140), host Laravel 13 apps need the PR fork as a root Composer repository and alias:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/fdemb/laravel-authentication-log"
        }
    ],
    "require": {
        "rappasoft/laravel-authentication-log": "dev-main as 6.0.1"
    }
}
```

The package itself keeps the normal `^6.0|^5.0` dependency so it can move back to upstream tags when Laravel 13 support is released.

## Admin And Access

- `LoginAuditResource` (`packages/login-audit/src/Filament/Resources/LoginAudits/LoginAuditResource.php`)
- `LoginAuditsWidget` (`packages/login-audit/src/Filament/Widgets/LoginAuditsWidget.php`)
- `LoginAuditSettingsSchema` (`packages/login-audit/src/Filament/Settings/LoginAuditSettingsSchema.php`)
- `LoginAuditsRelationManager` (`packages/login-audit/src/Filament/Resources/Users/RelationManagers/LoginAuditsRelationManager.php`)

- `LoginAuditsWidget` is gated by `admin` and `super_admin` roles and the `login_audits` dashboard setting.
- The user-resource bridge is controlled by the package settings and the host admin bridge support.

## Screenshot Coverage

The Laravel 13 demo harness has screenshots for the Login Audit resource, table filters, settings schema, dashboard/widget configuration, user edit access summary, and user relation use case. The relation use case requires the host user model to expose Rappasoft's `authentications()` relationship, normally by using `AuthenticationLoggable`.

## Common Pitfalls

- Set CDN IP header config before trusting IP addresses behind a proxy.
- Confirm notification settings before production rollout.
- Run migrations before loading the resource.
- In Laravel 13 apps, install the Rappasoft PR fork at the root app level until upstream ships a compatible tag.

## Docs

- [docs index](docs/README.md)
- [credits-and-acknowledgements.md](docs/credits-and-acknowledgements.md)
- [overview.md](docs/overview.md)
- [settings-and-ip-resolution.md](docs/settings-and-ip-resolution.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/login-audit/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Put behaviour changes in `src/Actions/`; UI classes, commands, and controllers should call actions instead of owning domain logic.
