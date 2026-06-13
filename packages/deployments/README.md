# Deployments

<!-- prettier-ignore-start -->

## What This Plugin Adds

Deployments is an **Available**, **Schema-owning** Capell package in the **Capell Operations** product group. It ships as `capell-app/deployments` and extends these surfaces: admin.

Deployments lets site operators connect GitHub, GitLab, or Bitbucket repositories from the Capell admin and publish Composer requirement changes without server shell access. OAuth-backed connections store encrypted tokens, remember the repository owner/name, and let extension install flows open pull requests, auto-merge them, or make direct commits according to policy. It is the Operations bundle bridge between Marketplace installs and reviewed repository changes.

After install, admins get package-owned management or reporting surfaces inside Capell.

Status details:

- Status: Available
- Tier: premium
- Bundle: operations
- Composer package: `capell-app/deployments`
- Namespace: `Capell\Deployments`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Connect a Git repository once, then install or update Capell extensions from admin workflows that publish composer.json changes as reviewed pull requests.

## Screens And Workflow

Screenshot contract: `docs/screenshots.json`.

- Deployment connection page before a repository is connected (admin, required).

## Technical Shape

- Service providers: `Capell\Deployments\Providers\DeploymentsServiceProvider`.
- Config files: `packages/deployments/config/capell-deployments.php`.
- Migrations: `packages/deployments/database/migrations/2026_05_10_190845_01_create_deployment_connections_table.php`, `packages/deployments/database/migrations/2026_06_07_090000_02_create_deployment_publications_table.php`.
- Models: `DeploymentConnection`, `DeploymentPublication`.
- Filament classes: `DeploymentConnectionPage`, `DeploymentConnectionWidget`.
- Route files: `packages/deployments/routes/oauth.php`.
- Actions: `CancelDeploymentPublicationAction`, `ConnectDeploymentAction`, `ConsumeOAuthStateAction`, `CreateOAuthStateAction`, `ValidateOAuthStateAction`, `PrepareComposerRequirementCommitAction`, `PublishComposerRequirementAction`, `RecordDeploymentPublicationAction`, `RefreshDeploymentPublicationStatusAction`, `RefreshProviderTokenAction`.
- Data objects: `ComposerRequirementData`, `OAuthConnectionData`, `PublishComposerChangeResultData`, `PullRequestData`, `RepoFile`.
- Health checks: `Capell\Deployments\Health\DeploymentsHealthCheck`.
- Blade views: `packages/deployments/resources/views/filament/pages/deployment-connection.blade.php`, `packages/deployments/resources/views/filament/widgets/deployment-connection.blade.php`.

## Data Model

- Required tables: `deployment_connections`, `deployment_publications`.
- Models: `DeploymentConnection`, `DeploymentPublication`.
- Migration files: `2026_05_10_190845_01_create_deployment_connections_table.php`, `2026_06_07_090000_02_create_deployment_publications_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `View:DeploymentConnectionPage`, `Manage:DeploymentConnectionPage`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: none declared.
- Commands: none declared.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |

## Quick Start

1. Install the package: `composer require capell-app/deployments`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify Deployments appears.

## Next Steps

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Screenshot contract](docs/screenshots.json)
- [Marketplace assets](docs/assets/marketplace/)
- [Capell content language plan](../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../docs/erd/capell-and-package-erds.md)
- Focused tests: `vendor/bin/pest packages/deployments/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
