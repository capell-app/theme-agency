# Capell Packages Operational Hardening Program Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Lock down review gates, screenshot policy, PHPStan debt reduction, public-output safety, package health checks, and site-owner security reporting so operational regressions are caught automatically.

**Architecture:** Treat this as a repo-level quality program made of small, independently shippable slices. Prefer existing scripts, existing Pest contract tests, and package-local tests over new infrastructure; add shared helpers only where repeated package checks are already drifting.

**Tech Stack:** Laravel package monorepo, Capell manifests, GitHub Actions, Pest, PHPStan, Pint, Prettier, Node screenshot tooling, Capell package security audit scripts.

---

## Master Plan

### Execution Order

1. **Screenshot artifact policy** - decide whether fixture PNGs are intentional tracked baselines or ignored runtime artifacts before adding more gates.
2. **CI gates** - wire existing audit scripts and formatting checks into pull-request workflows so drift stops returning.
3. **PHPStan baseline reduction** - burn down ignored errors by risk-ranked package groups while keeping the baseline-growth guard active.
4. **Theme manifest contracts** - centralize the `extends: default` assertion and related theme manifest expectations.
5. **Browser-level public safety QA** - add route-level anonymous checks for public-route packages, starting with the highest-risk packages.
6. **Package health checks** - standardize health-check coverage after manifests and public routes are already guarded.
7. **Site-owner security surface report** - generate an operator-facing report from the same manifest/security metadata used by CI.

### Commit Boundaries

- Commit each numbered plan as its own commit.
- Do not stage unrelated dirty work, especially generated screenshots unless the screenshot policy explicitly promotes them.
- For every implementation slice, run the narrowest relevant command first, then `COMPOSER=composer.local.json composer preflight` before committing.
- For CI, manifest, public-route, or reporting slices, also run the specific script or Pest file named in that slice.

### Program Verification

Run after all slices are complete:

```bash
COMPOSER=composer.local.json composer preflight
php scripts/audit-package-security.php
php scripts/audit-manifest-v3.php
node scripts/validate-screenshot-manifests.js
npx prettier --check 'packages/**/capell.json'
vendor/bin/pest tests/Packages/ManifestTruthTest.php tests/Packages/Security/PackageSecurityContractTest.php --configuration=phpunit.xml
bash scripts/check-phpstan-baseline-growth.sh
```

Expected: all commands pass. `bash scripts/check-phpstan-baseline-growth.sh` must print current debt less than or equal to the base debt.

---

## Plan 1: Screenshot Artifact Policy

**Goal:** Make generated screenshot fixture behavior explicit so routine full-suite runs do not create ambiguous dirty work.

**Decision:** Keep committed fixture PNGs only when they are intentional visual baselines. Generated output from routine test runs should go to a temporary directory unless the runner is invoked in baseline-refresh mode.

**Files:**

- Modify: `tests/Packages/Support/ThemeDemoLayoutScreenshots.php`
- Modify: `tests/Packages/Feature/ThemeDemoLayouts/*ThemeDemoLayoutScreenshotTest.php` only if the helper API requires an explicit mode argument.
- Modify: `.gitignore` if runtime output remains under the repo.
- Modify: `docs/package-screenshot-automation.md`
- Test: `tests/Packages/Feature/ThemeDemoLayouts/FoundationThemeDemoLayoutScreenshotTest.php`

### Task 1.1: Confirm Current Artifact Behavior

- [ ] Run:

```bash
git status --short tests/Packages/Fixtures/theme-demo-layout-screenshots
vendor/bin/pest tests/Packages/Feature/ThemeDemoLayouts/FoundationThemeDemoLayoutScreenshotTest.php --configuration=phpunit.xml
git status --short tests/Packages/Fixtures/theme-demo-layout-screenshots
```

- [ ] Expected: the test passes. If the final `git status` shows modified PNGs, continue with Task 1.2. If it stays clean, document that fixture changes must be promoted manually and continue with Task 1.4.

### Task 1.2: Add Baseline Refresh Mode

