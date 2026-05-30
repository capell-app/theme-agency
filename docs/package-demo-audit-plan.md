# Package Demo Audit Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Audit every package in isolation, capture the exact admin/frontend surfaces each package adds, document screenshots and use cases, and produce feature suggestions per package.

**Architecture:** Use one fresh accessible Capell demo app as the reproducible harness, reset it between packages, and install only Capell core plus the package under audit and its declared hard dependencies. Each package is handled sequentially inside its batch so screenshots, menus, docs, and feature suggestions are attributable to that package instead of to previously installed packages.

**Tech Stack:** Capell 4, Laravel, Filament, Composer path repositories, Docker or local PHP 8.4 runtime, Playwright/browser screenshot capture, Pest for package-level verification.

---

## Operating Rules

- Work in `/Users/ben/Sites/packages/capell/capell-packages-4`.
- Do not use `php artisan` in this package repo. Inside the separate demo app, use artisan only when the app install flow requires it.
- Use `$write-developer-docs` for every package documentation pass: inspect providers, routes, Filament resources/pages/widgets, Actions, config, migrations, tests, README, and existing package docs before changing prose.
- Use `$humanizer` before finalizing package docs: remove generic generated prose, keep the writing specific to the package, and cut claims that are not backed by code or tests.
- Keep the demo app outside this repo, at `/Users/ben/Sites/packages/capell/capell-package-demo-audit`.
- Use core Capell packages as the baseline: `capell-app/core`, `capell-app/admin`, `capell-app/frontend`, `capell-app/installer`, and `capell-app/marketplace` when required by the app install.
- For each audit run, install the target package plus only the hard dependencies declared by that package's `capell.json`. Do not install optional dependencies unless the package surface being documented explicitly requires them; if an optional dependency is used, record it in the package audit notes.
- Before capturing screenshots, compare the current admin menu against the core baseline. Any menu item not added by the target package or its hard dependencies must be removed from the demo app before capture.
- Every Filament page, resource index, create/edit/view form, relation manager, widget, custom action modal, settings page, dashboard panel, and custom control added by the target package needs a screenshot entry.
- Every public frontend route, Blade render path, widget, injected render hook, theme view, beacon behavior, cache behavior, and public action added by the target package needs a screenshot entry and a short use case.
- Manifest entries are not enough for the final pass. Each required entry must have a captured image file or an explicit blocker with the command, URL, and missing setup that prevented capture.
- Keep screenshots minimal: seed only enough data to show the package's contribution clearly.
- For packages with no visible frontend, document that explicitly in `docs/overview.md` and in the package audit notes.
- Anonymous and non-admin public screenshots must not expose authoring markers, package internals, model IDs, editor URLs, or admin-only labels.

## Deliverables Per Package

Each package audit produces or updates:

- `packages/{package}/README.md`: accurate install summary, what the package adds, and links to docs.
- `packages/{package}/docs/overview.md`: install flow, demo setup, admin surfaces, frontend surfaces, use cases, screenshot list, known risks, and feature suggestions.
- `packages/{package}/docs/screenshots.json`: complete screenshot contract for all admin and frontend surfaces.
- `docs/package-screenshot-manifest.json`: regenerated aggregate manifest from package screenshot manifests.
- `docs/package-testing-audit.md`: package checklist result, verification commands, and audit notes.
- `docs/internal/package-feature-suggestions.md`: package-by-package feature suggestions ranked by value and implementation risk.
- Screenshot image files in the demo/docs output path: `public/docs/screenshots/packages/{package}` in the demo app or generated docs deployment.

## Batch Ownership

Dispatch one worker per batch. Each worker must process its assigned packages one by one, resetting the demo app between packages.

- **Agent 1:** `access-gate`, `address`, `agent-bridge`, `ai-orchestrator`, `api`, `block-library`, `blog`, `campaign-studio`
- **Agent 2:** `content-sections`, `dashboard-reports`, `demo-kit`, `deployments`, `diagnostics`, `document-lifecycle`, `email-studio`, `events`
- **Agent 3:** `form-builder`, `foundation-theme`, `frontend-authoring`, `frontend-optimizer`, `ga4-reports`, `hero`, `html-cache`, `insights`
- **Agent 4:** `layout-builder`, `login-audit`, `media-ai`, `media-library`, `migration-assistant`, `navigation`, `newsletter`, `notes`
- **Agent 5:** `password-policy`, `public-actions`, `publishing-studio`, `search`, `seo-suite`, `site-discovery`, `tags`, `theme-agency`
- **Agent 6:** `theme-corporate`, `theme-saas`, `translation-manager`, `welcome-tour`, `wordpress-importer`

