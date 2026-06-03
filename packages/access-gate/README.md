# Access Gate

Gate any Capell page, download, or member area behind login, email approval, guest links, schedules, or paid checkout — with full request, grant, and audit management in the admin.

## At A Glance

- Package: `capell-app/access-gate`
- Namespace: `Capell\AccessGate\`
- Surfaces: admin, frontend, console
- Service providers: `packages/access-gate/src/Providers/AccessGateServiceProvider.php`
- Capell dependencies: `capell-app/core`
- Third-party dependencies: `laravel/framework`, `lorisleiva/laravel-actions`

## Why It Helps Your Capell Workflow

- Protect gated pages, downloads, or member-only areas without building a one-off approval system for each site.
- Keeps request, grant, claim-token, and approval state in package tables so operators can audit access decisions.
- Works well with public submission flows because access requests can stay separate from page rendering and cacheable public HTML.

## Best Used With

- [Public Actions](../public-actions/README.md)
- [HTML Cache](../html-cache/README.md)
- [Diagnostics](../diagnostics/README.md)

## What It Adds

- Access gating foundations for Capell CMS.
- Admin resources: `AccessAreaResource`, `AccessGateEventResource`, `BrowserTokenResource`, `ClaimTokenResource`, `GrantResource`, `RegistrationResource`.
- Package setup or maintenance commands.
- Public request, claim, status, and logout endpoints for gated access flows.

## Code Map

| Area      | Path                                 | Purpose                                                             |
| --------- | ------------------------------------ | ------------------------------------------------------------------- |
| Actions   | `packages/access-gate/src/Actions`   | Domain operations. Test these directly where possible.              |
| Data      | `packages/access-gate/src/Data`      | Structured payloads, form state, view models, and integration data. |
| Enums     | `packages/access-gate/src/Enums`     | Persisted states and Filament option values.                        |
| Models    | `packages/access-gate/src/Models`    | Eloquent records owned by the package.                              |
| Filament  | `packages/access-gate/src/Filament`  | Admin resources, pages, widgets, and settings UI.                   |
| HTTP      | `packages/access-gate/src/Http`      | Controllers, middleware, and request handling.                      |
| Providers | `packages/access-gate/src/Providers` | Registration, extension hooks, routes, migrations, and resources.   |
| Resources | `packages/access-gate/resources`     | Views, translations, assets, and package resources.                 |
| Routes    | `packages/access-gate/routes`        | Route files loaded by the service provider.                         |
| Config    | `packages/access-gate/config`        | Package configuration and publishable config.                       |
| Database  | `packages/access-gate/database`      | Migrations, seeders, and settings migrations.                       |
| Tests     | `packages/access-gate/tests`         | Package-level Pest coverage.                                        |

## Admin Surface

- Resources: `AccessAreaResource`, `AccessGateEventResource`, `BrowserTokenResource`, `ClaimTokenResource`, `GrantResource`, `RegistrationResource`.
- Pages: `CreateAccessArea`, `EditAccessArea`, `ListAccessAreas`, `ListAccessGateEvents`, `ListBrowserTokens`, `ListClaimTokens`, `ListGrants`, `ListRegistrations`.

## Runtime Surface

- Controllers: `AccessGateStatusController`, `ClaimAccessGateTokenController`, `LogoutAccessGateController`, `ShowAccessRequestController`, `StoreAccessRequestController`.
- Routes: `packages/access-gate/routes/web.php`.

## Commands

- `capell:access-gate-doctor` (packages/access-gate/src/Console/Commands/AccessGateDoctorCommand.php)
- `capell:access-gate-install` (packages/access-gate/src/Console/Commands/AccessGateInstallCommand.php)
- `capell:access-gate-setup` (packages/access-gate/src/Console/Commands/AccessGateSetupCommand.php)

## Data And Persistence

- Models: `AccessGateModel`, `Area`, `BrowserToken`, `ClaimToken`, `Event`, `Grant`, `Registration`.
- Migrations: `2026_05_10_190838_01_create_access_gate_areas_table.php`, `2026_05_10_190838_02_create_access_gate_registrations_table.php`, `2026_05_10_190838_03_create_access_gate_grants_table.php`, `2026_05_10_190838_04_create_access_gate_claim_tokens_table.php`, `2026_05_10_190838_05_create_access_gate_browser_tokens_table.php`, `2026_05_10_190838_06_create_access_gate_events_table.php`.
- Config: `packages/access-gate/config/access-gate.php`.
- Data objects live in `src/Data/`; use them for payloads, form state, and view models.

## Extension Points

- Contracts: `AccessRequestMethod`, `RegistrationField`.
- Events: `RegistrationApproved`.
- Register Capell extension points, routes, migrations, settings, render hooks, and resources from service providers.

## Install And Setup

- Install with `composer require capell-app/access-gate` in the host Capell application.
- Run migrations through the host application package install flow.
- In this repository, verify package changes with `vendor/bin/pest`; do not use `php artisan`.

## Docs

- [docs index](docs/README.md)
- [access-requests.md](docs/access-requests.md)
- [overview.md](docs/overview.md)
- [screenshots.json](docs/screenshots.json)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/access-gate/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Treat public routes as untrusted input and keep validation, permission checks, and side effects inside actions or dedicated services.
- Put behaviour changes in `src/Actions/`; UI classes, commands, and controllers should call actions instead of owning domain logic.
- Use package `Data` classes at boundaries instead of passing anonymous arrays between layers.
- Use backed enums for persisted values and enum labels for Filament options.