- [ ] Update `tests/Packages/Support/ThemeDemoLayoutScreenshots.php` so the default write target is outside tracked fixtures, for example `storage/framework/testing/theme-demo-layout-screenshots`.
- [ ] Add an explicit environment variable gate:

```php
$refreshBaselines = filter_var(env('CAPELL_REFRESH_THEME_SCREENSHOT_FIXTURES', false), FILTER_VALIDATE_BOOL);
```

- [ ] When `$refreshBaselines` is `true`, write to `tests/Packages/Fixtures/theme-demo-layout-screenshots`.
- [ ] When `$refreshBaselines` is `false`, write generated screenshots to the test output directory and compare against committed fixtures without overwriting them.
- [ ] Keep all public method signatures explicitly typed.

### Task 1.3: Protect Runtime Output

- [ ] If the helper must write under the repo, add the exact generated path to `.gitignore`:

```gitignore
/storage/framework/testing/theme-demo-layout-screenshots/
```

- [ ] Do not ignore `tests/Packages/Fixtures/theme-demo-layout-screenshots/`; committed baselines must remain visible to review.

### Task 1.4: Document Promotion Workflow

- [ ] Update `docs/package-screenshot-automation.md` with:

```markdown
### Theme Demo Layout Fixture Policy

Routine Pest runs must not overwrite committed PNG fixtures. To refresh intentional baselines, run the target test with `CAPELL_REFRESH_THEME_SCREENSHOT_FIXTURES=1`, visually review the changed PNGs, then stage only the accepted fixture changes.
```

### Task 1.5: Verify And Commit

- [ ] Run:

```bash
vendor/bin/pest tests/Packages/Feature/ThemeDemoLayouts/FoundationThemeDemoLayoutScreenshotTest.php --configuration=phpunit.xml
git status --short tests/Packages/Fixtures/theme-demo-layout-screenshots
COMPOSER=composer.local.json composer preflight
```

- [ ] Expected: Pest and preflight pass. `git status` must not show unexpected PNG changes after the normal run.
- [ ] Commit:

```bash
git add tests/Packages/Support/ThemeDemoLayoutScreenshots.php docs/package-screenshot-automation.md .gitignore
git commit -m "test: stabilize theme screenshot fixture policy"
```

---

## Plan 2: Manifest And Security CI Gates

**Goal:** Make package security, manifest v3, screenshot manifest, and `capell.json` formatting checks mandatory in CI.

**Files:**

- Modify: `.github/workflows/code-quality-and-styling.yml`
- Modify: `.github/workflows/security.yml`
- Modify: `.github/workflows/screenshots.yml` if screenshot validation belongs beside the runner workflow.
- Modify: `package.json`
- Modify: `composer.json`
- Modify: `composer.local.json`
- Test: no new PHP test required unless scripts need new fixtures.

### Task 2.1: Add Dedicated Script Aliases

- [ ] Add Composer scripts to both `composer.json` and `composer.local.json`:

```json
"manifest:check": "@php scripts/audit-manifest-v3.php",
"security:package-audit": "@php scripts/audit-package-security.php"
```

- [ ] Keep the existing `security:contracts` script intact. If it already includes `scripts/audit-package-security.php`, do not duplicate execution in the same workflow job.

- [ ] Add Node scripts to `package.json`:

```json
"capell-json:check": "npx prettier --check 'packages/**/capell.json'",
"screenshots:check": "node scripts/validate-screenshot-manifests.js"
```

`screenshots:check` already exists; keep the existing value if present.

### Task 2.2: Wire Pull Request Paths

- [ ] In `.github/workflows/code-quality-and-styling.yml`, include these paths in the trigger:

```yaml
- 'docs/package-screenshot-manifest.json'
- 'packages/**/docs/screenshots.json'
- 'scripts/audit-manifest-v3.php'
- 'scripts/validate-screenshot-manifests.js'
- 'package.json'
- 'package-lock.json'
```

- [ ] In `.github/workflows/security.yml`, include:

