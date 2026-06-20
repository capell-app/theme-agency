# GA4 Reports — Improvement & Growth Plan

> Package: capell-app/ga4-reports · Kind: package · Tier: premium · Product group: Capell Growth · Bundle: growth · Status: Complete

## 1. Snapshot

GA4 Reports is a snapshot-based Google Analytics 4 reporting package: a scheduled/CLI sync (`SyncGA4ReportsMetricsAction` → `capell:ga4-reports-sync`, with `ga4-reports:sync` retained as an alias) pulls a date window from the GA4 Data API through a hand-rolled service-account client (`src/Support/Insights/GA4ReportsDataClient.php`, RS256 JWT auth, persisted cache-store token cache, paginated page reports) and persists into three local tables (`ga4_reports_daily_metrics`, `ga4_reports_page_metrics`, `ga4_reports_sync_runs`). Admin surfaces — one extension `GA4ReportsPage`, five widgets (overview stats, traffic trend, top pages, top-pages table, setup status), three dashboard overview stats, and a settings group — read only from local rows via `Build*Action` DTO builders, never hitting GA4 during render. Surfaces declared: `admin`, `console`; deps `capell-app/admin`, `capell-app/core` plus `lorisleiva/laravel-actions` + `spatie/laravel-data`. Current marketplace summary now leads with GA4 traffic, top-page, and conversion snapshots inside Capell admin. Manifest declares the extension card plus **3** committed admin PNG captures from `docs/screenshots.json`.

## Completed Improvement Slices

- **2026-06-04:** Added bounded retry/backoff configuration for GA4 token and Data API calls, including transient connection failures, GA4 quota exhaustion responses, `Retry-After`, and explicit final quota-exhaustion messaging.
- **2026-06-05:** Shipped the GA4 Reports cleanup slice: the service provider now falls back to `NullGA4ReportsDataClient` until enabled/property/credentials settings are complete, the stray "GA4 Reports 4" copy is removed from composer/README/lang/command text, and the orphan settings page is absent because settings are managed through the registered `ga4_reports` settings surface. Evidence: `tests/Feature/Package/GA4ReportsPackageTest.php` covers null/real client binding; `src/Providers/AdminServiceProvider.php` registers `GA4ReportsPage` plus the settings management surface only; focused typo checks cover `composer.json`, `README.md`, `resources/lang/en/package.php`, and `src/Console/Commands/SyncGA4ReportsCommand.php`.
- **2026-06-08:** Added digest/export Actions so local GA4 snapshot data can be assembled once and exported as CSV without hitting GA4 during admin/report rendering.
- **2026-06-14:** Surfaced stored `event_count` and weighted `average_session_duration` in the overview widget and CSV digest, added a shared credential-path readiness Action/Data object, wired it into settings validation, setup status, and health checks, and made package-local tests register GA4 model surfaces deterministically.

## 2. Improvements (existing functionality)

