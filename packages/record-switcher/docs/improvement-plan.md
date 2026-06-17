# Record Switcher - Improvement & Growth Plan

> Package: capell-app/record-switcher · Kind: package · Tier: free · Product group: Capell Foundation · Bundle: foundation · Status: Active

## 1. Snapshot

Record Switcher is a small admin-only productivity package for Filament edit pages. It tags `RecordSwitcherHeadingExtender` into Capell Admin's `EditRecordHeadingExtender` extension point, replaces edit-page headings with a Livewire `RecordSwitcher` component, and loads first-party CSS/Alpine assets for searchable heading suggestions. It has no migrations, settings, commands, public routes, or permissions. The Livewire component builds options from the current Filament resource query, prioritizing recently updated generic records and, for Pages, same-parent siblings before same-site and other-site records. Special Page handling loads site, URL, and optional ancestry data so editors can distinguish records without returning to table views. The marketplace already promotes runner-backed light/dark admin screenshots for the heading switcher workflow.

## 2. Improvements (existing functionality)

1. **Align Composer dependencies with manifest/runtime imports.** `capell.json` declares `capell-app/core`, but `composer.json` only requires `capell-app/admin`. The provider extends `AbstractPackageServiceProvider`, the health check imports Core diagnostics data/contracts, and manifest tests import the Core validator, so Core should be explicit in Composer too. Evidence: `composer.json`, `capell.json dependencies.requires`, `src/Providers/RecordSwitcherServiceProvider.php`, `src/Health/RecordSwitcherHealthCheck.php`. - **S** - **Done 2026-06-14:** `composer.json` now explicitly requires `capell-app/core`.

2. **Make the health check prove registration, not just file existence.** `RecordSwitcherHealthCheck` checks class existence and asset files, but not whether the extender is actually tagged, the Livewire namespace is registered, or Filament asset registration can resolve the package handles. Add focused checks that match the package's admin contract. Evidence: `src/Health/RecordSwitcherHealthCheck.php`, `src/Providers/RecordSwitcherServiceProvider.php`. - **S** - **Done 2026-06-14:** diagnostics now assert the Capell Admin extender tag, Livewire namespace resolution, and Filament CSS/Alpine asset handles.

3. **Replace generic generated docs copy.** README and overview still say "admins get package-owned management or reporting surfaces inside Capell" and "document extension points here if..." even though this package contributes a heading extension, not a management/reporting surface. Rewrite the docs around the edit-page switcher workflow, no-schema install impact, supported resources, and troubleshooting for missing assets or empty suggestions. Evidence: `README.md`, `docs/overview.md`. - **S** - **Done 2026-06-14:** README, overview, and docs index now describe the edit-page heading workflow and troubleshooting paths.

4. **Add query-budget and resource edge-case coverage.** Manifest `adminQueryBudget` is 10, but tests only covered a simple Page option label. Coverage now verifies page hierarchy eager loading stays inside the manifest budget, generic resources search declared attributes, empty searchable attributes do not break loading, and package-owned test fixtures stay PSR-4. Evidence: `src/Livewire/RecordSwitcher.php`, `tests/Unit/RecordSwitcherOptionsTest.php`, `tests/Fixtures/*`, `capell.json performance.adminQueryBudget`. - **M** - **Done 2026-06-15**

5. **Make the heading replacement opt-out friendly.** `RecordSwitcherHeadingExtender::supports()` now respects a resource-level `recordSwitcherEnabled(): bool` convention, and the README/overview documents when custom resources should return `false`. Evidence: `src/Filament/RecordSwitcherHeadingExtender.php`, `tests/Unit/RecordSwitcherOptionsTest.php`, `README.md`, `docs/overview.md`. - **M** - **Done 2026-06-15**

6. **Declare diagnostics in manifest contributions.** The package already shipped `RecordSwitcherHealthCheck`, but `contributes[]` only exposed the admin asset. The manifest now includes a `health-check` contribution backed by a marker class and manifest test. Evidence: `capell.json`, `src/Manifest/RecordSwitcherHealthContribution.php`, `tests/Unit/ManifestRequirementsTest.php`. - **S** - **Done 2026-06-15**

## 3. Missing Features (gaps)

Capabilities declared: admin record switching, searchable edit-page navigation, Tab-to-accept, Filament heading switcher, and first-party admin assets.