```yaml
- 'scripts/audit-manifest-v3.php'
- 'scripts/validate-screenshot-manifests.js'
- 'docs/package-screenshot-manifest.json'
- 'packages/**/capell.json'
- 'packages/**/docs/screenshots.json'
```

### Task 2.3: Add CI Steps

- [ ] In `.github/workflows/code-quality-and-styling.yml`, after Node dependencies are installed or add Node setup if absent, run:

```yaml
- name: Validate package manifests
  run: composer manifest:check

- name: Validate screenshot manifests
  run: npm run screenshots:check

- name: Check capell.json formatting
  run: npm run capell-json:check
```

- [ ] Prefer placing these in the existing PHP Quality job after dependency install and before PHPStan, so failures are fast and visible.
- [ ] If adding Node setup to the PHP Quality job, use Node 22 and `npm ci`, matching `.github/workflows/security.yml`.

### Task 2.4: Verify Locally

- [ ] Run:

```bash
COMPOSER=composer.local.json composer manifest:check
php scripts/audit-package-security.php
node scripts/validate-screenshot-manifests.js
npx prettier --check 'packages/**/capell.json'
COMPOSER=composer.local.json composer preflight
```

- [ ] Expected: all pass. If Prettier fails on existing manifests, run `npx prettier --write 'packages/**/capell.json'`, inspect the diff carefully, and commit formatting separately only if the diff is broad.

### Task 2.5: Commit

- [ ] Commit:

```bash
git add .github/workflows/code-quality-and-styling.yml .github/workflows/security.yml .github/workflows/screenshots.yml composer.json composer.local.json package.json package-lock.json
git commit -m "ci: enforce manifest and security drift checks"
```

---

## Plan 3: PHPStan Baseline Burn-Down

**Goal:** Reduce baseline debt from 8513 by removing ignored errors in high-risk package groups first, while keeping the baseline-growth guard active.

**Risk Order:**

1. Public routes and public output: `api`, `agent-delivery`, `frontend-authoring`, `site-discovery`, `search`, `newsletter`, `knowledge-base`, `events`, `bookings`, `public-actions`.
2. Payments and webhooks: `payments`, `shopify-commerce`, `form-builder`, `public-actions`.
3. Privacy and security: `privacy-center`, `insights`, `login-audit`, `password-policy`, `contacts`, `customer-portal`.
4. Cache and publishing: `html-cache`, `publishing-studio`, `frontend-optimizer`, `layout-builder`, `content-sections`.

**Files:**

- Modify: package files reported by PHPStan.
- Modify: `phpstan/level-9-baseline.neon`
- Modify: `phpstan/preflight-baseline.neon`
- Test: package-local Pest files for touched package behavior.

### Task 3.1: Measure And Slice

- [ ] Run:

```bash
COMPOSER=composer.local.json composer analyze
bash scripts/check-phpstan-baseline-growth.sh
```

- [ ] Expected: PHPStan passes through the current baseline. The growth check prints `current=8513` or a lower current value.
- [ ] Create a scratch list of ignored baseline entries for the first risk group:

```bash
rg -n "packages/(api|agent-delivery|frontend-authoring|site-discovery|search|newsletter|knowledge-base|events|bookings|public-actions)/" phpstan/*baseline*.neon
```

### Task 3.2: Fix One Package At A Time

- [ ] Pick one package from the first risk group.
- [ ] Remove only that package's relevant ignore entries from `phpstan/level-9-baseline.neon` or `phpstan/preflight-baseline.neon`.
- [ ] Run:

```bash
COMPOSER=composer.local.json composer analyze
```

- [ ] Fix the now-visible errors in package code with the smallest behavior-preserving change.
- [ ] Prefer explicit types, typed DTO casts, `Collection<int, T>` phpdoc where needed, and narrowing `mixed` at boundaries.
- [ ] Do not silence errors with new baseline entries unless the error is a false positive caused by framework magic and cannot be expressed with an existing stub.

### Task 3.3: Test Touched Behavior

- [ ] Run the package-local tests for the package just touched:

