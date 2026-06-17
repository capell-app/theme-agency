# Improvement Plan Status

> Last refreshed: 2026-06-17. Generated from current package-local `docs/improvement-plan.md` files.

## Current Scope

The repository contains 81 package-local improvement plans. Current implementation-plan status is complete: every package plan has 0 `Now` rows, 0 `Next` rows, and no unchecked completion checklist items.

| Bucket  | Rows |
| ------- | ---: |
| Now     |    0 |
| Next    |    0 |
| Later   |  121 |
| Done    |  977 |
| Blocked |    1 |
| Other   |   58 |

`Later` and `Blocked` rows are intentionally deferred product-depth or runner-environment follow-ups in their package-local plans; they are not active implementation-plan blockers.

## Verification Evidence

- `vendor/bin/pest packages/access-gate/tests --configuration=phpunit.xml` passed: 182 tests, 881 assertions.
- `vendor/bin/pest packages/live-chat/tests packages/site-monitor/tests --configuration=phpunit.xml` passed: 85 tests, 530 assertions.
- `vendor/bin/pest packages/api/tests --configuration=phpunit.xml` passed: 35 tests, 241 assertions.
- `vendor/bin/pest packages/theme-liquid-glass/tests --configuration=phpunit.xml` passed: 27 tests, 229 assertions.
- `php scripts/audit-package-docs.php` passed for 81 packages with no warnings.
- `COMPOSER=composer.local.json composer preflight` passed on a clean worktree.

## Status Rules

| Status          | Meaning                                                                                                                                  |
| --------------- | ---------------------------------------------------------------------------------------------------------------------------------------- |
| Complete        | The package-local plan has no active Now/Next work, its checklist is complete, and remaining Later/Blocked rows are explicitly deferred. |
| Needs follow-up | The package-local plan still has active Now/Next work or unchecked checklist items.                                                      |

## Package Tracker

