# Deployments Overview

The Deployments package stores Git provider connections and publishes Composer requirement changes for package installation flows without requiring shell access.

## Responsibilities

- Manage active deployment connections for GitHub, GitLab, and Bitbucket repositories.
- Prepare Composer requirement commits.
- Publish install changes through Git provider pull requests.
- Provide the `PublishesComposerChanges` contract used by package install flows.

Deployment connection secrets are stored through the package model casts and should remain encrypted at rest.

## Admin Surfaces

- `DeploymentConnectionPage` for configuring the active Git provider connection.
- `DeploymentConnectionWidget` for surfacing connection state in admin dashboards.

Connection managers enter the repository owner/group and repository name before starting OAuth. Those coordinates are stored in the one-time OAuth state and used by the callback when creating the `DeploymentConnection`, so callbacks never persist fictional placeholder repositories.

## Runtime Surfaces

The package registers authenticated OAuth callback routes under `capell/oauth`:

- `capell-deployments.oauth.github`
- `capell-deployments.oauth.gitlab`
- `capell-deployments.oauth.bitbucket`

These routes are workflow callbacks rather than public frontend pages, so screenshot coverage should focus on the admin connection page and widget. Callback routes should be verified by feature tests.

## Composer Publishing Consumer

Install flows should resolve `Capell\Deployments\Contracts\PublishesComposerChanges` and pass a `ComposerRequirementData` payload. The default binding publishes through exactly one active deployment connection and throws when multiple active connections exist, because the consumer must then choose the intended repository explicitly and call `PublishComposerRequirementAction` with that `DeploymentConnection`.

## Screenshot Coverage

The screenshot contract is stored in [screenshots.json](screenshots.json). Final capture should include the connection page and registered dashboard widget after demo connection data is prepared.