```bash
vendor/bin/pest packages/<package>/tests --configuration=phpunit.xml
COMPOSER=composer.local.json composer analyze
bash scripts/check-phpstan-baseline-growth.sh
```

- [ ] Expected: package tests pass, PHPStan passes, and baseline debt is lower than before the slice.

### Task 3.4: Commit Each Package Slice

- [ ] Commit one package at a time:

```bash
git add packages/<package> phpstan/level-9-baseline.neon phpstan/preflight-baseline.neon
git commit -m "fix: reduce phpstan baseline for <package>"
```

### Task 3.5: Stop Rule

- [ ] Stop a burn-down branch after 5 to 10 percent debt reduction or after one coherent package group, whichever comes first.
- [ ] Run:

```bash
COMPOSER=composer.local.json composer preflight
```

- [ ] Expected: preflight passes before the last commit in the branch.

---

## Plan 4: Standardized Theme Manifest Contracts

**Goal:** Replace per-theme drift with shared assertions for theme parent metadata, screenshot contracts, provider definitions, and public Blade safety.

**Files:**

- Create: `tests/Packages/Support/ThemeManifestAssertions.php`
- Modify: `tests/Packages/ManifestTruthTest.php`
- Modify: `tests/Packages/Arch/ThemePublicBladeSafetyTest.php`
- Modify: `packages/foundation-theme/tests/Unit/ThemePackageManifestTest.php`
- Modify: `packages/theme-*/tests/Unit/ManifestRequirementsTest.php`
- Test: `tests/Packages/ManifestTruthTest.php`, `tests/Packages/Arch/ThemePublicBladeSafetyTest.php`, and touched theme manifest tests.

### Task 4.1: Extract Theme Manifest Assertions

- [ ] Create `tests/Packages/Support/ThemeManifestAssertions.php` with strictly typed functions:

```php
<?php

declare(strict_types=1);

use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;

/**
 * @param array<string, mixed> $manifest
 * @param array<string, array<string, mixed>> $manifestsByName
 * @return list<string>
 */
function capell_theme_manifest_contract_issues(string $manifestPath, array $manifest, array $manifestsByName): array
{
    $issues = [];

    if (($manifest['kind'] ?? null) !== 'theme') {
        return $issues;
    }

    $themeKey = $manifest['themeKey'] ?? null;
    $extends = $manifest['extends'] ?? null;

    if (! is_string($themeKey) || $themeKey === '') {
        $issues[] = 'theme manifests must declare themeKey';
    }

    if (! is_string($extends) || $extends === '') {
        $issues[] = 'theme manifests must declare extends';
    }

    foreach (capell_theme_manifest_provider_classes($manifest) as $providerClass) {
        if (! method_exists($providerClass, 'definition')) {
            continue;
        }

        $definition = $providerClass::definition();

        if (! $definition instanceof ThemeDefinitionData) {
            continue;
        }

        $resolvedExtends = capell_theme_manifest_resolved_extends($extends, $manifestsByName);

        if ($definition->extends !== $resolvedExtends) {
            $issues[] = sprintf(
                '%s provider extends [%s] but manifest extends [%s] resolves to [%s]',
                $providerClass,
                $definition->extends ?? 'null',
                is_string($extends) ? $extends : 'null',
                $resolvedExtends ?? 'null',
            );
        }
    }

    return $issues;
}
```

- [ ] Move existing helper logic from `tests/Packages/ManifestTruthTest.php` into this support file without changing behavior.

### Task 4.2: Use The Shared Helper

- [ ] Add `require_once __DIR__ . '/Support/ThemeManifestAssertions.php';` to `tests/Packages/ManifestTruthTest.php`.
- [ ] Replace the inline theme `extends` test body with calls to `capell_theme_manifest_contract_issues()`.
- [ ] Update per-theme `ManifestRequirementsTest.php` files to use the same helper for package-local assertions where they currently duplicate `extends` expectations.

### Task 4.3: Add Contract Coverage

