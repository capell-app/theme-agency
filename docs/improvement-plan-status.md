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
| campaign-studio | 6 | 5 | 5 | Slice committed | Latest Campaign Studio slice adds campaign hero UTM fields, decorates hero CTA URLs through `BuildCampaignUrlAction`, updates screenshots/docs, and adds public render coverage; continue with CTA/page-view capture, full-page public safety tests, cache/personalisation strategy, variant publish filtering, scheduling, and experiment result readout. |
| comments | 8 | 5 | 3 | Slice committed | Latest Comments slice adds public form bot-trap controls, rejects honeypot/too-fast submissions before persistence, updates docs/tests; continue with real health check, spam scoring, throttle key hardening, moderator notifications, auto-inject leakage tests, reply pagination, marketplace screenshots, and retention/erasure work. |
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
| hero | 6 | 6 | 4 | Slice committed | Current follow-up aligns admin dependency/surface metadata, declares hero capabilities, removes provider dead code, and updates docs/tests; continue with screenshots, cache safety, render-budget, CTA, and accessibility rows. |
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