- **Done/Shipped 2026-06-14: surfaced `averageSessionDuration` and `eventCount`.** `BuildGA4ReportsOverviewAction` now returns `eventCount`, `GA4ReportsOverviewStatsFilamentWidget` renders Events and Avg. session duration rows with previous-window comparisons, and CSV digest export includes the events row. Evidence: focused Actions and Filament tests cover aggregate event count, overview widget output, and CSV export. — `src/Actions/BuildGA4ReportsOverviewAction.php`, `src/Data/GA4ReportsOverviewData.php`, `src/Filament/Widgets/GA4ReportsOverviewStatsFilamentWidget.php`, `src/Actions/ExportGA4ReportsDigestCsvAction.php`, `tests/Feature/Actions/GA4ReportsActionsTest.php`, `tests/Feature/Filament/GA4ReportsFilamentTest.php` — S
- **Shipped 2026-06-06: dashboard read aggregates are cached.** `BuildGA4ReportsOverviewAction`, `BuildGA4ReportsTrendAction`, and `BuildTopGA4ReportsPagesAction` now cache local aggregate DTOs under a short TTL keyed by `property_id`, date window, and top-page limit, with successful syncs invalidating the dashboard cache. `capell.json` now declares the `ga4-reports` cache tag and marks the admin aggregate reads cache-safe because they only render local metric snapshots. — `src/Support/GA4ReportsDashboardCache.php`, `src/Actions/BuildGA4ReportsOverviewAction.php`, `BuildGA4ReportsTrendAction.php`, `BuildTopGA4ReportsPagesAction.php`, `SyncGA4ReportsMetricsAction.php`, `capell.json` — M
- **Shipped 2026-06-06: overview stats use the dashboard default date range.** `AdminServiceProvider::ga4Overview()` now builds the same current-week dashboard window used by GA4 dashboard widgets before calling `BuildGA4ReportsOverviewAction`, so the three registered overview stats no longer fall back to the package `sync_days` window. Focused coverage seeds a large out-of-window metric and asserts the registered sessions stat only resolves current dashboard-window data. — `src/Providers/AdminServiceProvider.php`, `tests/Feature/Filament/GA4ReportsFilamentTest.php` — S
- **Shipped 2026-06-06: command follows the `capell:` convention.** `SyncGA4ReportsCommand` now exposes `capell:ga4-reports-sync`, keeps `ga4-reports:sync` as a backward-compatible alias, the schedule uses the Capell command name, and `capell.json.commands.doctor` points at the sync command for discoverability. — `src/Console/Commands/SyncGA4ReportsCommand.php`, `src/Providers/AdminServiceProvider.php`, `capell.json` — S
- **Shipped 2026-06-06: configurable sync schedule.** `registerSchedule()` now reads `GA4ReportsSettings::$sync_cron` with a config fallback and calls `->cron(...)` instead of hard-coded `->daily()`. The settings schema exposes a translated cron expression field and the package config defaults to `0 2 * * *`. — `src/Providers/AdminServiceProvider.php`, `src/Settings/GA4ReportsSettings.php`, `config/capell-ga4-reports.php` — S
- **Done/Shipped 2026-06-14: validate service-account credential readiness before sync.** `CheckGA4ReportsCredentialsPathAction` returns a typed status for missing, unreadable, invalid JSON, invalid service-account, and valid credentials. Settings validation now rejects bad paths when provided, setup status shows credential readiness without exposing the filesystem path, and health diagnostics reuse the same check. — `src/Actions/CheckGA4ReportsCredentialsPathAction.php`, `src/Data/GA4ReportsCredentialsStatusData.php`, `src/Filament/Settings/GA4ReportsSettingsSchema.php`, `src/Filament/Widgets/GA4ReportsSetupStatusFilamentWidget.php`, `src/Health/Ga4ReportsHealthCheck.php`, `tests/Feature/Filament/GA4ReportsFilamentTest.php` — M
- **Done/Shipped: add a "Sync now" action to the page/setup widget.** — `GA4ReportsPage` now exposes a translated `Sync now` header action that runs `SyncGA4ReportsMetricsAction` and shows the returned success/failure message in a Filament notification. — `src/Filament/Pages/GA4ReportsPage.php` — M
- **Done/Shipped: surface the last sync error to operators.** — `GA4ReportsSetupStatusFilamentWidget` now adds a translated `Last error` row when the latest sync run failed and has a stored error message, capped to a short admin-safe summary. — `src/Filament/Widgets/GA4ReportsSetupStatusFilamentWidget.php` — S

## 3. Missing Features (gaps)

Capabilities declared: `ga4-reports`, `ga4-reports-admin`, `ga4-reports-console`. Against GA4-integration norms:

- **API quota / 429 handling + retry-with-backoff shipped.** `GA4ReportsDataClient::runReport()` and `accessToken()` now retry transient connection failures, common upstream statuses, `429`, and GA4 `RESOURCE_EXHAUSTED` responses with bounded backoff and `Retry-After` support. Final quota failures now raise an explicit quota-exhaustion message. Keep the focused data-client tests as regression coverage. — `src/Support/Insights/GA4ReportsDataClient.php`, `tests/Unit/Support/GA4ReportsDataClientTest.php`
- **Shipped 2026-06-06: persisted / cross-process token cache.** `GA4ReportsDataClient` now caches OAuth access tokens in the configured cache store under a hashed service-account/scope key, honours `expires_in`, and keeps a 60-second refresh buffer so queue workers and CLI invocations avoid unnecessary JWT signing/token requests. — `src/Support/Insights/GA4ReportsDataClient.php`, `config/capell-ga4-reports.php`, `tests/Unit/Support/GA4ReportsDataClientTest.php`
- **Service-account is the only auth path.** Norm is to also support OAuth user consent (Search Console / GA4 connect-flow) so non-technical owners avoid hand-placing a JSON key file. The contract (`GA4ReportsDataClientInterface`) makes this swappable, but no OAuth client ships. Differentiator vs table-stakes.
- **Single property only.** `property_id` is a scalar setting; no multi-property selection or property picker, and the schema indexes already key on `property_id`, so multi-property is half-built. Multi-property is a clear premium differentiator for agencies/multi-site owners.
- **Shipped 2026-06-06: period-over-period widget comparisons.** The GA4 dashboard widgets now derive the immediately preceding window from the selected dashboard date range, show previous-period datasets on the traffic trend chart, add `Vs previous` deltas to overview rows, and compare top-page views against the matching previous-period page path. — `src/Filament/Widgets/Concerns/BuildsGA4ReportsDashboardWindow.php`, `src/Filament/Widgets/GA4ReportsTrafficTrendFilamentWidget.php`, `src/Filament/Widgets/GA4ReportsOverviewStatsFilamentWidget.php`, `src/Filament/Widgets/GA4ReportsTopPagesFilamentWidget.php`, `src/Filament/Widgets/GA4ReportsTopPagesTableFilamentWidget.php`
- **No realtime / named event breakdown widget.** Aggregate `eventCount` and conversions now appear in overview/digest surfaces, but there is still no events-by-name, conversions-by-event, or GA4 realtime card. Table stakes for deeper "GA4 reporting".
- **Shipped 2026-06-08: CSV digest/export seam.** `BuildGA4ReportsDigestAction` assembles overview, trend, and top-page local snapshot data; `ExportGA4ReportsDigestCsvAction` serializes that digest to CSV for admin commands, future dashboard downloads, or scheduled-report consumers. PDF output and scheduled email delivery remain future depth.
- **Dimensions are fixed.** Reports are hardcoded to `date` / `pagePathPlusQueryString` / `pageTitle`; no channel/source-medium, country, or device dimension. No configurable report builder. Differentiator if added.

## 4. Issues / Risks