- [ ] Add fixture coverage in `tests/Packages/ManifestTruthTest.php` proving a theme manifest that extends `capell-app/foundation-theme` resolves to the foundation theme key `default`.
- [ ] Add fixture coverage proving a provider returning a different parent key fails with a clear message.

### Task 4.4: Verify And Commit

- [ ] Run:

```bash
vendor/bin/pest tests/Packages/ManifestTruthTest.php tests/Packages/Arch/ThemePublicBladeSafetyTest.php --configuration=phpunit.xml
vendor/bin/pest packages/foundation-theme/tests/Unit/ThemePackageManifestTest.php --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```

- [ ] Expected: all pass.
- [ ] Commit:

```bash
git add tests/Packages/Support/ThemeManifestAssertions.php tests/Packages/ManifestTruthTest.php tests/Packages/Arch/ThemePublicBladeSafetyTest.php packages/foundation-theme/tests/Unit/ThemePackageManifestTest.php packages/theme-*/tests/Unit/ManifestRequirementsTest.php
git commit -m "test: centralize theme manifest contracts"
```

---

## Plan 5: Browser-Level Public Safety QA

**Goal:** Prove anonymous output for public-route packages does not expose admin/editor markers, signed editor URLs, package internals, model IDs, or authoring selectors.

**Initial Package Set:** `frontend-authoring`, `api`, `agent-delivery`, `public-actions`, `newsletter`, `events`, `bookings`, `knowledge-base`, `search`, `site-discovery`, `customer-portal`, `privacy-center`.

**Files:**

- Create: `tests/Packages/Support/PublicRouteSafetyAssertions.php`
- Create: `tests/Packages/Feature/PublicRouteSafetyQaTest.php`
- Modify: package tests only when a package needs a seedable public route fixture.
- Test: `tests/Packages/Feature/PublicRouteSafetyQaTest.php`

### Task 5.1: Add Shared Leak Scanner

- [ ] Create `tests/Packages/Support/PublicRouteSafetyAssertions.php` with forbidden markers:

```php
<?php

declare(strict_types=1);

const CAPELL_PUBLIC_ROUTE_FORBIDDEN_MARKERS = [
    'authoring/regions',
    'CapellFrontendAuthoring',
    'capell-frontend-authoring',
    'data-capell-authoring',
    'capell-authoring',
    'signed-editor',
    'signed_editor',
    'signed editor',
    'edit_url',
    'recordKey',
    'model_id',
    'modelId',
    'field_path',
    'fieldPath',
    'filament-peek',
    'x-capell-editor',
];

/**
 * @return list<string>
 */
function capell_public_response_leaks(string $body): array
{
    $leaks = [];

    foreach (CAPELL_PUBLIC_ROUTE_FORBIDDEN_MARKERS as $marker) {
        if (str_contains($body, $marker)) {
            $leaks[] = $marker;
        }
    }

    return $leaks;
}
```

### Task 5.2: Add Route-Driven Pest Flow

- [ ] Create `tests/Packages/Feature/PublicRouteSafetyQaTest.php`.
- [ ] Require `tests/Packages/Support/PublicRouteSafetyAssertions.php`.
- [ ] Use manifest route metadata from `capell.json` security public surface where present.
- [ ] For each route, make an anonymous GET request only when the route method is safe and seed data exists.
- [ ] Assert:

```php
expect(capell_public_response_leaks($response->getContent()))->toBe([]);
```

- [ ] Treat expected redirects, 401s, 403s, and 404s as allowed only if the route is tokenized, signed, authenticated, or fixture data is absent. Do not mark a route safe from an exception page.

### Task 5.3: Seed First Public Routes

- [ ] Start with packages that already have public route tests:
  - `packages/newsletter/tests/Feature/PreferenceCenterPublicOutputSafetyTest.php`
  - `packages/events/tests/Feature/PublicEventViewDataTest.php`
  - `packages/bookings/tests/Feature/PublicBookingRequestTest.php`
  - `packages/knowledge-base/tests/Feature/Frontend/KnowledgeBasePublicRoutesTest.php`
  - `packages/agent-delivery/tests/Feature/Http/PageManifestControllerTest.php`
