# Improvement Plan Status

> Last refreshed: 2026-06-04. This is the control document for the long-running package improvement work. The per-package source of truth remains `packages/<package>/docs/improvement-plan.md`; this file tracks implementation progress and the next audit queue.

## Current Scope

The current repository contains 55 package improvement plans. Their roadmap rows currently total:

| Bucket | Rows |
| ------ | ---: |
| Now    | 292 |
| Next   | 314 |
| Later  | 202 |

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
| campaign-studio | 3 | 4 | 5 | Slice committed | Current follow-up filters campaign landing-page variant resolution to linked pages passing Capell's public publish-date scope, after the prior slice added same-origin CTA/page-view conversion beacon capture, URL-scoped CTA goal attribution, post-load tracker injection, full-page public no-leak coverage, and non-cacheable UTM-varying frontend contribution metadata. Continue with conversion-rate join hardening, scheduling automation, A/B result readout, revenue/ROAS, audience targeting, attribution-window config, experiment decoupling, and completion review. |
| comments | 1 | 0 | 0 | Slice committed | Latest Comments slice adds public Like reactions, aggregate public reaction counts, approved-reply author notifications, and tokenized reply-notification opt-out handling; continue with marketplace screenshot capture/wiring. |
| contacts | 0 | 7 | 2 | Slice committed | Current follow-ups expose audited privacy export/anonymization and queue/isolate all source-adapter listeners. Contacts has no Now rows left, but still needs Next/Later CRM depth work and a full package completion review before it can be marked Complete. |
| content-sections | 5 | 6 | 4 | Slice committed | `54b758fe1` landed readiness fixes; continue with remaining render safety/content rows. |
| customer-portal | 0 | 2 | 3 | Slice committed | Current follow-up adds same-site frontend account-isolation coverage, blocks suspended/archived accounts, adds package factories for portal accounts/support requests, covers unauthenticated/throttled frontend paths, wires `portal-profile` through an Action/rendered dashboard section, adds support request events/requester notifications, replaces placeholder frontend performance budgets, adds the package README, and schema-drives portal preferences. Continue with provider fan-out, marketplace screenshots/copy, Later rows, and a package completion review before marking Complete. |
| dashboard-reports | 1 | 4 | 3 | Slice committed | Current follow-up adds real translated Diagnostics checks, content-health filtered deep links, page-table filter extender registration, date-range reuse, scoped scheduled totals, render/gating coverage, manifest/docs copy, and the dashboard group label fix. Continue with screenshot captures, configurable stale-day threshold, missing-actor scoping, CSV export, scheduled digest, report registry, range-aware bucketing, theme-token chart colours, and completion review. |
| demo-kit | 5 | 6 | 4 | Slice committed | `aa2d02c4c` fixed seed fanout; continue with demo coverage and safety rows. |
| deployments | 0 | 4 | 3 | Slice committed | Current follow-up gates/registers the dashboard widget, requires real repository coordinates before OAuth, surfaces OAuth client misconfiguration, documents the `PublishesComposerChanges` consumer boundary, and fails loudly when the default publisher sees multiple active connections. Continue with OAuth token refresh, install policy UI, publish history/status, idempotent PR dedupe, rollback/cancel, health-gated deploy hooks/events, and provider webhooks. |
| diagnostics | 0 | 4 | 4 | Slice committed | Current follow-up adds implemented/stub/broken health-check reflection, runnable extension health checks, key-addressable Diagnostics assertions, explicit command-palette risk mapping, output redaction before audit persistence, and the `capell:diagnostics:health` doctor command. Continue with severity rollup, marketplace screenshot/copy wiring, expensive scan caching/snapshots, persisted health history, alerting, env/system info, JSON/CSV export, metadata cleanup, and completion review. |
| document-lifecycle | 1 | 4 | 4 | Slice committed | Current follow-up adds Action-backed admin registration plus archive/restore lifecycle actions, while the prior slice added factories, removed the unbacked public frontend surface, tightened acceptance mass assignment, and covered legacy rollback. Continue with screenshot captures, publish-version and acceptance admin actions, exports, re-acceptance, retention/expiry, signed evidence, content diffs, and completion review. |
| email-studio | 6 | 5 | 4 | Slice committed | `8cacf75e1` landed health/copy fixes; continue with retry/backoff and delivery gaps. |
| events | 6 | 6 | 4 | Slice committed | Current follow-up queues event registration notifications after commit and defers registration notification scheduling until the registration transaction commits. Continue with waitlist reconcile, doctor command wiring, screenshots, marketplace copy, portal cancellation, and completion review. |
| experiments | 5 | 6 | 5 | Slice committed | `cf3798724` landed health/subject fixes; continue with frontend allocation and significance rows. |
| filament-peek | 7 | 7 | 4 | Slice committed | `1e0ef3735` landed health/preview safety tests; continue with dependency/runtime edge rows. |
| form-builder | 5 | 6 | 4 | Slice committed | `f59eca815` landed health/spam safety fixes; admin UI and file/payment fields remain high value. |
| foundation-theme | 6 | 6 | 4 | Slice committed | `978ed62e8` landed health/manifest consistency; continue with WCAG, dark mode, and token rows. |
| frontend-authoring | 7 | 6 | 3 | Slice committed | `f329dd1e2` landed health/signer tests; continue with gate and publishing integration rows. |
| frontend-optimizer | 3 | 6 | 4 | Slice committed | Current follow-up adds listener/settings coverage for critical-CSS opt-out capture and settings normalization, plus a guard so frontend context listeners only resolve blueprints on compatible page models. Continue with screenshot capture, cache invalidation extension points, hot-path safety, and completion review. |
| ga4-reports | 6 | 7 | 5 | Slice committed | Current follow-up adds GA4 token/Data API retry-backoff, Retry-After handling, and explicit quota-exhaustion messaging; continue with cache/read-budget, screenshots, operator sync actions, and command convention rows. |
| hero | 6 | 6 | 4 | Slice committed | Current follow-up aligns admin dependency/surface metadata, declares hero capabilities, removes provider dead code, and updates docs/tests; continue with screenshots, cache safety, render-budget, CTA, and accessibility rows. |
| html-cache | 7 | 5 | 4 | Slice committed | Current follow-up adds config-driven path/cookie bypass rules that prevent configured public or personalized requests from reading or writing shared HTML cache entries, including direct `PageCache` writes and eligibility diagnostics. CDN purge, SWR, telemetry, screenshot reconciliation, and completion review remain. |
| insights | 6 | 6 | 4 | Slice committed | `5afe307aa` landed privacy hardening; continue with analytics/product gaps. |
| knowledge-base | 6 | 7 | 3 | Slice committed | Current follow-up throttles/dedupes anonymous feedback and updates shipped-feature docs; policies and edit/versioning gaps remain high priority. |
| layout-builder | 7 | 5 | 4 | Slice committed | Current follow-up removes dead cache enum, reconciles screenshot manifests, and documents remaining render-budget risk. |
| login-audit | 0 | 5 | 2 | Slice committed | Current follow-up adds real auth-event capture coverage, syncs vendor retention purge execution, records purge success timestamps, adds translated capture-configuration health diagnostics, and moves admin/user last-seen writes behind shared throttled Actions. Continue with remaining Next/Later operational rows and completion review. |
| media-ai | 6 | 6 | 3 | Slice committed | `f4b35bc06` landed health/allow-list fixes; production image-doctor/provider story remains. |
| media-library | 7 | 4 | 3 | Slice committed | Current follow-up keeps private Curator media off Glide/public conversion URLs, uses temporary disk URLs when supported, returns no URL when private disks cannot sign, and suppresses responsive srcsets for private media. Continue with cleanup UI, duplicate detection, config/owner FK depth, and completion review. |
| migration-assistant | 7 | 5 | 3 | Slice committed | `3f14f3f77` landed readiness fixes; continue with end-to-end import rows. |
| navigation | 6 | 5 | 4 | Slice committed | Current follow-up deepens translated `NavigationHealthCheck` diagnostics with main-menu coverage and orphaned page-reference integrity checks. Continue with resolver/render-boundary rows, manifest metadata, screenshots, marketplace copy, and completion review. |
| newsletter | 6 | 6 | 4 | Slice committed | `d934f6d59` landed token hardening; delivery engine and campaign rows remain. |
| notes | 6 | 7 | 3 | Slice committed | `0a153655b` landed health/body validation; reminders and inbox workflow remain. |
| password-policy | 7 | 6 | 4 | Slice committed | `71c4e91e2` fixed expiry safety; continue with policy UX/report rows. |
| payments | 7 | 6 | 3 | Slice committed | `1f16e9fd7` landed webhook/checkout hardening; continue with async and provider gaps. |
| privacy-center | 7 | 5 | 4 | Slice committed | `c45d46877` wired retention command; DSAR/consent UI and cross-package privacy rows remain. |
| public-actions | 5 | 7 | 5 | Slice committed | `aee93d196` landed webhook safety fixes; durable fanout/replay/retention rows remain. |
| publishing-studio | 5 | 6 | 5 | Slice committed | `c6a886556` landed health/screenshots; continue with publishing workflow rows. |
| search | 7 | 6 | 4 | Slice committed | `73eed65c8` landed Wave 7 readiness; continue with hot-path and analytics rows. |
| seo-suite | 6 | 6 | 3 | Slice committed | Current follow-up extracts shared public-output leak scanning, expands doctor/crawler-preview leak detection, and redacts signed sitemap diagnostics. Continue with anonymous/non-admin AI Discovery endpoint safety tests, dormant SEO check wiring, health probes, and completion review. |
| shopify-commerce | 6 | 7 | 4 | Slice committed | Current follow-up scrubs persisted sync errors; sync loop, customer producer, and webhook reachability remain high value. |
| site-discovery | 6 | 5 | 4 | Slice committed | `c76a0cc14` merged URL registry work; continue with remaining discovery/SEO rows. |
| structured-content-library | 7 | 5 | 4 | Slice committed | `73eed65c8` landed Wave 7 readiness; current follow-up hardens public payload sanitization, publish timestamps, and import slug dedup. |
| tags | 6 | 5 | 4 | Slice committed | Latest Tags slice binds admin tag type selection to `TagTypeEnum`, removes factory type drift, declares taxonomy capabilities/cache tag, and updates docs/tests; continue with workspace ownership, status visibility, cache invalidation sources, and policy/deletion tests. |
| theme-agency | 6 | 6 | 3 | Slice committed | Latest Agency slice binds the page shell to surface/foreground tokens, gives all presets explicit surface tokens, replaces fixed section gradients with `site-brand-gradient`, and updates docs/tests; continue with screenshot deployment captures, tier/bundle alignment, preview assets, navigation/proof a11y, and richer agency-specific renderers. |
| theme-commerce | 6 | 5 | 4 | Slice committed | Latest Commerce slice translates hero/catalog retail copy, makes hero trust badges data-driven, removes the theme-specific catalog carousel selector, and updates docs/tests; continue with screenshot captures, cacheSafety, token replacement, PDP/cart/promo/review surfaces, LCP hints, and broader section render tests. |
| theme-corporate | 7 | 5 | 3 | Slice committed | Latest Corporate slice replaces placeholder hero stats with translated/data-driven stats, translates proof/content-listing UI labels, removes the theme-specific proof carousel selector, and updates docs/tests; continue with commercial tier conflict, richer screenshot capture set, token/card/dark-mode work, JS extraction, and corporate-specific sections. |
| theme-education | 6 | 7 | 2 | Slice committed | Latest Education slice moves course catalogue, event, and instructor default copy into translations, removes dead course carousel controls, hardens manifest tests, and updates docs/tests; continue with screenshot captures, data-driven course/events content, token colours, preview asset path verification, WCAG, and conversion sections. |
| theme-healthcare | 6 | 7 | 4 | Slice committed | Latest Healthcare slice wires the public skip-link target, adds image loading/dimension/fetch-priority attributes, translates visible event carousel controls, hardens manifest tests, and updates docs/tests; continue with route-backed screenshots, booking/Form Builder integration, token colours, inline event script extraction, empty states, locations detail, WCAG contrast, and clinical conversion sections. |
| theme-knowledge | 7 | 6 | 3 | Slice committed | Latest Knowledge slice moves author and topic hub defaults into translations, makes those sections data-driven, removes optional package checks from public Blade, hardens manifest tests, and updates docs/tests; continue with doc/article layout, functional search form, route-backed screenshots, code/prose styling, tokens, dark mode, reduced motion, and article feedback/versioning. |
| theme-local-services | 6 | 8 | 2 | Slice committed | Latest Local Services slice translates/data-drives service-area cards, adds the contact anchor, removes dead area links, hardens manifest tests, and updates docs/tests; continue with quote-form fallback, contact click-to-call/address/map, screenshots, LocalBusiness schema, hero LCP/alt, literal translation sweep, token/radius work, and full section/render-budget coverage. |
| theme-nonprofit | 6 | 7 | 4 | Slice committed | Latest Nonprofit slice fixes the public skip-link target, translates the events label, removes public Blade package introspection, and updates docs/tests; continue with screenshots, heading hierarchy, donation/payment flow, volunteer/event/story connected branches, token colours, hero LCP/progressbar, data-driven campaigns/stories/contact, and render-budget coverage. |
| theme-portfolio | 6 | 7 | 3 | Slice committed | Latest Portfolio slice translates newsletter chrome, removes inert `action="#"` fallback forms, supports hydrated capture actions, and updates docs/tests; continue with screenshots, product-group drift, data-driven testimonials/media-kit/contact, creator-lane about/bio, image LCP/alt, reduced motion, token colours, dark preset, and render-budget coverage. |
| theme-saas | 6 | 6 | 3 | Slice committed | Latest SaaS slice wires Content Sections, Document Lifecycle, and Form Builder availability into pricing/docs/demo-request guidance with branch tests and docs; continue with rendered anonymous leak tests, cacheSafety reconciliation, adapter translation, real pricing matrix, demo/trial form embed, route-backed screenshots, jargon copy, token colours, dark mode, and interactive calculator work. |
| translation-manager | 6 | 5 | 5 | Slice committed | `56038d0db` landed import/export/readiness work; continue with placeholder validation and MT review. |
| url-manager | 5 | 7 | 4 | Slice committed | `f060bcb3e` landed verification improvements; continue with opportunity/decorator reachability. |
| welcome-tour | 6 | 5 | 5 | Slice committed | `ce8f38f2d` landed improvements; continue with host-column/runtime edge rows. |
| wordpress-importer | 7 | 6 | 3 | Slice committed | `747584bb4` landed importer improvements; continue with field mapping and execution gaps. |

## Next Audit Queue

No package rows are currently marked `Needs audit`. Highest-value remaining work should continue from the explicit follow-up notes in the `Slice committed` rows:

1. Theme-line systemic pass: route-backed screenshots, token-driven colors, WCAG/dark mode, LCP/image handling, and render-budget coverage.
2. Product-depth pass: real connected integrations, data-driven section content, and stronger vertical differentiators for each premium theme.

After each package slice lands, update this tracker with the commit and any rows that remain high-risk.