- **Null-client binding shipped.** `GA4ReportsServiceProvider::bindGA4ReportsClient()` now binds `NullGA4ReportsDataClient` until enabled, property ID, and credentials path are all configured, keeping the README and data-client docs accurate. Regression evidence lives in `tests/Feature/Package/GA4ReportsPackageTest.php`. — `src/Support/Insights/NullGA4ReportsDataClient.php`, `src/Providers/GA4ReportsServiceProvider.php`
- **Documentation mismatch closed by null-client binding.** `docs/data-client.md` states "The package binds a real client when config is complete and **a null client when it is not**", and `README.md` lists "null-client behavior" as a value prop; the provider binding now matches those claims. — `docs/data-client.md`, `README.md`, `src/Providers/GA4ReportsServiceProvider.php`
- **Health check shipped.** `Ga4ReportsHealthCheck` now runs real Diagnostics probes for storage tables, model/schema availability, credential/settings readiness, latest successful sync freshness, and data-client/API read reachability where an enabled install has local credentials ready. Output intentionally avoids echoing the GA4 property ID, private key, credential JSON, or full credentials path. Evidence: `vendor/bin/pest packages/ga4-reports/tests/Feature/Health/GA4ReportsHealthCheckTest.php --configuration=phpunit.xml` (14 tests / 38 assertions). — `src/Health/Ga4ReportsHealthCheck.php`, `tests/Feature/Health/GA4ReportsHealthCheckTest.php`, `config/capell-ga4-reports.php`
- **Orphan settings page removed.** Settings are surfaced via `registerExtensionManagementSurface` + the `ga4_reports` settings group, and only `GA4ReportsPage` is registered as an extension page. No `GA4ReportsSettingsPage` file remains in the package. — `src/Providers/AdminServiceProvider.php`
- **Limited caching resilience remains (see §3)** — transient GA4 5xx/429 failures now retry before the sync run fails, and access tokens are cached cross-process in the configured cache store. The sync schedule is still daily by default. — `src/Support/Insights/GA4ReportsDataClient.php`, `src/Providers/AdminServiceProvider.php`
- **Bound client coverage added; real sync-path exception coverage remains.** `GA4ReportsDataClientTest` exercises `GA4ReportsDataClient` directly, and `tests/Feature/Package/GA4ReportsPackageTest.php` now asserts the container binds the null client when unconfigured and the real client when configured. `SyncGA4ReportsMetricsAction` still uses `FakeGA4ReportsDataClient` for lifecycle/error-path tests. — `tests/Feature/Actions/GA4ReportsActionsTest.php`, `tests/Feature/Package/GA4ReportsPackageTest.php`
- **Done/Shipped 2026-06-14: credential readiness is checked without echoing secrets.** The service-account JSON still lives at an operator-set filesystem path, but the settings field now validates provided paths through a shared Action, the setup widget reports readiness without rendering the path, and health checks reuse the same service-account JSON checks. Public-output safety remains not applicable because the package has no frontend routes. Future hardening can replace raw path entry with a host-managed secret/file picker. — `src/Actions/CheckGA4ReportsCredentialsPathAction.php`, `src/Filament/Settings/GA4ReportsSettingsSchema.php`, `src/Filament/Widgets/GA4ReportsSetupStatusFilamentWidget.php`, `src/Health/Ga4ReportsHealthCheck.php`
- **Performance cache metadata shipped.** `capell.json performance` now keeps `frontendRenderBudgetMs: 0` for the admin-only package, declares the `ga4-reports` cache tag, and marks local aggregate dashboard reads cache-safe by property/date range/limit with invalidation from sync/local metric persistence. — `capell.json`, `src/Support/GA4ReportsDashboardCache.php`
- **`commercial.privateDocsRequested: true` but `docs/` is public-style.** The README links siblings and renders public OpenGraph preview cards; confirm what is intended to be private vs marketplace-public. — `capell.json`, `README.md`
- **Done/Shipped: Branding typo "GA4 Reports 4".** The stray "4" copy has been removed from composer, README, language, and command-description surfaces; focused `rg` checks now find no shipped-code copy matches. — `composer.json`, `README.md`, `resources/lang/en/package.php`, `src/Console/Commands/SyncGA4ReportsCommand.php`
- **PHP floor lags house standard.** `composer.json` requires `php: ^8.3`; the capell convention targets PHP 8.4. Low risk (code is 8.3-compatible) but inconsistent with siblings. — `composer.json`
- **i18n.** Strings are translated via `capell-ga4-reports::` keys across `layout_widgets`, `settings`, `sync` (good). Gaps: `Ga4ReportsHealthCheck` and the `MarketingStudioActionData`/`ExtensionManagementSurfaceData` icons use raw strings (acceptable), but the README/docs typo and the persisted enum-like `status` strings (`running`/`succeeded`/`failed`) are raw literals in the model/sync action rather than a backed enum with labels — the capell convention prefers backed enums for persisted values. — `src/Actions/SyncGA4ReportsMetricsAction.php`, `src/Filament/Widgets/GA4ReportsSetupStatusFilamentWidget.php`

## 5. Marketplace & Selling

**Marketplace copy status:** manifest, Composer, README, and overview now lead with the concrete value: pulling Google Analytics 4 traffic, top-page, and conversion snapshots into Capell admin on a daily schedule without per-pageview API calls. The remaining buyer-facing gap is visual proof of the reporting surfaces.

