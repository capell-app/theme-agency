# Package Security Checklist

Use this checklist when adding or changing Capell packages, public routes, admin resources, external integrations, stored credentials, public Blade, or package manifests.

## Required Checks

- Run `COMPOSER=composer.local.json composer security:contracts` after changing routes, manifests, public Blade, workflows, or external HTTP clients.
- Run `COMPOSER=composer.local.json composer security:all` before release-ready package work.
- Keep every package `capell.json` `security` section in sync by running `php scripts/sync-package-security-manifests.php` after route, model, policy, permission, or external-client changes.
- Stage only task-related changes. Do not include unrelated package churn or generated artifacts.

## Route And Public Surface

- Every package route must be represented in `security.publicSurface.routeNames`.
- Every CSRF-exempt route must also be one of:
    - throttled
    - signed
    - tokenized
    - webhook verified
- Public POST endpoints should use the narrowest practical throttle.
- Signed URLs must be temporary when they expose editor, preview, paid-download, billing, or account state.
- Token routes must store only hashed tokens unless a package-specific encrypted cast is used.
- Webhooks must validate provider signatures or shared secrets before mutating state.

## Secrets And Sensitive Data

- Secret-like model fields must be encrypted, hashed, or listed in `security.sensitiveData.plaintextJustifications`.
- Public or admin summaries must not expose raw API keys, OAuth tokens, webhook secrets, private keys, signed URLs, or token hashes.
- External provider errors saved to the database or logs must be redacted first.
- OAuth callback responses should log only safe keys such as error code, status, and provider message.

## Public Rendering

- Public Blade must not contain admin/editor markers, signed editor URLs, model IDs, field paths, permissions, package names, or authoring selectors.
- Public Blade must not query the database or lazy-load relationships. Load data in controllers, actions, view models, Livewire components, composers, or view components.
- Cached HTML must remain safe for anonymous visitors, signed-in users, admins, crawlers, and static exports.
- Packages with `performance.cacheSafety.cacheable=true` need tests proving no authoring or secret output is cached.

## Admin Resources

- Admin packages must declare their authorization posture in `security.adminSurface.authorization`.
- Prefer package permissions or policies over relying only on panel authentication for mutable resources.
- Site-scoped package data must be filtered by the actor's assigned sites in policies, queries, selectors, and bulk actions.
- Filament labels and messages must use translations.

## External HTTP Clients

- Every `Http::` client must set an explicit timeout.
- Long-running provider calls should use retry rules only when the remote operation is idempotent or retry-safe.
- User-configured webhook destinations must reject private network targets unless the package has an explicit local-development override.
- Request and response bodies that may contain secrets must be redacted before persistence.

## CI And Supply Chain

- GitHub Actions must use least-privilege `permissions`.
- Third-party Actions must be pinned to full commit SHAs.
- Dependabot is enabled for GitHub Actions, root Composer, root npm, and `packages/frontend-optimizer` npm.
- Dependabot auto-merge is limited to semver patch updates and still waits for required checks.
- GitHub-native CodeQL, dependency review, and secret scanning push protection are recommended org/repo settings, but the repo-owned checks must pass without them.
