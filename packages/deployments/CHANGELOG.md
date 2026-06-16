# Changelog

All notable changes to `capell-app/deployments` will be documented in this file.

## Unreleased

- Emits `DeploymentPublishSucceeded` and `DeploymentPublishFailed` around Composer requirement publishes so install workflows, Diagnostics, and other Operations packages can react to deployment lifecycle outcomes.

### 2026-06-04

- Registered `DeploymentConnectionWidget` on the System Health dashboard and gated it with the same deployment page view/manage permissions so repository coordinates are not shown to unauthorised dashboard users.
- Replaced the OAuth `repo_name = app` placeholder flow with repository owner/name entry before OAuth; callbacks now consume those coordinates from one-time OAuth state.
- Disabled connect buttons until repository coordinates are present and the selected provider has an OAuth client id configured.
- Made the bound `PublishesComposerChanges` publisher fail loudly when multiple active deployment connections exist, and documented the explicit-connection action path for consuming install flows.

### 2026-06-03

- Replaced the stub `DeploymentsHealthCheck` with real diagnostics: it now verifies the `deployment_connections` storage table exists and that at least one Git provider has an OAuth client id configured, reporting only provider names (never credential values).
- Rewrote the marketplace summary, manifest description, and Composer description to describe the buyer outcome (no-terminal extension installs via reviewed pull requests) instead of internal plumbing.
- Promoted the two real connection-page screenshots (light and dark) into `marketplace.screenshots`.
- Dropped the unbacked `console` surface and `deployments-console` capability from the manifest, as no console command ships in this package.

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
