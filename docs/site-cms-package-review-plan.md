# Site, CMS, and Package Review Plan

> Updated: 2026-06-19.
>
> Source context: Claude chat "Site, CMS, and package review", current package docs, and `docs/improvement-plan-status.md`.

## Current State

The package review work has moved on since the original chat. The repository now has 81 package-local improvement plans, and the generated status document reports 0 active `Now` rows, 0 active `Next` rows, and no unchecked completion checklist items. Remaining `Later` and `Blocked` rows are intentional deferred product-depth or environment follow-ups, not active blockers.

That means the next plan should not be another broad documentation sweep. The useful work is to turn the review findings into platform-level safeguards, visible marketplace proof, security fixes, and a short list of revenue-relevant package completions.

## Goal

Make Capell's public site, CMS platform story, and package catalogue trustworthy from four angles:

- Owners can understand what each package does, why it matters, what risk it reduces, and what proof exists.
- Admins can see the surfaces, settings, permissions, and operational impact before installing.
- Editors and end users get safe public output, accessible theme experiences, and no leaked authoring machinery.
- Developers get accurate manifests, real health checks, reachable capabilities, and tests that keep package promises honest.

## Principles

- Fix catalogue-wide validation once before doing repeated per-package cleanup.
- Prefer de-scoping advertised-but-dead capabilities over shipping misleading manifests.
- Treat public output safety, tenant scoping, SSRF, open redirects, XSS, and prod demo credentials as release blockers.
- Use package-local plans for detail; use this document as the sequencing map.
- Keep verification narrow first, then run package-level tests and `COMPOSER=composer.local.json composer preflight` before committing implementation work.

## Phase 1: Platform Truth Checks

### 1. Run Real Health Checks

**Problem:** Many packages advertise critical health checks that are effectively no-ops. Diagnostics currently counts registered health checks rather than proving they run and report useful results.

**Plan:**

- Add a Diagnostics runner that executes registered package health checks with timeout handling and severity rollup.
- Add a distinct `unverified` state for stubbed or empty critical health checks.
- Add an architecture test that fails when a manifest declares `severity: critical` for a health check with no non-trivial `check()` or `report()` implementation.
- Reuse existing package doctor logic where possible instead of inventing duplicate probes.

**Verification:**

- `vendor/bin/pest packages/diagnostics/tests --configuration=phpunit.xml`
- A focused monorepo architecture test for critical health checks.

### 2. Validate Manifest Reality

**Problem:** `capell.json` often drifts from the code and assets: screenshots are missing from manifests, settings and permissions are omitted, console surfaces are declared without commands, cache metadata is wrong, and table names can be inaccurate.

**Plan:**

- Add or extend a manifest validator covering screenshots, settings classes, policies/permissions, console commands, required tables, routes/surfaces, cache metadata, and dependency declarations.
- Add a theme-specific assertion that manifest `extends` matches the provider theme definition.
- Run the validator in CI or the existing package-doc audit flow.

**Verification:**

- `php scripts/audit-package-docs.php`
- Focused Pest coverage for the validator.

### 3. Guard Capability Reachability

**Problem:** Several packages advertise capabilities that have no production caller, UI entry point, route, command, scheduler, hook, or resolver.

**Plan:**

- Define a small reachability contract for manifest capabilities.
- Add an architecture/static test that flags manifest-declared capabilities with no production entry point.
- For each flagged capability, make a build-or-de-scope decision in the package-local plan.

**Verification:**

- Focused architecture test.
- Package-specific feature tests when a capability is wired end to end.

## Phase 2: Security and Public Safety

### 4. Triage High-Severity Findings First

**Priority issues from the review:**

- `public-actions`: DNS-rebinding SSRF risk from pre-flight IP validation followed by re-resolving request execution.
- `insights`: Consent region trusted from request body and default hash salt risk.
- `url-manager`: Open redirect and admin-controlled regex ReDoS risk.
- `password-policy`: Null `password_changed_at` can lock out existing admins when expiry is enabled.
- `customer-portal`: Support request resource lacks site scoping.
- `contacts`: Cross-tenant dashboard stats and encrypted-column search failure.
- `content-sections` / `structured-content-library`: Raw editor-supplied summary/meta can reach public frontend output.
- `demo-kit`: Demo command can create a known-credential super-admin without a production guard.

**Plan:**

- Fix these as separate, focused commits or PRs, one package or closely related foundation group at a time.
- Add tests proving anonymous/non-admin public output does not expose authoring data or unsafe HTML.
- Add tenant-scope tests for admin resources that expose sensitive data.

**Verification:**

- Package-level Pest command for each touched package.
- `COMPOSER=composer.local.json composer preflight` before each focused commit.

### 5. Copy Public-Output Safety Tests Across Packages

**Problem:** Frontend-authoring has strong public safety coverage; many other packages rely on convention.

**Plan:**

- Identify package views, hooks, contributors, widgets, and cached output paths that can render public HTML.
- Add anonymous and non-admin leak tests for authoring markers, selectors, model IDs, field paths, signed editor URLs, package names, and admin-only labels.
- Add query/lazy-load checks for public Blade where package views are known to render model data.

**Verification:**

- Package-level public rendering tests.
- No broad suite until multiple shared render paths are touched.

