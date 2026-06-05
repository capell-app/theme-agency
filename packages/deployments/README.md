# Deployments

Connect GitHub, GitLab, or Bitbucket repositories from the Capell admin, then install or update extensions by publishing Composer requirement changes through Git provider pull requests.

## At A Glance

- Package: `capell-app/deployments`
- Namespace: `Capell\Deployments\`
- Surfaces: Filament admin, authenticated HTTP callbacks, database
- Service providers: `packages/deployments/src/Providers/DeploymentsServiceProvider.php`
- Capell dependencies: `capell-app/admin`, `capell-app/core`
- Third-party dependencies: `laravel/framework`, `lorisleiva/laravel-actions`, `spatie/laravel-data`, `spatie/laravel-package-tools`

## Why It Helps Your Capell Workflow

- Helps operators connect GitHub, GitLab, or Bitbucket repositories through OAuth and publish Composer requirement changes from Capell-managed workflows.
- Keeps deployment connection state and provider-specific behavior inside one package instead of scattering Git provider code.
- Gives developers Actions for validating OAuth state, preparing requirement commits, and publishing requirement changes.

## Best Used With

- [Diagnostics](../diagnostics/README.md)
- [Agent Bridge](../agent-bridge/README.md)
- [Migration Assistant](../migration-assistant/README.md)

## What It Adds

- OAuth-backed deployment repository connections for GitHub, GitLab, and Bitbucket.
- A deployment repository page where operators enter the target owner/name before authorising the Git provider.
- A dashboard widget that shows deployment repository connection state to authorised deployment page viewers.
- A `PublishesComposerChanges` contract for install flows that need to publish Composer requirements without knowing provider APIs.

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

## Screens And Workflow

Screenshots are generated from [docs/screenshots.json](docs/screenshots.json) during package deployment. The committed captures show the Deployment Repository OAuth entry point; active-connection and OAuth-to-pull-request flow media still need real captures before Marketplace approval.

## Code Map

| Area      | Path                                 | Purpose                                                             |
| --------- | ------------------------------------ | ------------------------------------------------------------------- |
| Actions   | `packages/deployments/src/Actions`   | Domain operations. Test these directly where possible.              |
| Data      | `packages/deployments/src/Data`      | Structured payloads, form state, view models, and integration data. |
| Enums     | `packages/deployments/src/Enums`     | Persisted states and Filament option values.                        |
| Models    | `packages/deployments/src/Models`    | Eloquent records owned by the package.                              |
| Filament  | `packages/deployments/src/Filament`  | Admin resources, pages, widgets, and settings UI.                   |
| HTTP      | `packages/deployments/src/Http`      | Controllers, middleware, and request handling.                      |
| Providers | `packages/deployments/src/Providers` | Registration, extension hooks, routes, migrations, and resources.   |
| Resources | `packages/deployments/resources`     | Views, translations, assets, and package resources.                 |
| Routes    | `packages/deployments/routes`        | Route files loaded by the service provider.                         |
| Config    | `packages/deployments/config`        | Package configuration and publishable config.                       |
| Database  | `packages/deployments/database`      | Migrations, seeders, and settings migrations.                       |
| Tests     | `packages/deployments/tests`         | Package-level Pest coverage.                                        |

## Admin Surface

- Pages: `DeploymentConnectionPage`.
- Widgets: `DeploymentConnectionWidget` on the System Health dashboard.

## Runtime Surface

- Controllers: `BitbucketCallbackController`, `GitHubCallbackController`, `GitLabCallbackController`.
- Routes: `packages/deployments/routes/oauth.php`.

## Data And Persistence

- Models: `DeploymentConnection`.
- Migrations: `2026_05_10_190845_01_create_deployment_connections_table.php`.
- Config: `packages/deployments/config/capell-deployments.php`.
- Data objects live in `src/Data/`; use them for payloads, form state, and view models.

## Extension Points

- Contracts: `GitProviderContract`, `PublishesComposerChanges`.
- Register Capell extension points, routes, migrations, settings, render hooks, and resources from service providers.

### Publishing Composer Changes From Another Package

Packages that install extensions can consume `PublishesComposerChanges` through the container:

```php
use Capell\Deployments\Contracts\PublishesComposerChanges;
use Capell\Deployments\Data\ComposerRequirementData;

$result = app(PublishesComposerChanges::class)->publish(new ComposerRequirementData(
    composerName: 'capell-app/example-extension',
    versionConstraint: '^4.0',
    repositoryUrl: 'git@github.com:capell-app/example-extension.git',
    label: 'Example Extension',
));
```

The bound publisher is intentionally conservative: it publishes through the single active deployment connection. If a site has multiple active connections, call `PublishComposerRequirementAction::run($requirement, $connection)` with an explicit `DeploymentConnection` selected by the consuming workflow.

## Install And Setup

- Install with `composer require capell-app/deployments` in the host Capell application.
- Run migrations through the host application package install flow.
- In this repository, verify package changes with `vendor/bin/pest`; do not use `php artisan`.

## Docs

- [docs index](docs/README.md)
- [credits-and-acknowledgements.md](docs/credits-and-acknowledgements.md)
- [overview.md](docs/overview.md)

## Testing

Run package tests from the repository root:

```bash
vendor/bin/pest packages/deployments/tests --configuration=phpunit.xml
```

## Maintenance Notes

- Put behaviour changes in `src/Actions/`; UI classes, commands, and controllers should call actions instead of owning domain logic.
- Use package `Data` classes at boundaries instead of passing anonymous arrays between layers.
- Use backed enums for persisted values and enum labels for Filament options.