## Task 1: Create The Fresh Demo Harness

**Files:**

- Create: `/Users/ben/Sites/packages/capell/capell-package-demo-audit/`
- Create: `docs/package-demo-audit-harness.md`

- [ ] **Step 1: Inspect Capell app bootstrap instructions**

Run:

```bash
sed -n '1,220p' ../capell-4/README.md
sed -n '1,220p' ../capell-4/composer.json
```

Expected: identify the exact Capell app install command and any required environment variables.

- [ ] **Step 2: Create a brand new demo app**

Use the app bootstrap command found in Step 1. The target directory must be:

```bash
/Users/ben/Sites/packages/capell/capell-package-demo-audit
```

Expected: the app boots with Capell core/admin/frontend only, before any optional package under audit is installed.

- [ ] **Step 3: Configure local package path repositories**

Inside `/Users/ben/Sites/packages/capell/capell-package-demo-audit/composer.json`, add path repositories for:

```json
[
    {
        "type": "path",
        "url": "../capell-packages-4/packages/*",
        "options": {
            "symlink": true
        }
    },
    {
        "type": "path",
        "url": "../capell-4/packages/*",
        "options": {
            "symlink": true
        }
    }
]
```

Expected: `composer require capell-app/{package}:4.x-dev` resolves local package code.

- [ ] **Step 4: Make the app accessible**

Run the app through Docker or the simplest available local PHP runtime. The app must be reachable in a browser at one stable URL, for example:

```text
https://capell-package-demo-audit.test
```

Expected: admin and frontend URLs load in a browser and can be captured by Playwright or the in-app browser.

- [ ] **Step 5: Document the harness**

Create `docs/package-demo-audit-harness.md` with:

```markdown
# Package Demo Audit Harness

Demo app path: `/Users/ben/Sites/packages/capell/capell-package-demo-audit`
Browser URL: `https://capell-package-demo-audit.test`
PHP runtime: Docker or documented equivalent
Core baseline packages: `capell-app/core`, `capell-app/admin`, `capell-app/frontend`, `capell-app/installer`, `capell-app/marketplace`

## Reset Command

Document the exact command sequence used to return the app to core baseline before installing the next package.

## Admin Account

Document the local admin email and role used for screenshots.

## Screenshot Output

Screenshots are written to `public/docs/screenshots/packages/{package}`.
```

## Task 2: Capture The Core Baseline

**Files:**

- Create: `docs/internal/core-demo-baseline.md`
- Create: `docs/internal/core-admin-menu-baseline.json`

- [ ] **Step 1: Install only the core baseline**

Reset the demo app and install only the baseline Capell app packages.

Expected: no package from `/Users/ben/Sites/packages/capell/capell-packages-4/packages` is installed.

- [ ] **Step 2: Capture baseline admin menu**

Export the Filament navigation labels, resource/page classes, route names, and URLs into `docs/internal/core-admin-menu-baseline.json`.

Expected shape:

```json
{
    "capturedAt": "2026-05-19",
    "appUrl": "https://capell-package-demo-audit.test",
    "items": [
        {
            "label": "Dashboard",
            "class": "Capell\\Admin\\...",
            "routeName": "filament.admin.pages.dashboard",
            "url": "/admin"
        }
    ]
}
```

- [ ] **Step 3: Capture baseline public frontend**

Create `docs/internal/core-demo-baseline.md` with the home page URL, admin dashboard URL, and a short list of core-only screens.

Expected: later package audits can diff against this baseline and avoid documenting unrelated menu items.

## Task 3: Add The Package Audit Template

**Files:**

- Create: `docs/internal/package-audit-template.md`
- Create or modify: `docs/internal/package-feature-suggestions.md`

- [ ] **Step 1: Create the per-package audit template**

Create `docs/internal/package-audit-template.md`:

```markdown
# {Package Name} Audit