**Improved 1-sentence summary:**

> Pull Google Analytics 4 traffic, top-page, and conversion snapshots into your Capell admin on a daily schedule — no per-pageview API calls, no leaving the CMS.

**Improved 3–4 sentence description:**

> GA4 Reports brings Google Analytics 4 into the Capell admin as cached daily snapshots, so owners see traffic trends, top pages, sessions, and conversions beside the content they manage. A scheduled sync authenticates with a Google service account, stores a configurable window locally, and powers dashboard widgets and overview stats that never call GA4 at render time. Setup status, sync history, and a swappable data-client contract keep the integration transparent and testable. Built for marketing and growth teams who want analytics signal in the CMS without standing up a separate reporting tool.

**Screenshot/media status:** Manifest ships the marketplace card plus all three committed admin PNG captures from `docs/screenshots.json` (dashboard page, setup-status, settings). Remaining optional media depth is a tighter hero crop of the traffic-trend chart for the listing.

**Pricing / tier / bundle positioning:** `premium` tier, `Capell Growth` group, `growth` bundle — correct. It sits alongside `insights` (also Growth/growth bundle) which records _first-party_ visits/events/journeys; GA4 Reports is the _third-party_ analytics counterpart. Position GA4 Reports as the "bring your existing GA4 in" on-ramp and `insights` as the "own your first-party data" upgrade.

**Cross-sell (via deps + Extension Suites):**

- **insights** — same bundle; "first-party vs GA4" complementary pairing; bundle discount.
- **dashboard-reports** (Operations) — generic reporting widgets that can host GA4 cards.
- **seo-suite** (Search & SEO) — already has Search Console insights; cross-sell as a "full acquisition picture" (organic search + GA4 behaviour).
- The shipped **CSV digest/export** seam is a natural hook into a marketing/email package for scheduled reporting depth.

**Differentiators / value props / target buyer:** Snapshot architecture (zero GA4 calls at render → fast, quota-safe dashboards); swappable `GA4ReportsDataClientInterface` (fake in tests, OAuth/alternate backend in host); admin-native, no third-party BI tool. Target buyer: marketing/growth lead or site owner on a Capell site who already runs GA4 and wants the headline numbers inside the CMS.

**Keywords/tags:** `ga4`, `google-analytics`, `analytics`, `traffic-reports`, `dashboard`, `top-pages`, `conversions`, `marketing-analytics`, `growth`, `service-account`, `scheduled-sync`, `filament`

## Completion Review

Completion review refreshed 2026-06-14. The current manifest-backed plan is closed for the approved GA4 Reports slice: GA4 Reports ships local snapshot syncing, null-client protection, retry/backoff and quota handling, persisted token cache, configurable schedules, setup/error visibility, dashboard aggregate caching, period-over-period widgets, aggregate events and average-session-duration reporting, shared credential-readiness validation, real health diagnostics, runner-backed screenshots, and CSV digest/export Actions. OAuth connect flow, multi-property selection, realtime/named-event breakdowns, PDF/scheduled emails, configurable report dimensions, and host-managed credential storage remain future product-depth candidates.

## 6. Prioritized Roadmap

