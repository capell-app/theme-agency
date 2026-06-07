# Changelog

All notable changes to `capell-app/public-actions` will be documented in this file.

## Unreleased

- Added durable destination fan-out preparation so successful submissions create pending dispatch-attempt rows before sync or queued delivery begins.
- Added `capell:public-actions:prune-submissions` for retention-based submission and dispatch-attempt pruning with dry-run and JSON output.
- Added configurable submit and Zapier API rate-limit overrides, including per-action and per-integration-token limits.
- Added manual replay for pending, retryable, and failed dispatch attempts from the dispatch-attempt admin table.
- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-06

- Added all eight Capell screenshot-runner captures for configured actions, action form, destinations, submissions, dispatch attempts, integration tokens, the frontend form, and Zapier discovery, then promoted those PNGs into marketplace media.
- Filled in the package overview with install notes, admin and frontend surfaces, screenshot coverage, public safety guidance, and focused verification guidance.
- Reused the canonical encoded webhook payload for request hashes, signatures, and non-GET request bodies.

## 2026-06-05

- Hardened HTTP webhook dispatch against DNS rebinding and unchecked redirects by resolving destination hosts once, blocking unresolved or private addresses by default, pinning cURL to the validated address, disabling redirect following, and covering private/link-local/internal redirect targets.

## 2026-06-03

- Replaced the stub `PublicActionsHealthCheck` with real diagnostics covering the four advertised health keys: webhook dispatch storage and adapter availability, webhook SSRF guard configuration, provider preset normalisation (Zapier, Pipedream, n8n, Make, generic), and the Form Builder bridge listener.
- Sharpened the marketplace summary, manifest description, and composer description to lead with signed, retried, fully-audited webhooks to Zapier, Make, n8n, and Pipedream.