Package: `{package}`
Composer name: `{composerName}`
Hard dependencies installed: `{hardDependencies}`
Optional dependencies installed: `{optionalDependencies}`
Demo URL: `{demoUrl}`

## Install Verification

- Composer require command:
- Migration/setup command:
- Package discovery result:
- Package tests:

## Admin Surfaces Added

| Surface | URL | Screenshot | Notes |
| ------- | --- | ---------- | ----- |

## Frontend Surfaces Added

| Surface | URL | Screenshot | Use case |
| ------- | --- | ---------- | -------- |

## Menu Diff

List only menu entries added by this package or its hard dependencies.

## Screenshot Coverage

Confirm every Filament page, resource, form, widget, action modal, relation manager, settings page, frontend route, render hook, and public Blade output has a screenshot entry.

## Public Safety Checks

Confirm anonymous and non-admin frontend output exposes no authoring/admin surface.

## Feature Suggestions

| Suggestion | User value | Implementation risk | Package fit |
| ---------- | ---------- | ------------------- | ----------- |

## Risks And Follow-Ups

Record broken UI, missing docs, missing screenshot coverage, package conflicts, or suspicious dependencies.
```

- [ ] **Step 2: Create feature suggestions index**

Create `docs/internal/package-feature-suggestions.md`:

```markdown
# Package Feature Suggestions

Suggestions are collected during isolated package audits. Each suggestion must be tied to a real observed package surface and ranked by user value and implementation risk.