| Item                                                                                                     | Bucket  | Effort | Impact | Section                                                                                                                    |
| -------------------------------------------------------------------------------------------------------- | ------- | ------ | ------ | -------------------------------------------------------------------------------------------------------------------------- |
| Fix client binding to fall back to null client when unconfigured (or delete null client + claims)        | Shipped | S      | High   | §4                                                                                                                         |
| Implement real `Ga4ReportsHealthCheck` (credentials, last-sync recency, API reachability)                | Shipped | M      | High   | §4                                                                                                                         |
| Add retry-with-backoff + 429/quota handling to GA4 HTTP calls                                            | Done    | M      | High   | §3, §4                                                                                                                     |
| Fix "GA4 Reports 4" typo across composer/README/lang/command                                             | Shipped | S      | Med    | §4                                                                                                                         |
| Generate & commit the 3 required screenshots                                                             | Done    | S      | High   | §5 — closed 2026-06-06: three Capell runner PNG captures are committed and promoted into marketplace media.                |
| Shipped: rewrite marketplace summary + description                                                       | Done    | S      | High   | §5                                                                                                                         |
| Remove orphan `GA4ReportsSettingsPage` (or wire it)                                                      | Shipped | S      | Med    | §4                                                                                                                         |
| Shipped 2026-06-06: Cache dashboard read aggregates; set `cacheTags` + revisit `cacheSafety` in manifest | Done    | M      | High   | §2, §4 — local aggregate DTO cache added with sync invalidation and manifest cache metadata.                               |
| Shipped 2026-06-06: Make overview stats honour the dashboard default date range                          | Done    | S      | Med    | §2                                                                                                                         |
| Shipped 2026-06-06: Add period-over-period comparison (deltas) to widgets                                | Done    | M      | High   | §3 — overview rows, traffic trend datasets, and top-pages rows compare against the immediately preceding dashboard window. |
| Shipped 2026-06-06: Persisted/cross-process OAuth token cache                                            | Done    | S      | Med    | §3                                                                                                                         |
| Done/Shipped: Add "Sync now" page action + surface last sync error                                       | Done    | M      | Med    | §2                                                                                                                         |
| Done/Shipped 2026-06-14: Surface aggregate Events and Avg. session duration in overview and CSV digest   | Done    | S      | Med    | §2, §3                                                                                                                     |
| Done/Shipped 2026-06-14: Validate service-account credential readiness in settings/status/health         | Done    | M      | High   | §2, §4                                                                                                                     |
| Rename command to `capell:` convention; populate manifest `commands`                                     | Done    | S      | Med    | §2 — Done 2026-06-06: primary command is `capell:ga4-reports-sync`; legacy `ga4-reports:sync` remains an alias.            |
| Shipped 2026-06-06: Configurable sync schedule (frequency/cron via settings)                             | Done    | S      | Med    | §2                                                                                                                         |
| Shipped 2026-06-08: CSV digest/export Action seam                                                        | Done    | M      | Med    | §3                                                                                                                         |
| Multi-property support + property picker                                                                 | Future  | L      | High   | §3                                                                                                                         |
| OAuth user-consent connect flow (alongside service account)                                              | Future  | L      | High   | §3                                                                                                                         |
| Events/conversions breakdown widget + PDF export & scheduled digest                                      | Future  | L      | Med    | §3                                                                                                                         |
| Configurable report dimensions (channel, country, device)                                                | Future  | L      | Med    | §3                                                                                                                         |
| Shipped: Test that the container binds the real client when configured                                   | Done    | S      | Med    | §4                                                                                                                         |

## 7. Verification

Latest focused verification:

```bash
vendor/bin/pest packages/ga4-reports/tests/Feature/Actions/GA4ReportsActionsTest.php packages/ga4-reports/tests/Feature/Filament/GA4ReportsFilamentTest.php packages/ga4-reports/tests/Feature/Health/GA4ReportsHealthCheckTest.php --configuration=phpunit.xml
```

Result: passed on 2026-06-14 (35 tests, 145 assertions).

Full package verification:

```bash
vendor/bin/pest packages/ga4-reports/tests --configuration=phpunit.xml
```

Result: passed on 2026-06-14 (48 tests, 207 assertions).

Preflight:

```bash
COMPOSER=composer.local.json composer preflight
```

Result: passed on 2026-06-14.

## 8. Completion Checklist

- [x] Package plan refreshed against current code.
- [x] Comprehensive review pass completed locally for Actions, Filament widgets/settings, health checks, docs, manifest, and tests.
- [x] Capell audience pass completed for package developers/integrators, admin operators, and site-owner marketplace positioning.
- [x] Approved implementation slice shipped for aggregate metrics and credential readiness.
- [x] Focused GA4 Reports verification passed.
- [x] Package tests passed.
- [x] Repo preflight passed for changed files.