- **No per-resource configuration.** There is no package config for excluded resources, custom labels, result limits, or search attributes. The package leans entirely on Filament resource defaults.
- **Done/Shipped: recent, sibling, and same-site prioritization.** Generic resource suggestions now sort recently updated records first before falling back to key order. Page suggestions prioritize same-parent siblings, then records on the same site, then other-site pages, while preserving the existing bounded query and enriched Page labels. — `src/Livewire/RecordSwitcher.php`, `tests/Unit/RecordSwitcherOptionsTest.php`
- **Keyboard behavior is present in the JS asset but not covered by a browser test.** Unit tests do not prove Tab-to-accept, focus behavior, loading states, or dark-mode styling in a real browser.
- **No accessibility verification.** The custom Choices/heading UI should be checked for label announcement, focus trap behavior, Escape handling, and touch target size.

## 4. Issues / Risks

1. **Important gap: package can install without an explicit Core composer dependency.** Admin likely pulls Core transitively in normal Capell apps, but split-package consumers and validators should not rely on transitive dependencies for imported Core contracts. Recommended fix: add `capell-app/core` to package Composer metadata and the root overlays if required. - **P2**

2. **Important gap: health can pass while the admin extension is not actually wired.** Existing diagnostics prove asset files exist but not Capell Admin tagging or Livewire registration. Recommended fix: assert tagged extender, Livewire component namespace, and Filament asset handles. - **P2**

3. **Improvement: generic docs weaken buyer/operator confidence.** The package is small and useful, but generated copy makes it sound like an admin resource package. Recommended fix: rewrite docs to the actual workflow and failure modes. - **P3**

4. **Done/Shipped: unbounded resource support can surprise custom resources.** Resources can now opt out with `recordSwitcherEnabled(): false`, and docs call out that seam for unusual headings or sensitive workflows. - **P3**

## 5. Marketplace & Positioning

Record Switcher belongs in the free Foundation bundle because it improves every admin workflow without adding domain state. For admin users, the outcome is fewer table round-trips while editing pages, layouts, navigation records, and other Capell resources. For developers, the value is a first-party heading extender and assets that avoid app-level Filament customization.

**Current summary:** "Jump between editable Capell records directly from the page heading with fast suggestions and Tab-to-accept keyboard flow."

**Improved summary:** "Move between editable Capell records from the page heading, with searchable suggestions that keep editors out of table round-trips."

**Improved description:** "Record Switcher adds a compact searchable switcher to Filament edit-page headings, so editors can jump to sibling pages or related records while staying in their current workflow. It uses Capell Admin's heading extension point, Livewire, and first-party admin assets instead of app-level resource overrides. Page records include site, URL, and hierarchy context to reduce wrong-record edits. Built as a foundation package for repeated admin productivity across Capell installs."

**Media status:** Marketplace media is stronger than many no-plan packages: both light and dark runner screenshots are committed and promoted. Keep screenshot tests in place while improving docs.

**Keywords/tags:** `record-switcher`, `admin-productivity`, `filament`, `edit-pages`, `keyboard-navigation`, `searchable-navigation`, `livewire`, `foundation`, `editor-workflow`.

## 6. Prioritized Roadmap

| Item                                                                                        | Bucket | Effort | Impact | Section ref |
| ------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Add explicit `capell-app/core` Composer dependency and keep overlays aligned if needed      | Done   | S      | High   | §2.1, §4.1  |
| Extend health checks to verify extender tag, Livewire namespace, and Filament asset handles | Done   | S      | Medium | §2.2, §4.2  |
| Rewrite README and overview around the real heading-switcher workflow                       | Done   | S      | Medium | §2.3, §5    |
| Add query-budget and resource edge-case coverage for option loading                         | Done   | M      | Medium | §2.4        |
| Add a documented opt-out/config seam for unsupported resources                              | Done   | M      | Low    | §2.5, §4.4  |
| Declare health-check contribution metadata                                                  | Done   | S      | Medium | §2.6        |
| Add browser-level keyboard/accessibility coverage for Tab/Escape/focus behavior             | Later  | M      | Medium | §3          |
| Done/Shipped: add recency/sibling/same-site ordering as an editor productivity enhancement  | Done   | M      | Medium | §3          |

## 7. Verification

Focused package verification passed:

```bash
vendor/bin/pest packages/record-switcher/tests --configuration=phpunit.xml
```

Result: 9 tests, 47 assertions passed.

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for provider, health check, Livewire option loading, docs, and manifest.
- [x] Capell audience pass completed for admin editors and package adopters.
- [x] Approved implementation slices shipped.
- [x] Focused Record Switcher verification passed.
- [x] Package tests passed.
- [x] Repo preflight passed for changed files.