| Package | Suggestion | User value | Implementation risk | Notes |
| ------- | ---------- | ---------- | ------------------- | ----- |
```

## Task 4: Package Audit Loop

**Files:**

- Modify: `packages/{package}/README.md`
- Modify or create: `packages/{package}/docs/overview.md`
- Modify or create: `packages/{package}/docs/screenshots.json`
- Modify: `docs/package-testing-audit.md`
- Modify: `docs/internal/package-feature-suggestions.md`

Repeat these steps for each package in the assigned batch.

- [ ] **Step 1: Read package metadata**

Run:

```bash
sed -n '1,220p' packages/{package}/composer.json
sed -n '1,220p' packages/{package}/capell.json
find packages/{package}/src/Filament -type f | sort
find packages/{package}/resources/views -type f | sort
find packages/{package}/routes -type f | sort
```

Expected: identify composer name, hard dependencies, optional dependencies, admin surfaces, frontend surfaces, and setup commands.

- [ ] **Step 2: Reset demo app**

In `/Users/ben/Sites/packages/capell/capell-package-demo-audit`, run the reset sequence documented in `docs/package-demo-audit-harness.md`.

Expected: app returns to the core baseline before installing the next package.

- [ ] **Step 3: Install target package and hard dependencies**

Install the target package with its `capell.json` hard dependencies. Use Composer path repositories so package code comes from this repo.

Expected: `composer show 'capell-app/*'` lists core baseline packages, hard dependencies, and the single target package. It must not list unrelated optional packages.

- [ ] **Step 4: Run package setup**

Run migrations, seeders, install commands, and demo commands listed in the package README or `docs/overview.md`.

Expected: the package has minimal demo content for screenshots and no unrelated package data.

- [ ] **Step 5: Run package tests from this repo**

Run the narrowest package test suite:

```bash
vendor/bin/pest packages/{package}/tests --configuration=phpunit.xml
```

Expected: package tests pass, or failures are recorded in `docs/package-testing-audit.md` with exact failing test names.

- [ ] **Step 6: Capture menu diff**

Compare the current Filament admin menu to `docs/internal/core-admin-menu-baseline.json`.

Expected: the diff contains only the target package and hard-dependency surfaces. If another package appears, remove it from the demo app and repeat the capture.

- [ ] **Step 7: Capture admin screenshots**

For every added Filament surface, capture desktop screenshots. Include mobile screenshots when the surface is responsive, dense, or likely to collapse awkwardly.

Required admin captures:

- Resource index table
- Create form
- Edit form
- View/infolist page, when present
- Relation managers, when present
- Custom action modals
- Custom widgets
- Dashboard panels
- Settings pages
- Custom Filament pages
- Empty state when it is materially different from the populated state

Expected: screenshots are saved under `public/docs/screenshots/packages/{package}` and referenced from `packages/{package}/docs/screenshots.json`.

- [ ] **Step 8: Capture frontend screenshots and use cases**

For every public route, render hook, theme view, widget, public action, or Blade output, capture screenshots and write a use case in `packages/{package}/docs/overview.md`.

Expected: each frontend screenshot answers: who uses this, what they are trying to do, and what package behavior is visible.

- [ ] **Step 9: Check public safety**

View frontend pages as anonymous and as a normal non-admin user.

Expected: no authoring HTML, authoring JavaScript, editable markers, model IDs, field paths, labels, package internals, permissions, selectors, or signed editor URLs appear in public HTML.

- [ ] **Step 10: Update package docs**

Update `README.md` and `docs/overview.md` so they match the real install and demo flow.

Expected: docs contain no claims that were not verified in the isolated demo app.

- [ ] **Step 11: Update screenshot manifest**

Create or update `packages/{package}/docs/screenshots.json` with entries for every required screenshot.

Expected manifest shape:

```json
{
    "package": "{package}",
    "composerName": "capell-app/{composer-name}",
    "composerRequires": ["capell-app/{hard-dependency}"],
    "outputDirectory": "public/docs/screenshots/packages/{package}",
    "entries": [
        {
            "id": "{stable-screenshot-id}",
            "title": "{Human readable title}",
            "surface": "admin",
            "targetType": "admin-surface",
            "target": "{Filament resource or page class}",
            "required": true,
            "docsPage": "packages/{package}/docs/overview.md",
            "output": "{stable-screenshot-id}.png"
        }
    ],
    "browserTests": []
}
```

- [ ] **Step 12: Record feature suggestions**

Add at least three feature suggestions to `docs/internal/package-feature-suggestions.md` unless the package is intentionally tiny. Suggestions must come from observed package behavior, not imagination detached from the UI.

Expected: each suggestion includes user value, implementation risk, and why it belongs in this package.

- [ ] **Step 13: Update package testing audit**

Update `docs/package-testing-audit.md` with the package checklist result, commands run, screenshot status, and known risks.

Expected: package status is understandable without reopening the demo app.

## Task 5: Regenerate Aggregate Screenshot Manifest

**Files:**

- Create: `scripts/build-package-screenshot-manifest.js`
- Modify: `docs/package-screenshot-manifest.json`
- Modify: `package.json`

- [ ] **Step 1: Add the aggregate manifest builder**

Create `scripts/build-package-screenshot-manifest.js`. The script must read every `packages/*/docs/screenshots.json`, merge their `entries`, preserve package-level `browserTests`, and write deterministic pretty JSON to `docs/package-screenshot-manifest.json`.

Required output shape:

```json
{
    "generatedFor": "capell-docs-deployment",
    "source": "packages/*/docs/screenshots.json",
    "outputRoot": "public/docs/screenshots/packages",
    "requirements": [
        "Install the package under test.",
        "Composer require any package-level composerRequires before seeding demo data.",
        "Run package setup or demo commands listed in the package overview.",
        "Authenticate as an admin user with the required role or permission.",
        "Resolve admin-surface targets through Filament resource/page URLs when possible.",
        "Resolve frontend-url targets through seeded demo routes or package route names.",
        "Execute package-level browserTests when declared by the source screenshots manifest."
    ],
    "entries": [],
    "browserTests": []
}
```

Expected: aggregate manifest contains every screenshot entry and browser test from every audited package.

- [ ] **Step 2: Add package scripts**

Merge these scripts into the existing `package.json` scripts block:

```json
{
    "scripts": {
        "screenshots:manifest": "node scripts/build-package-screenshot-manifest.js",
        "screenshots:validate": "node scripts/validate-screenshot-manifests.js"
    }
}
```

Expected: `npm run screenshots:manifest` rebuilds the aggregate file and `npm run screenshots:validate` confirms it matches package manifests.

- [ ] **Step 3: Regenerate and validate**

Run:

```bash
npm run screenshots:manifest
npm run screenshots:validate
```

Expected: `docs/package-screenshot-manifest.json` is deterministic and validation prints `Screenshot manifests are in sync.`

- [ ] **Step 4: Validate package coverage**

Compare package list to packages with screenshot manifests:

```bash
find packages -maxdepth 2 -name composer.json -print | sort
find packages -maxdepth 3 -path '*/docs/screenshots.json' -print | sort
```

Expected: every package with a visible admin or frontend surface has a screenshot manifest. Packages with no visible surface are explicitly documented in their overview.

## Task 6: Cross-Package Conflict Pass

**Files:**

- Modify: `docs/package-testing-audit.md`
- Modify: affected package docs or manifests

- [ ] **Step 1: Review Composer and Capell manifests**

Run:

```bash
find packages -maxdepth 2 -name capell.json -print | sort
find packages -maxdepth 2 -name composer.json -print | sort
```

Expected: hard dependencies are declared in `capell.json`, optional dependencies are not installed during isolated screenshots unless explicitly needed, and conflicts are documented where needed.

- [ ] **Step 2: Review admin navigation collisions**

Use the captured menu diffs from every package.

Expected: no package audit screenshots include unrelated menu items from a previously installed package. If two packages intentionally share a navigation group, document the shared group and the package-specific items.

- [ ] **Step 3: Review frontend collisions**

Check package routes, view namespaces, asset names, render hooks, and public paths.

Expected: no screenshots or docs attribute another package's frontend output to the package under audit.

## Task 7: Final Verification

**Files:**

- Modify: `docs/package-testing-audit.md`
- Modify: `docs/internal/package-feature-suggestions.md`
- Modify: `docs/package-screenshot-manifest.json`

- [ ] **Step 1: Run focused package tests for changed packages**

For every package whose code or tests changed, run:

```bash
vendor/bin/pest packages/{package}/tests --configuration=phpunit.xml
```

Expected: tests pass or failures are documented with exact failing test names.

- [ ] **Step 2: Run documentation checks**

Run:

```bash
composer prettier
composer lint:changed
```

Expected: changed Markdown, JSON, Blade, PHP, CSS, and JS files are formatted.

- [ ] **Step 3: Spot-check screenshots**

Open at least one admin screenshot and one frontend screenshot from each package with visible surfaces.

Expected: screenshot is readable, minimal, seeded with useful data, and not hiding broken UI states.

- [ ] **Step 4: Produce final audit summary**

Update `docs/package-testing-audit.md` with:

- Packages completed
- Packages blocked
- Screenshot coverage gaps
- Public safety risks
- Menu conflict findings
- Top feature suggestions across all packages

Expected: Ben can decide which package improvements to prioritize without rerunning the whole audit.

## Current Repo State Notes

As of 2026-05-19, package composer files exist for:

`access-gate`, `address`, `agent-bridge`, `ai-orchestrator`, `api`, `block-library`, `blog`, `campaign-studio`, `content-sections`, `dashboard-reports`, `demo-kit`, `deployments`, `diagnostics`, `document-lifecycle`, `email-studio`, `events`, `form-builder`, `foundation-theme`, `frontend-authoring`, `frontend-optimizer`, `ga4-reports`, `hero`, `html-cache`, `insights`, `layout-builder`, `login-audit`, `media-ai`, `media-library`, `migration-assistant`, `navigation`, `newsletter`, `notes`, `password-policy`, `public-actions`, `publishing-studio`, `search`, `seo-suite`, `site-discovery`, `tags`, `theme-agency`, `theme-corporate`, `theme-saas`, `translation-manager`, `welcome-tour`, `wordpress-importer`.

Existing screenshot manifests are present for:

`address`, `agent-bridge`, `ai-orchestrator`, `blog`, `campaign-studio`, `deployments`, `diagnostics`, `form-builder`, `foundation-theme`, `frontend-authoring`, `insights`, `login-audit`, `media-ai`, `media-library`, `migration-assistant`, `navigation`, `publishing-studio`, `search`, `seo-suite`, `tags`, `theme-agency`, `theme-corporate`, `theme-saas`, `wordpress-importer`.

Existing overview docs are present for:

`address`, `agent-bridge`, `ai-orchestrator`, `blog`, `campaign-studio`, `dashboard-reports`, `demo-kit`, `deployments`, `diagnostics`, `email-studio`, `form-builder`, `foundation-theme`, `frontend-authoring`, `ga4-reports`, `insights`, `login-audit`, `media-ai`, `media-library`, `migration-assistant`, `navigation`, `password-policy`, `publishing-studio`, `search`, `seo-suite`, `tags`, `theme-agency`, `theme-corporate`, `theme-saas`, `translation-manager`, `welcome-tour`, `wordpress-importer`.

The first audit pass should prioritize packages missing screenshot manifests or overview docs, while still verifying existing manifests against the fresh isolated demo app.

## Task 8: Deep Documentation And Screenshot Pass

**Files:**

- Modify: `packages/{package}/README.md`
- Modify: `packages/{package}/docs/overview.md`
- Modify: `packages/{package}/docs/screenshots.json`
- Create or update: screenshot image files under `/Users/ben/Sites/packages/capell/capell-package-demo-audit/public/docs/screenshots/packages/{package}`
- Modify: `docs/package-testing-audit.md`
- Modify: `docs/internal/package-feature-suggestions.md`

Repeat these steps for each package. Do not batch packages so tightly that docs become generic.

- [ ] **Step 1: Read the package source before writing**

Inspect the package's providers, Filament classes, routes, views, Actions, config, migrations, tests, and existing docs:

```bash
find packages/{package}/src packages/{package}/resources packages/{package}/config packages/{package}/database packages/{package}/tests -maxdepth 5 -type f | sort
sed -n '1,220p' packages/{package}/README.md
test -f packages/{package}/docs/overview.md && sed -n '1,260p' packages/{package}/docs/overview.md
test -f packages/{package}/docs/screenshots.json && sed -n '1,260p' packages/{package}/docs/screenshots.json
```

Expected: the maintainer can point from every documented feature to a real class, route, config key, migration, test, or Blade view.

- [ ] **Step 2: Verify install in the Laravel 13 harness**

Install the target package and all hard dependencies in an isolated harness. For `login-audit`, the host app must use the Rappasoft Laravel 13 PR fork:

```bash
composer require 'rappasoft/laravel-authentication-log:dev-main as 6.0.1' capell-app/login-audit:4.x-dev -W
```

Expected: Composer resolves `rappasoft/laravel-authentication-log` from `https://github.com/fdemb/laravel-authentication-log` at PR #140 until upstream ships Laravel 13 support.