- [ ] Reuse existing factories and setup methods from those tests. Do not create new package-specific factories in the shared test.

### Task 5.4: Verify And Commit

- [ ] Run:

```bash
vendor/bin/pest tests/Packages/Feature/PublicRouteSafetyQaTest.php --configuration=phpunit.xml
vendor/bin/pest packages/newsletter/tests/Feature/PreferenceCenterPublicOutputSafetyTest.php packages/events/tests/Feature/PublicEventViewDataTest.php packages/bookings/tests/Feature/PublicBookingRequestTest.php packages/knowledge-base/tests/Feature/Frontend/KnowledgeBasePublicRoutesTest.php --configuration=phpunit.xml
COMPOSER=composer.local.json composer preflight
```

- [ ] Expected: all pass.
- [ ] Commit:

```bash
git add tests/Packages/Support/PublicRouteSafetyAssertions.php tests/Packages/Feature/PublicRouteSafetyQaTest.php packages/newsletter/tests packages/events/tests packages/bookings/tests packages/knowledge-base/tests
git commit -m "test: add public route safety qa"
```

---

## Plan 6: Package Health Check Coverage

**Goal:** Make health checks consistently verify provider registration, manifest validity, migrations/settings, public route posture, and admin permission coverage where applicable.

**Files:**

- Create: `tests/Packages/Support/PackageHealthCheckAssertions.php`
- Create: `tests/Packages/Feature/PackageHealthCheckContractTest.php`
- Modify: `tests/Packages/Arch/PackageSettingsFeatureHealthTest.php`
- Modify: `tests/Packages/Feature/AdminSurfaceContractCoverageTest.php`
- Modify: package `src/Health/*HealthCheck.php` files only when the new shared contract exposes missing behavior.
- Test: `tests/Packages/Feature/PackageHealthCheckContractTest.php`

### Task 6.1: Define Shared Health Expectations

- [ ] Create `tests/Packages/Support/PackageHealthCheckAssertions.php` with functions that inspect a decoded manifest and return issue strings:
  - `capell_health_provider_registration_issues()`
  - `capell_health_manifest_validity_issues()`
  - `capell_health_migration_and_settings_issues()`
  - `capell_health_public_route_posture_issues()`
  - `capell_health_admin_permission_issues()`

- [ ] Reuse existing helpers from `scripts/audit-manifest-v3.php` and `scripts/audit-package-security.php` by requiring those scripts rather than reimplementing parsing.

### Task 6.2: Add Repo-Level Contract Test

- [ ] Create `tests/Packages/Feature/PackageHealthCheckContractTest.php`.
- [ ] For every `packages/*/capell.json`, assert:
  - providers declared in `providers.*` resolve to classes.
  - `php scripts/audit-manifest-v3.php` has no issue for the package.
  - packages with `database.migrations: true` have migration files or an explicit deferred contribution.
  - packages with public routes declare route names, and their manifest `security.publicSurface` values match the route chains discovered by `scripts/audit-package-security.php`.
  - packages with admin surfaces declare permissions, policies, or panel-auth coverage.

### Task 6.3: Upgrade Package Health Checks Incrementally

- [ ] Start with the same high-risk group as PHPStan: public routes, webhooks, payments, privacy, cache, publishing.
- [ ] For each package, add assertions to its `src/Health/*HealthCheck.php` by composing existing package Actions where possible.
- [ ] Add or update package-local health tests, for example:

```bash
vendor/bin/pest packages/payments/tests/Unit/PaymentsHealthReportTest.php packages/payments/tests/Feature --configuration=phpunit.xml
```

### Task 6.4: Verify And Commit

- [ ] Run:

```bash
vendor/bin/pest tests/Packages/Feature/PackageHealthCheckContractTest.php tests/Packages/Arch/PackageSettingsFeatureHealthTest.php tests/Packages/Feature/AdminSurfaceContractCoverageTest.php --configuration=phpunit.xml
php scripts/audit-manifest-v3.php
php scripts/audit-package-security.php
COMPOSER=composer.local.json composer preflight
```