| Package                      | Now | Next | Later | Done | Blocked | Current status | Evidence                                                                                                                           |
| ---------------------------- | --: | ---: | ----: | ---: | ------: | -------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| access-gate                  |   0 |    0 |     2 |    8 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| address                      |   0 |    0 |     2 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| agent-bridge                 |   0 |    0 |     1 |    8 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| agent-delivery               |   0 |    0 |     2 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| ai-orchestrator              |   0 |    0 |     1 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| api                          |   0 |    0 |     2 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| automation-studio            |   0 |    0 |     2 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| block-library                |   0 |    0 |     0 |    9 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| blog                         |   0 |    0 |     2 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| bookings                     |   0 |    0 |     2 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| campaign-studio              |   0 |    0 |     0 |   10 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| comments                     |   0 |    0 |     0 |   15 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| contacts                     |   0 |    0 |     1 |    9 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| content-sections             |   0 |    0 |     2 |   14 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| customer-portal              |   0 |    0 |     0 |    0 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| dashboard-reports            |   0 |    0 |     0 |   15 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| demo-kit                     |   0 |    0 |     0 |   17 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| deployments                  |   0 |    0 |     1 |   16 |       1 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later; 1 Blocked row(s) remain intentionally deferred. |
| diagnostics                  |   0 |    0 |     3 |   12 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 3 Later row(s) remain intentionally deferred.            |
| document-lifecycle           |   0 |    0 |     0 |   16 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| email-studio                 |   0 |    0 |     0 |   10 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| equestrian-clinics           |   0 |    0 |     2 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| events                       |   0 |    0 |     3 |   16 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 3 Later row(s) remain intentionally deferred.            |
| exception-reports            |   0 |    0 |     2 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| experiments                  |   0 |    0 |     0 |   12 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| filament-peek                |   0 |    0 |     5 |   13 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 5 Later row(s) remain intentionally deferred.            |
| form-builder                 |   0 |    0 |     1 |   14 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| foundation-theme             |   0 |    0 |     0 |   16 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| frontend-authoring           |   0 |    0 |     3 |   13 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 3 Later row(s) remain intentionally deferred.            |
| frontend-optimizer           |   0 |    0 |     4 |   14 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 4 Later row(s) remain intentionally deferred.            |
| ga4-reports                  |   0 |    0 |     0 |   14 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| hero                         |   0 |    0 |     2 |   14 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| html-cache                   |   0 |    0 |     3 |   17 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 3 Later row(s) remain intentionally deferred.            |
| inertia                      |   0 |    0 |     1 |    5 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| inertia-react-adapter        |   0 |    0 |     1 |    5 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| inertia-vue-adapter          |   0 |    0 |     1 |    5 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| insights                     |   0 |    0 |     0 |   19 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| knowledge-base               |   0 |    0 |     0 |   17 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| layout-builder               |   0 |    0 |     5 |   11 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 5 Later row(s) remain intentionally deferred.            |
| live-chat                    |   0 |    0 |     4 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 4 Later row(s) remain intentionally deferred.            |
| login-audit                  |   0 |    0 |     0 |   17 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| media-ai                     |   0 |    0 |     0 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| media-library                |   0 |    0 |     0 |   18 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| migration-assistant          |   0 |    0 |     4 |   12 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 4 Later row(s) remain intentionally deferred.            |
| navigation                   |   0 |    0 |     1 |   17 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| newsletter                   |   0 |    0 |     3 |   14 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 3 Later row(s) remain intentionally deferred.            |
| notes                        |   0 |    0 |     4 |   13 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 4 Later row(s) remain intentionally deferred.            |
| password-policy              |   0 |    0 |     2 |   18 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| payments                     |   0 |    0 |     2 |   15 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| privacy-center               |   0 |    0 |     4 |   12 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 4 Later row(s) remain intentionally deferred.            |
| public-actions               |   0 |    0 |     2 |   18 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| publishing-studio            |   0 |    0 |     6 |   11 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 6 Later row(s) remain intentionally deferred.            |
| record-switcher              |   0 |    0 |     1 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| search                       |   0 |    0 |     0 |   20 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| seo-suite                    |   0 |    0 |     4 |   15 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 4 Later row(s) remain intentionally deferred.            |
| shopify-commerce             |   0 |    0 |     3 |   16 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 3 Later row(s) remain intentionally deferred.            |
| site-discovery               |   0 |    0 |     0 |   14 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| site-monitor                 |   0 |    0 |     2 |    9 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| social-feeds                 |   0 |    0 |     3 |    9 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 3 Later row(s) remain intentionally deferred.            |
| structured-content-library   |   0 |    0 |     5 |   10 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 5 Later row(s) remain intentionally deferred.            |
| tags                         |   0 |    0 |     2 |   16 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 2 Later row(s) remain intentionally deferred.            |
| theme-agency                 |   0 |    0 |     0 |   15 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| theme-commerce               |   0 |    0 |     0 |   16 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| theme-corporate              |   0 |    0 |     0 |   15 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| theme-education              |   0 |    0 |     0 |   15 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| theme-estate-agents          |   0 |    0 |     1 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| theme-healthcare             |   0 |    0 |     0 |   17 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| theme-inertia-bookings       |   0 |    0 |     1 |    3 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| theme-inertia-bookings-react |   0 |    0 |     3 |    6 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 3 Later row(s) remain intentionally deferred.            |
| theme-inertia-bookings-vue   |   0 |    0 |     3 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 3 Later row(s) remain intentionally deferred.            |
| theme-knowledge              |   0 |    0 |     0 |   16 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| theme-liquid-glass           |   0 |    0 |     1 |    7 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| theme-local-services         |   0 |    0 |     0 |   15 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| theme-nonprofit              |   0 |    0 |     0 |   13 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| theme-portfolio              |   0 |    0 |     0 |   15 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| theme-restaurant             |   0 |    0 |     1 |    9 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| theme-saas                   |   0 |    0 |     0 |   13 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| translation-manager          |   0 |    0 |     0 |   16 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. No deferred rows remain.                                 |
| url-manager                  |   0 |    0 |     1 |   17 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| welcome-tour                 |   0 |    0 |     1 |   16 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |
| wordpress-importer           |   0 |    0 |     1 |   17 |       0 | Complete       | Package-local plan has 0 Now rows, 0 Next rows, and a complete checklist. 1 Later row(s) remain intentionally deferred.            |

## Next Audit Queue

No package rows are currently marked `Needs follow-up`; no package-local plans are missing; no active Now/Next implementation-plan rows remain. Future work should start from package-local `Later` or explicitly `Blocked` rows only when those deferred product-depth or runner-environment items are promoted into active scope.
