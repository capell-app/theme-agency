# Password Policy

Admin password expiry, forced password changes, complexity rules, reuse history, and breached-password checks for Capell CMS.

## At A Glance

- Package: `capell-app/password-policy`
- Namespace: `Capell\PasswordPolicy\`
- Surfaces: Filament admin, database
- Service providers: `packages/password-policy/src/Providers/PasswordPolicyServiceProvider.php`
- Capell dependencies: `capell-app/admin`, `capell-app/core`
- Third-party dependencies: `laravel/framework`, `lorisleiva/laravel-actions`, `spatie/laravel-data`, `spatie/laravel-package-tools`

## Why It Helps Your Capell Workflow

- Adds password expiry, forced password changes, complexity rules, reuse history, and breached-password checks for Capell admin and user accounts.
- Helps operators enforce account hygiene without custom middleware in each host app.
- Pairs with login audit so security policy and authentication history can be reviewed together.

## Best Used With

- [Login Audit](../login-audit/README.md)
- [Diagnostics](../diagnostics/README.md)
- [Document Lifecycle](../document-lifecycle/README.md)

## What It Adds

- Password expiry, forced password changes, and password safety policy for Capell CMS.
- Settings-backed policy controls for password age, complexity, history, compromised checks, and forced changes.
- Admin-facing forced password change flow.

## Built With

This package makes its Composer dependencies visible because they are part of the value proposition, not just plumbing. When an upstream package has a public repository, its linked preview card points readers back to the maintainers so their work gets proper credit.

**Capell packages used here**

- [Capell Admin](https://github.com/capell-app/admin)
- [Capell Core](https://github.com/capell-app/core)

**Open-source packages used here**

- [Laravel Actions](https://github.com/lorisleiva/laravel-actions) - single-purpose action classes that keep package workflows out of controllers and Filament resources.
- [Spatie Laravel Data](https://github.com/spatie/laravel-data) - typed data objects for package boundaries, form state, settings, and structured results.
- [Spatie Laravel Package Tools](https://github.com/spatie/laravel-package-tools) - Laravel package bootstrapping for config, migrations, commands, translations, and service provider setup.

**Linked package previews**

[![Laravel Actions GitHub preview](https://opengraph.githubassets.com/capell-readme/lorisleiva/laravel-actions)](https://github.com/lorisleiva/laravel-actions)

[![Spatie Laravel Data GitHub preview](https://opengraph.githubassets.com/capell-readme/spatie/laravel-data)](https://github.com/spatie/laravel-data)

[![Spatie Laravel Package Tools GitHub preview](https://opengraph.githubassets.com/capell-readme/spatie/laravel-package-tools)](https://github.com/spatie/laravel-package-tools)

## Code Map

| Area      | Path                                     | Purpose                                                               |
| --------- | ---------------------------------------- | --------------------------------------------------------------------- |
| Actions   | `packages/password-policy/src/Actions`   | Domain operations. Test these directly where possible.                |
| Data      | `packages/password-policy/src/Data`      | Structured payloads, form state, view models, and integration data.   |
| Filament  | `packages/password-policy/src/Filament`  | Admin resources, pages, widgets, and settings UI.                     |
| HTTP      | `packages/password-policy/src/Http`      | Controllers, middleware, and request handling.                        |
| Providers | `packages/password-policy/src/Providers` | Registration, extension hooks, routes, migrations, and resources.     |
| Rules     | `packages/password-policy/src/Rules`     | Reusable host-auth validation rules for registration and reset flows. |
| Resources | `packages/password-policy/resources`     | Views, translations, assets, and package resources.                   |
| Config    | `packages/password-policy/config`        | Package configuration and publishable config.                         |
| Database  | `packages/password-policy/database`      | Migrations, seeders, and settings migrations.                         |
| Tests     | `packages/password-policy/tests`         | Package-level Pest coverage.                                          |

## Admin Surface

- Pages: `ForcedPasswordChangePage`.
- Settings: Extensions page modal surface for `PasswordPolicySettings`.
- Health: `BuildPasswordSecurityPostureReportAction` exposes enabled controls, installed persistence columns, and panel-aware forced-change URLs for Diagnostics.

## Data And Persistence

- Migrations: `2026_05_10_190863_01_add_password_policy_columns_to_users_table.php`, `2026_05_10_190863_02_create_password_policy_password_histories_table.php`.
- Settings migration: `2026_05_10_190864_01_create_password_policy_settings.php`.
- Config: `packages/password-policy/config/capell-password-policy.php`.
- Data objects live in `src/Data/`; use them for payloads, form state, and view models.

## Extension Points

- Register Capell extension points, routes, migrations, settings, render hooks, and resources from service providers.

## Install And Setup

- Install with `composer require capell-app/password-policy` in the host Capell application.
- Run the host application package install flow so both `database/migrations` and `database/settings` files are published before migrations run.
- In this repository, verify package changes with `vendor/bin/pest`; do not use `php artisan`.

## Docs

- [docs index](docs/README.md)
- [overview.md](docs/overview.md)
- [fortify.md](docs/fortify.md)
- [screenshots.json](docs/screenshots.json)
- [credits-and-acknowledgements.md](docs/credits-and-acknowledgements.md)

The required marketplace captures are declared in `docs/screenshots.json`, and
`capell.json` promotes the extension card plus real Capell runner captures for
the settings page, forced-change form, and Users-table policy columns.

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/password-policy/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Put behaviour changes in `src/Actions/`; UI classes, commands, and controllers should call actions instead of owning domain logic.
- Password expiry does not force-reset legacy users only because `password_changed_at` is missing. The install migration backfills existing users, and explicit `must_change_password` remains the operator-controlled reset path.
- Use package `Data` classes at boundaries instead of passing anonymous arrays between layers.
