# Changelog

All notable changes to `capell-app/deployments` will be documented in this file.

## Unreleased

### 2026-06-03

- Replaced the stub `DeploymentsHealthCheck` with real diagnostics: it now verifies the `deployment_connections` storage table exists and that at least one Git provider has an OAuth client id configured, reporting only provider names (never credential values).
- Rewrote the marketplace summary, manifest description, and Composer description to describe the buyer outcome (no-terminal extension installs via reviewed pull requests) instead of internal plumbing.
- Promoted the two real connection-page screenshots (light and dark) into `marketplace.screenshots`.
- Dropped the unbacked `console` surface and `deployments-console` capability from the manifest, as no console command ships in this package.

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