## Phase 3: Marketplace and Site Proof

### 6. Surface Existing Screenshots

**Problem:** Many packages already have polished screenshot assets, but manifests and marketplace-facing docs do not reference them consistently.

**Plan:**

- Update package manifests and `docs/screenshots.json` files to reference committed assets accurately.
- Fail validation when a manifest declares screenshots that do not exist or omits committed package screenshot sets.
- Prioritise visually sold products: themes, SEO, campaign, insights, diagnostics, migration, URL, publishing, login audit, and address.

**Verification:**

- `php scripts/audit-package-docs.php`
- Focused manifest tests where present.

### 7. Rewrite Catalogue Copy Around Buyer Outcomes

**Problem:** Several package summaries duplicate descriptions or describe implementation rather than outcome.

**Plan:**

- Rewrite each weak summary as one clear buyer outcome plus one differentiator.
- Keep descriptions to 3-4 useful sentences: who it is for, what it adds, where it appears, and what operational risk it handles.
- Align package copy with extension-suite bundles: migration, growth, commerce, AI, operations, publishing, and themes.

**Verification:**

- Package docs audit.
- Manual review against owner/admin/developer audience checklist.

### 8. Connect Site Narrative to Package Reality

**Problem:** The public site story needs to match the actual CMS and package catalogue: Core, Admin, Frontend, and Packages are the core system map, but package proof must be concrete.

**Plan:**

- Make the site overview point to real package examples, screenshots, install impact, admin surfaces, and safety guarantees.
- Keep the public positioning honest: packages add optional Laravel capability; they do not magically replace application ownership.
- Add routes or content blocks for extension suites once package copy and manifest data are reliable enough to drive them.

**Verification:**

- Site/content tests in the sibling Capell app when those files are changed.
- Package manifest/docs audit in this repo.

## Phase 4: Highest-Leverage Package Completion

### 9. Build or De-Scope Dead Capabilities

**Priority packages:**

- `form-builder`: Admin UI and real handling for multi-step forms, payments, uploads, and submission management.
- `shopify-commerce`: Sync loop, polling/import continuation, customer sync producer, and webhooks.
- `automation-studio`: Queue/persistence/idempotency path and real run history.
- `knowledge-base`: Edit page, versioning entry points, related articles, search document build, and AI output entry points.
- `events`: Registration status transitions and waitlist promotion.
- `bookings`: Confirm/cancel/reminder UI or scheduled callers.
- `newsletter`: Real campaign send path or de-scope the claim.
- `ai-orchestrator` / `media-ai`: Real provider layer and enforced approval levels.
- `migration-assistant` / `wordpress-importer`: End-to-end import path for the acquisition funnel.

**Plan:**

- For each package, decide build vs de-scope first.
- If building, add the failing reachability or feature test before implementation.
- If de-scoping, remove manifest capabilities and marketplace copy in the same commit.

**Verification:**

- Package-level tests.
- Capability reachability test.
- Preflight before commit.

## Phase 5: Theme Line

### 10. Fix Theme Systemic Defects

**Problems:**

- Vertical theme manifests and provider definitions can disagree on `extends`.
- Brand tokens are present but hard-coded section colors prevent re-theming.
- Several premium themes have placeholder or missing real screenshots.
- Some flagship sections are static mocks rather than working forms or CTAs.
- Accessibility and dark-mode coverage are thin.

**Plan:**

- Add the manifest/provider `extends` guard and fix all mismatches.
- Move section color usage to theme tokens so Theme Studio presets actually recolor public output.
- Commit route-backed demo screenshots for every premium theme.
- Make one headline conversion section per theme real, or explicitly position it as a CTA to an installed capability.
- Fix skip links, heading structure, hard-coded English strings, and dark-mode inheritance in `foundation-theme` first where possible.

**Verification:**

- Theme package tests.
- Public output safety tests for anonymous renders.
- Browser/screenshot QA only after routes are confirmed available in the workbench or sibling app.

## Phase 6: Deferred Product Depth

The remaining `Later` rows are useful, but they should not block the platform truth pass:

- Experiments statistical significance.
- Retry/backoff for external providers.
- Cache-vs-personalisation strategy.
- Queueing work currently done on request hot paths.
- Paid upsell lines: Layout Pro, advanced DAM, inline editing pro, custom content types.
- Extension-suite packaging and bundle pages.

## Execution Order

1. Diagnostics real health checks and stub detection.
2. Manifest reality validator, including theme `extends`.
3. Capability reachability guard.
4. High-severity security fixes.
5. Public-output safety coverage expansion.
6. Screenshot and marketplace manifest alignment.
7. Catalogue copy and site narrative updates.
8. Highest-leverage package build/de-scope decisions.
9. Theme line systemic fixes.
10. Deferred product-depth work.

## Completion Criteria

- `docs/improvement-plan-status.md` remains at 0 active `Now` and `Next` rows unless a deliberate new active slice is opened.
- Platform validators catch stub health checks, manifest drift, and unreachable capabilities.
- High-severity findings have package-level tests and focused commits.
- Public-facing package and site copy matches the actual shipped capability.
- Package screenshots and manifests agree.
- Theme screenshots, token-driven styling, and accessibility basics are no longer systemic gaps.
