# Changelog

## v2.0.66 - 2026-07-04

### What's Changed

* Feat/theme catalogue differentiation by @howdu in https://github.com/capell-app/capell-packages/pull/128

**Full Changelog**: https://github.com/capell-app/capell-packages/compare/v2.0.65...v2.0.66

## v2.0.65 - 2026-07-04

Release v2.0.65 for Capell package library 4.x.

Targets Capell core API ^4.0.

## v2.0.64 - 2026-07-02

Release v2.0.64 for Capell package library 4.x.

## v2.0.63 - 2026-07-02

Release v2.0.63 for Capell package library 4.x.

Targets Capell core API ^4.0.

## v2.0.17 - 2026-06-01

### What's Changed

- feat: site discovery URL registry updates by @howdu in https://github.com/capell-app/capell-packages/pull/86

**Full Changelog**: https://github.com/capell-app/capell-packages/compare/v2.0.16...v2.0.17

## v2.0.14 - 2026-05-20

### Fixes

- Create missing split repositories and initialize their 4.x branches.
- Update the monorepo split matrix to match current package directories.
- Remove stale split targets for packages that no longer exist.

## v2.0.13 - 2026-05-20

### What's Changed

- chore(deps): bump danharrin/monorepo-split-github-action from 2.4.4 to 2.4.5 by @dependabot[bot] in https://github.com/capell-app/packages/pull/80

**Full Changelog**: https://github.com/capell-app/packages/compare/v2.0.12...v2.0.13

## v2.0.12 - 2026-05-05

**Full Changelog**: https://github.com/capell-app/packages/compare/v2.0.11...v2.0.12

## v2.0.11 - 2026-05-05

**Full Changelog**: https://github.com/capell-app/packages/compare/v2.0.10...v2.0.11

## v2.0.10 - 2026-05-04

**Full Changelog**: https://github.com/capell-app/packages/compare/v2.0.9...v2.0.10

## v2.0.9 - 2026-05-02

**Full Changelog**: https://github.com/capell-app/packages/compare/v2.0.8...v2.0.9

## Unreleased

### Upstream changes

- The `Capell\Core\ContentSync` feature in `capell-app/capell` has been renamed to `Capell\Core\Exchanger` ([capell#73](https://github.com/capell-app/capell/pull/73)). No packages in this repo referenced the old namespace, but host apps that bump both monorepos together should update:
  - `Capell\Core\ContentSync\*` imports → `Capell\Core\Exchanger\*`
  - `config('content-sync.*')` → `config('exchanger.*')`
  - `trans('content_sync.*')` → `trans('exchanger.*')`
  - Queue name `content-sync` → `exchanger` (drain old queue before deploy)
  - Storage paths under `content-sync/` → `exchanger/`
  

## v2.0.4 - 2025-12-12

### What's Changed

- Refactor Blade Components and Service Loaders to Use Frontend Facade and Improve Model Tracking by @howdu in https://github.com/capell-app/packages/pull/22

**Full Changelog**: https://github.com/capell-app/packages/compare/v2.0.3...v2.0.4

## v2.0.3 - 2025-12-07

### What's Changed

- Modernise Plugin Architecture, Unify Code Quality Workflows, and Refactor Address/Blog Resources and Frontend Integration by @howdu in https://github.com/capell-app/packages/pull/21

**Full Changelog**: https://github.com/capell-app/packages/compare/v2.0.2...v2.0.3

## v2.0.2 - 2025-12-02

### What's Changed

- Feature/4.x by @howdu in https://github.com/capell-app/packages/pull/20
- Bump actions/checkout from 5 to 6 by @dependabot[bot] in https://github.com/capell-app/packages/pull/19

**Full Changelog**: https://github.com/capell-app/packages/compare/v2.0.1...v2.0.2

## v2.0.1 - 2025-11-22

### What's Changed

- Update update-changelog.yml by @howdu in https://github.com/capell-app/packages/pull/14
- Update update-changelog.yml by @howdu in https://github.com/capell-app/packages/pull/15
- Refactor Address Package Model Registration and Schema Handling; Modernize Blog Package and Composer Tooling by @howdu in https://github.com/capell-app/packages/pull/16
- Updates by @howdu in https://github.com/capell-app/packages/pull/17
- Updates by @howdu in https://github.com/capell-app/packages/pull/18

**Full Changelog**: https://github.com/capell-app/packages/compare/v2.0.0...v2.0.1

## v.1.0.1 - 2025-06-10

**Full Changelog**: https://github.com/capell-app/capell/compare/v1.0.0...v.1.0.1

## v1.0.0 - 2025-06-09

### What's Changed

- Fix tests by @howdu in https://github.com/capell-app/capell/pull/1
- Hotfix/rename by @howdu in https://github.com/capell-app/capell/pull/2
- Hotfix/rename by @howdu in https://github.com/capell-app/capell/pull/3
- Hotfix/rename by @capell-app in https://github.com/capell-app/capell/pull/4

### New Contributors

- @howdu made their first contribution in https://github.com/capell-app/capell/pull/1
- @capell-app made their first contribution in https://github.com/capell-app/capell/pull/4

**Full Changelog**: https://github.com/capell-app/capell/commits/v1.0.0
