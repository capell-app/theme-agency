# Improvement Plan Status

> Last refreshed: 2026-06-04. This is the control document for the long-running package improvement work. The per-package source of truth remains `packages/<package>/docs/improvement-plan.md`; this file tracks implementation progress and the next audit queue.

## Current Scope

The current repository contains 55 package improvement plans. Their roadmap rows currently total:

| Bucket | Rows |
| ------ | ---: |
| Now    | 351 |
| Next   | 336 |
| Later  | 205 |

The work is not complete until every package plan has been reviewed against current code, required features are implemented or intentionally deferred, new behavior is documented, and focused verification passes for each changed package.

## Status Rules

| Status | Meaning |
| ------ | ------- |
| Needs audit | No recent package-specific implementation slice has been identified in this run. Review the plan against current code before editing. |
| Slice committed | At least one plan-backed implementation slice has landed. This does not mean the package plan is complete. |
| Complete | Reserved for packages whose full plan has been checked row-by-row, with all required docs/tests/features complete or explicitly deferred. |

No package should be marked `Complete` from commit history alone. Completion requires a fresh `comprehensive-review` pass against the package plan and current code.

## Package Tracker

| Package | Now | Next | Later | Current status | Evidence / next action |
| ------- | --: | ---: | ----: | -------------- | ---------------------- |
| campaign-studio | 6 | 5 | 5 | Slice committed | `dd9bf35ea` landed a now-bucket slice; continue with remaining Next/Later rows. |
| comments | 8 | 5 | 3 | Slice committed | `8fe039e95` landed a now-bucket slice; continue with remaining plan rows. |
| contacts | 6 | 7 | 2 | Slice committed | `132778050` fixed stats scoping; continue with privacy/export/merge gaps. |
| content-sections | 5 | 6 | 4 | Slice committed | `54b758fe1` landed readiness fixes; continue with remaining render safety/content rows. |
| customer-portal | 6 | 7 | 3 | Slice committed | `6de22b6d4` landed a now-bucket slice; continue with cross-account and profile gaps. |
| dashboard-reports | 7 | 5 | 3 | Slice committed | `5779a66d6` landed a now-bucket slice; continue with budget/reporting gaps. |
| demo-kit | 5 | 6 | 4 | Slice committed | `aa2d02c4c` fixed seed fanout; continue with demo coverage and safety rows. |
| deployments | 6 | 7 | 3 | Slice committed | `8f5c95387` landed health/manifest fixes; continue with deploy history and rollback rows. |
| diagnostics | 5 | 6 | 4 | Slice committed | `86ebebd0a` improved readiness; platform health-check execution remains high priority. |
| document-lifecycle | 7 | 5 | 4 | Slice committed | `32dfee056` landed health/acceptance/migration fixes; continue with admin lifecycle and export rows. |
| email-studio | 6 | 5 | 4 | Slice committed | `8cacf75e1` landed health/copy fixes; continue with retry/backoff and delivery gaps. |
| events | 7 | 6 | 4 | Slice committed | Current follow-up wires registration confirm/cancel admin actions, waitlist promotion reachability, and manifest permissions; continue with mail queueing, screenshots, portal cancellation, and doctor command rows. |
| experiments | 5 | 6 | 5 | Slice committed | `cf3798724` landed health/subject fixes; continue with frontend allocation and significance rows. |
| filament-peek | 7 | 7 | 4 | Slice committed | `1e0ef3735` landed health/preview safety tests; continue with dependency/runtime edge rows. |
| form-builder | 5 | 6 | 4 | Slice committed | `f59eca815` landed health/spam safety fixes; admin UI and file/payment fields remain high value. |
| foundation-theme | 6 | 6 | 4 | Slice committed | `978ed62e8` landed health/manifest consistency; continue with WCAG, dark mode, and token rows. |
| frontend-authoring | 7 | 6 | 3 | Slice committed | `f329dd1e2` landed health/signer tests; continue with gate and publishing integration rows. |
| frontend-optimizer | 7 | 7 | 4 | Slice committed | `acc89aa2d` landed health/copy fixes; continue with cache invalidation and hot-path rows. |
| ga4-reports | 6 | 7 | 5 | Slice committed | Current follow-up adds GA4 token/Data API retry-backoff, Retry-After handling, and explicit quota-exhaustion messaging; continue with cache/read-budget, screenshots, operator sync actions, and command convention rows. |
| hero | 6 | 6 | 4 | Needs audit | No recent package slice identified; audit against plan before implementation. |
| html-cache | 7 | 6 | 4 | Slice committed | `b260cd847` fixed invalidation/header issues; CDN purge/SWR/telemetry remain. |
| insights | 6 | 6 | 4 | Slice committed | `5afe307aa` landed privacy hardening; continue with analytics/product gaps. |
| knowledge-base | 6 | 7 | 3 | Slice committed | Current follow-up throttles/dedupes anonymous feedback and updates shipped-feature docs; policies and edit/versioning gaps remain high priority. |
| layout-builder | 7 | 5 | 4 | Slice committed | Current follow-up removes dead cache enum, reconciles screenshot manifests, and documents remaining render-budget risk. |
| login-audit | 7 | 6 | 2 | Slice committed | `043626303` landed health/session fixes; continue with hot-path and retention rows. |
| media-ai | 6 | 6 | 3 | Slice committed | `f4b35bc06` landed health/allow-list fixes; production image-doctor/provider story remains. |
| media-library | 7 | 5 | 3 | Slice committed | `16207ad15` landed upload/health fixes; cleanup UI, duplicate detection, and private URL rows remain. |
| migration-assistant | 7 | 5 | 3 | Slice committed | `3f14f3f77` landed readiness fixes; continue with end-to-end import rows. |
| navigation | 7 | 5 | 4 | Slice committed | `8b7c1f8df` replaced JSON reverse lookup; resolver/render-boundary rows remain. |
| newsletter | 6 | 6 | 4 | Slice committed | `d934f6d59` landed token hardening; delivery engine and campaign rows remain. |
| notes | 6 | 7 | 3 | Slice committed | `0a153655b` landed health/body validation; reminders and inbox workflow remain. |
| password-policy | 7 | 6 | 4 | Slice committed | `71c4e91e2` fixed expiry safety; continue with policy UX/report rows. |
| payments | 7 | 6 | 3 | Slice committed | `1f16e9fd7` landed webhook/checkout hardening; continue with async and provider gaps. |
| privacy-center | 7 | 5 | 4 | Slice committed | `c45d46877` wired retention command; DSAR/consent UI and cross-package privacy rows remain. |
| public-actions | 5 | 7 | 5 | Slice committed | `aee93d196` landed webhook safety fixes; durable fanout/replay/retention rows remain. |
| publishing-studio | 5 | 6 | 5 | Slice committed | `c6a886556` landed health/screenshots; continue with publishing workflow rows. |
| search | 7 | 6 | 4 | Slice committed | `73eed65c8` landed Wave 7 readiness; continue with hot-path and analytics rows. |
| seo-suite | 7 | 6 | 3 | Slice committed | `e548d68ac` landed readiness fixes; continue with redaction and AI discovery rows. |
| shopify-commerce | 6 | 7 | 4 | Slice committed | Current follow-up scrubs persisted sync errors; sync loop, customer producer, and webhook reachability remain high value. |
| site-discovery | 6 | 5 | 4 | Slice committed | `c76a0cc14` merged URL registry work; continue with remaining discovery/SEO rows. |
| structured-content-library | 7 | 5 | 4 | Slice committed | `73eed65c8` landed Wave 7 readiness; current follow-up hardens public payload sanitization, publish timestamps, and import slug dedup. |
| tags | 6 | 5 | 4 | Needs audit | No recent package slice identified; taxonomy/status/workspace drift needs review. |
| theme-agency | 6 | 6 | 3 | Needs audit | Theme-line systemic work: extends alignment, tokens, screenshots, WCAG/dark mode. |
| theme-commerce | 6 | 5 | 4 | Needs audit | Theme-line systemic work plus commerce-specific real CTA/form behavior. |
| theme-corporate | 7 | 5 | 3 | Needs audit | Theme-line systemic work plus corporate-specific screenshots and WCAG checks. |
| theme-education | 6 | 7 | 2 | Needs audit | Theme-line systemic work plus education-specific conversion sections. |
| theme-healthcare | 6 | 7 | 4 | Needs audit | Theme-line systemic work plus healthcare booking/consent sections. |
| theme-knowledge | 7 | 6 | 3 | Needs audit | Theme-line systemic work plus docs/KB layout mismatch. |
| theme-local-services | 6 | 8 | 2 | Needs audit | Theme-line systemic work plus quote/contact section behavior. |
| theme-nonprofit | 6 | 7 | 4 | Needs audit | Theme-line systemic work plus donation/volunteer CTA behavior. |
| theme-portfolio | 6 | 7 | 3 | Needs audit | Theme-line systemic work plus portfolio/agency differentiation. |
| theme-saas | 6 | 6 | 3 | Needs audit | Theme-line systemic work plus pricing/demo request behavior. |
| translation-manager | 6 | 5 | 5 | Slice committed | `56038d0db` landed import/export/readiness work; continue with placeholder validation and MT review. |
| url-manager | 5 | 7 | 4 | Slice committed | `f060bcb3e` landed verification improvements; continue with opportunity/decorator reachability. |
| welcome-tour | 6 | 5 | 5 | Slice committed | `ce8f38f2d` landed improvements; continue with host-column/runtime edge rows. |
| wordpress-importer | 7 | 6 | 3 | Slice committed | `747584bb4` landed importer improvements; continue with field mapping and execution gaps. |

## Next Audit Queue

Highest-value remaining work should start with packages that are both high-risk and still marked `Needs audit`:

1. `hero` plan audit and implementation slice.
2. `tags` taxonomy/status/workspace drift.
3. Theme-line systemic pass: `extends` alignment, token-driven colors, screenshots, WCAG/dark mode.

After each package slice lands, update this tracker with the commit and any rows that remain high-risk.