- [ ] **Step 3: Seed minimal screenshot data**

Create only the data needed to show the package's own screens. Prefer package demo/install commands where they exist. If no command exists, document the exact records created and why.

Expected: screenshots show useful package state without unrelated menu clutter or demo bloat.

- [ ] **Step 4: Capture every required screenshot**

For each required `docs/screenshots.json` entry, open the actual URL/surface and write the screenshot file declared by the manifest. Capture admin and frontend surfaces as separate images. Capture anonymous/non-admin frontend states where public safety matters.

Expected: every required manifest entry has an image file or a documented blocker in `docs/package-testing-audit.md`.

- [ ] **Step 5: Rewrite docs from the reader's next action**

Use `$write-developer-docs` standards. Package docs should say:

- what the package adds
- how to install it in a host app
- which admin and frontend surfaces appear
- which data/tables/settings/config keys it owns
- which commands/jobs/schedules/routes it registers
- how screenshots are seeded and captured
- how to verify package changes
- what can break if extension boundaries are changed carelessly

Expected: no package docs rely on broad claims such as "powerful", "seamless", "comprehensive", or "robust" without concrete code-backed detail.

- [ ] **Step 6: Run the humanizer pass**

Read the whole changed README and overview. Remove chatbot residue, inflated claims, repeated sentence shapes, and filler.

Expected: the docs sound like a maintainer wrote them after testing the package, not like a generated product summary.

- [ ] **Step 7: Verify**

Run:

```bash
vendor/bin/pest packages/{package}/tests --configuration=phpunit.xml
jq empty packages/{package}/docs/screenshots.json
npm run screenshots:manifest
npm run screenshots:validate
git diff --check -- packages/{package}/README.md packages/{package}/docs docs/package-testing-audit.md docs/internal/package-feature-suggestions.md
```

Expected: package tests pass, screenshot manifests stay in sync, and changed docs have no whitespace errors.
