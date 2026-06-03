# Changelog

All notable changes to `capell-app/publishing-studio` will be documented in this file.

## Unreleased

### 2026-06-03

#### Changed

- Rewrote the marketplace summary, package description, and `composer.json` description to lead with the review-first value proposition rather than a flat feature list.
- Expanded `marketplace.screenshots` from a single extension card to the desktop and mobile heroes plus the editorial timeline, draft workspace, review/approval, preview-link, scheduled-publishing, and stale-draft workflow captures.

#### Fixed

- Replaced the stubbed `PublishingStudioHealthCheck` (which asserted nothing despite being declared a `critical` health check) with a real probe. It now verifies the workflow, revision, and scheduler storage tables exist and that at least one publish-readiness check is configured and resolvable, so a silent no-op publish-gating install is surfaced by Diagnostics. Added pass and fail coverage.

### Earlier

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