- [ ] Expected: all pass.
- [ ] Commit:

```bash
git add tests/Packages/Support/PackageHealthCheckAssertions.php tests/Packages/Feature/PackageHealthCheckContractTest.php tests/Packages/Arch/PackageSettingsFeatureHealthTest.php tests/Packages/Feature/AdminSurfaceContractCoverageTest.php packages/*/src/Health packages/*/tests
git commit -m "test: standardize package health coverage"
```

---

## Plan 7: Site-Owner Package Security Surface Report

**Goal:** Generate an operator-facing report listing public routes, webhook routes, throttled routes, signed/tokenized routes, sensitive fields, cache safety, and security posture per package.

**Files:**

- Create: `scripts/generate-package-security-surface-report.php`
- Create: `docs/package-security-surface.md`
- Create: `tests/Packages/Security/PackageSecuritySurfaceReportTest.php`
- Modify: `composer.json`
- Modify: `composer.local.json`
- Modify: `docs/README.md`
- Test: `tests/Packages/Security/PackageSecuritySurfaceReportTest.php`

### Task 7.1: Add Report Builder

- [ ] Create `scripts/generate-package-security-surface-report.php`.
- [ ] Require `scripts/audit-package-security.php`.
- [ ] For every package manifest, output a Markdown table with columns:
  - Package
  - Risk tier
  - Public routes
  - Webhook routes
  - Throttled routes
  - Signed/tokenized routes
  - Sensitive fields
  - Cache safety
  - Admin authorization
- [ ] Sort packages alphabetically.
- [ ] Keep report generation deterministic.

### Task 7.2: Add Composer Script

- [ ] Add to both Composer manifests:

```json
"security:surface-report": "@php scripts/generate-package-security-surface-report.php",
"security:surface-report:check": "@php scripts/generate-package-security-surface-report.php --check"
```

### Task 7.3: Add Report Test

- [ ] Create `tests/Packages/Security/PackageSecuritySurfaceReportTest.php`.
- [ ] Assert the generated report contains:
  - a top-level heading `# Package Security Surface Report`
  - table headers for every required column
  - known high-risk packages: `payments`, `public-actions`, `privacy-center`, `frontend-authoring`
  - no absolute local filesystem paths

### Task 7.4: Generate And Link Docs

- [ ] Run:

```bash
COMPOSER=composer.local.json composer security:surface-report
```

- [ ] Link `docs/package-security-surface.md` from `docs/README.md`.

### Task 7.5: Verify And Commit

- [ ] Run:

```bash
vendor/bin/pest tests/Packages/Security/PackageSecuritySurfaceReportTest.php tests/Packages/Security/PackageSecurityContractTest.php --configuration=phpunit.xml
COMPOSER=composer.local.json composer security:surface-report
COMPOSER=composer.local.json composer preflight
```

- [ ] Expected: tests pass, report regenerates with no unexpected diff after the second run.
- [ ] Commit:

```bash
git add scripts/generate-package-security-surface-report.php docs/package-security-surface.md docs/README.md tests/Packages/Security/PackageSecuritySurfaceReportTest.php composer.json composer.local.json
git commit -m "docs: add package security surface report"
```

---

## Final Review Checklist

- [ ] Screenshot fixture policy prevents normal test runs from dirtying `tests/Packages/Fixtures/theme-demo-layout-screenshots`.
- [ ] CI runs package security audit, manifest v3 audit, screenshot manifest validation, and `capell.json` Prettier checks.
- [ ] PHPStan baseline debt is lower than the starting 8513 and cannot grow on pull requests.
- [ ] Theme manifest contract assertions live in one shared helper.
- [ ] Public-route packages have anonymous leak checks for authoring/editor markers.
- [ ] Package health checks consistently cover provider, manifest, migration/settings, route, and admin permission posture.
- [ ] Site-owner security report is generated, tested, and linked from docs.
- [ ] Each slice is committed separately and verified with the narrowest meaningful tests plus `COMPOSER=composer.local.json composer preflight`.
