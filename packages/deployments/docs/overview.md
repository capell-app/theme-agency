# Deployments Overview

The Deployments package stores Git provider connections and publishes Composer requirement changes for package installation flows.

## Responsibilities

- Manage active deployment connections.
- Prepare Composer requirement commits.
- Publish install changes through Git provider pull requests.
- Provide the `PublishesComposerChanges` contract used by package install flows.

Deployment connection secrets are stored through the package model casts and should remain encrypted at rest.

## Admin Surfaces

- `DeploymentConnectionPage` for configuring the active Git provider connection.
- `DeploymentConnectionWidget` for surfacing connection state in admin dashboards.

## Runtime Surfaces

The package registers authenticated OAuth callback routes under `capell/oauth`:

- `capell-deployments.oauth.github`
- `capell-deployments.oauth.gitlab`
- `capell-deployments.oauth.bitbucket`

These routes are workflow callbacks rather than public frontend pages, so screenshot coverage should focus on the admin connection page and widget. Callback routes should be verified by feature tests.

## Screenshot Coverage

The screenshot contract is stored in [screenshots.json](screenshots.json). Final capture should include the connection page and widget after demo connection data is prepared.
