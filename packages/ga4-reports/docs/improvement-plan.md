# GA4 Reports — Improvement & Growth Plan

> Package: capell-app/ga4-reports · Kind: package · Tier: premium · Product group: Capell Growth · Bundle: growth · Status: Draft

## 1. Snapshot

GA4 Reports is a snapshot-based Google Analytics 4 reporting package: a scheduled/CLI sync (`SyncGA4ReportsMetricsAction` → `ga4-reports:sync`) pulls a date window from the GA4 Data API through a hand-rolled service-account client (`src/Support/Insights/GA4ReportsDataClient.php`, RS256 JWT auth, in-memory token cache, paginated page reports) and persists into three local tables (`ga4_reports_daily_metrics`, `ga4_reports_page_metrics`, `ga4_reports_sync_runs`). Admin surfaces — one extension `GA4ReportsPage`, five widgets (overview stats, traffic trend, top pages, top-pages table, setup status), three dashboard overview stats, and a settings group — read only from local rows via `Build*Action` DTO builders, never hitting GA4 during render. Surfaces declared: `admin`, `console`; deps `capell-app/admin`, `capell-app/core` plus `lorisleiva/laravel-actions` + `spatie/laravel-data`. Current marketplace summary, verbatim: **"GA4 Reports provides dashboard reporting for Capell."** Manifest declares **1** screenshot (`docs/assets/marketplace/extension-card.jpg`); `docs/screenshots.json` defines **3** deployment captures but `docs/screenshots/` does not exist — media gap.

## Completed Improvement Slices

- **2026-06-04:** Added bounded retry/backoff configuration for GA4 token and Data API calls, including transient connection failures, GA4 quota exhaustion responses, `Retry-After`, and explicit final quota-exhaustion messaging.
- **2026-06-05:** Shipped the GA4 Reports cleanup slice: the service provider now falls back to `NullGA4ReportsDataClient` until enabled/property/credentials settings are complete, the stray "GA4 Reports 4" copy is removed from composer/README/lang/command text, and the orphan settings page is absent because settings are managed through the registered `ga4_reports` settings surface. Evidence: `tests/Feature/Package/GA4ReportsPackageTest.php` covers null/real client binding; `src/Providers/AdminServiceProvider.php` registers `GA4ReportsPage` plus the settings management surface only; focused typo checks cover `composer.json`, `README.md`, `resources/lang/en/package.php`, and `src/Console/Commands/SyncGA4ReportsCommand.php`.

## 2. Improvements (existing functionality)

- **Surface `averageSessionDuration` and `eventCount`, or stop computing them** — `BuildGA4ReportsOverviewAction::averageSessionDuration()` runs an extra full `->get()` load on every overview render to weight a value no widget or overview stat ever displays; `conversions`/`eventCount` are persisted but `eventCount` is never shown. Either add an "Avg. session duration" / "Events" metric row to `GA4ReportsOverviewStatsWidget` or drop the computation. — `src/Actions/BuildGA4ReportsOverviewAction.php`, `src/Filament/Widgets/GA4ReportsOverviewStatsWidget.php` — S
- **Cache dashboard read aggregates** — five widgets each re-run `SUM`/`GROUP BY` queries against the metric tables on every Livewire render/poll; data only changes once per daily sync. Wrap `Build*Action` results in a short-TTL cache keyed by `property_id` + window, invalidated by `SyncGA4ReportsMetricsAction`. Manifest `performance.cacheTags` is empty and `cacheSafety.cacheable=false`; revisit both. — `src/Actions/BuildGA4ReportsOverviewAction.php`, `BuildGA4ReportsTrendAction.php`, `BuildTopGA4ReportsPagesAction.php` — M
- **Make overview stats honour the dashboard date range** — widgets use `BuildsGA4ReportsDashboardWindow` (respects `HasDashboardDateRange`), but `AdminServiceProvider::ga4Overview()` calls `BuildGA4ReportsOverviewAction::run()` with no window, so the three registered overview stats always show the default `sync_days` window regardless of the dashboard date filter. Inconsistent numbers between the stat strip and the widget. — `src/Providers/AdminServiceProvider.php` — S
- **Rename the command to the `capell:` convention** — sibling packages use `capell:<pkg>-<verb>` (e.g. `capell:seo-suite-setup`); this exposes a bare `ga4-reports:sync`. Align for discoverability under `php artisan list`, and populate `commands` in `capell.json` (currently all `null` despite a `console` surface + `ga4-reports-console` capability). — `src/Console/Commands/SyncGA4ReportsCommand.php`, `capell.json` — S
- **Make the sync schedule configurable** — `registerSchedule()` hardcodes `->daily()`. GA4 data latency and operator preference vary; expose a frequency/time setting (or cron expression) read from `GA4ReportsSettings`/config, as other scheduled packages do. — `src/Providers/AdminServiceProvider.php` — S
- **Mark `credentials_path` as a path field, validate existence** — settings `TextInput` has no validation; an unreadable/missing path only surfaces as a runtime `GA4ReportsApiException` deep in a sync run. Add a "file exists & is JSON service account" validation/affordance in `GA4ReportsSettingsSchema` and reflect it in setup status. — `src/Filament/Settings/GA4ReportsSettingsSchema.php`, `src/Filament/Widgets/GA4ReportsSetupStatusWidget.php` — M
- **Add a "Sync now" action to the page/setup widget** — operators currently must wait for the daily schedule or shell into the host to run the command. A gated header action on `GA4ReportsPage` dispatching `SyncGA4ReportsMetricsAction` (queued) closes the loop. — `src/Filament/Pages/GA4ReportsPage.php` — M
- **Surface the last sync error to operators** — `GA4ReportsSyncRun.error_message` is persisted but the setup-status widget only shows `status`; show the failure reason (sanitised) when `status = failed`. — `src/Filament/Widgets/GA4ReportsSetupStatusWidget.php` — S

## 3. Missing Features (gaps)

Capabilities declared: `ga4-reports`, `ga4-reports-admin`, `ga4-reports-console`. Against GA4-integration norms:

- **API quota / 429 handling + retry-with-backoff shipped.** `GA4ReportsDataClient::runReport()` and `accessToken()` now retry transient connection failures, common upstream statuses, `429`, and GA4 `RESOURCE_EXHAUSTED` responses with bounded backoff and `Retry-After` support. Final quota failures now raise an explicit quota-exhaustion message. Keep the focused data-client tests as regression coverage. — `src/Support/Insights/GA4ReportsDataClient.php`, `tests/Unit/Support/GA4ReportsDataClientTest.php`
- **Persisted / cross-process token cache.** The OAuth access token is cached only in the `GA4ReportsDataClient` instance (`$this->accessToken`). The client is bound per-process, so every queue worker/CLI invocation re-signs a JWT and re-hits the token endpoint. Cache the token (it carries an expiry) in the cache store. — `src/Support/Insights/GA4ReportsDataClient.php`
- **Service-account is the only auth path.** Norm is to also support OAuth user consent (Search Console / GA4 connect-flow) so non-technical owners avoid hand-placing a JSON key file. The contract (`GA4ReportsDataClientInterface`) makes this swappable, but no OAuth client ships. Differentiator vs table-stakes.
- **Single property only.** `property_id` is a scalar setting; no multi-property selection or property picker, and the schema indexes already key on `property_id`, so multi-property is half-built. Multi-property is a clear premium differentiator for agencies/multi-site owners.
- **No date-range comparisons.** Widgets render a single window; there is no period-over-period delta (e.g. "+12% vs previous 30d"), which is the headline feature of most GA4 dashboards. The `HasDashboardDateRange` trait gives the current window but no comparison window. Differentiator.
- **No realtime / no events or conversions breakdown widget.** `eventCount` and `conversions` are stored but only `conversions` appears (as a column); there is no events-by-name or conversions-by-event widget, and no GA4 realtime card. Table stakes for "GA4 reporting".
- **No export / scheduled email of the report.** No CSV/PDF export of top pages or scheduled digest — common buyer expectation and a cross-sell hook into a marketing/email package.
- **Dimensions are fixed.** Reports are hardcoded to `date` / `pagePathPlusQueryString` / `pageTitle`; no channel/source-medium, country, or device dimension. No configurable report builder. Differentiator if added.

## 4. Issues / Risks

- **Null-client binding shipped.** `GA4ReportsServiceProvider::bindGA4ReportsClient()` now binds `NullGA4ReportsDataClient` until enabled, property ID, and credentials path are all configured, keeping the README and data-client docs accurate. Regression evidence lives in `tests/Feature/Package/GA4ReportsPackageTest.php`. — `src/Support/Insights/NullGA4ReportsDataClient.php`, `src/Providers/GA4ReportsServiceProvider.php`
- **Documentation mismatch closed by null-client binding.** `docs/data-client.md` states "The package binds a real client when config is complete and **a null client when it is not**", and `README.md` lists "null-client behavior" as a value prop; the provider binding now matches those claims. — `docs/data-client.md`, `README.md`, `src/Providers/GA4ReportsServiceProvider.php`
- **Health check shipped.** `Ga4ReportsHealthCheck` now runs real Diagnostics probes for storage tables, model/schema availability, credential/settings readiness, latest successful sync freshness, and data-client/API read reachability where an enabled install has local credentials ready. Output intentionally avoids echoing the GA4 property ID, private key, credential JSON, or full credentials path. Evidence: `vendor/bin/pest packages/ga4-reports/tests/Feature/Health/GA4ReportsHealthCheckTest.php --configuration=phpunit.xml` (14 tests / 38 assertions). — `src/Health/Ga4ReportsHealthCheck.php`, `tests/Feature/Health/GA4ReportsHealthCheckTest.php`, `config/capell-ga4-reports.php`
- **Orphan settings page removed.** Settings are surfaced via `registerExtensionManagementSurface` + the `ga4_reports` settings group, and only `GA4ReportsPage` is registered as an extension page. No `GA4ReportsSettingsPage` file remains in the package. — `src/Providers/AdminServiceProvider.php`
- **Limited caching resilience remains (see §3)** — transient GA4 5xx/429 failures now retry before the sync run fails, but access tokens are still cached only in process and the sync schedule is still daily by default. — `src/Support/Insights/GA4ReportsDataClient.php`, `src/Providers/AdminServiceProvider.php`
- **Bound client coverage added; real sync-path exception coverage remains.** `GA4ReportsDataClientTest` exercises `GA4ReportsDataClient` directly, and `tests/Feature/Package/GA4ReportsPackageTest.php` now asserts the container binds the null client when unconfigured and the real client when configured. `SyncGA4ReportsMetricsAction` still uses `FakeGA4ReportsDataClient` for lifecycle/error-path tests. — `tests/Feature/Actions/GA4ReportsActionsTest.php`, `tests/Feature/Package/GA4ReportsPackageTest.php`
- **Credential/secret handling.** The service-account JSON (containing a private key) lives at an operator-set filesystem path; `credentials_path` is stored in the settings DB and rendered as a plain `TextInput` (path disclosure, no `password()`/masking). The client reads the file each token refresh with an `is_readable` guard and throws typed exceptions — acceptable — but secrets should never be echoed; confirm the path field and any error surfacing never leak the file contents, and mask the path in the UI. Public-output safety is not a concern here (admin-only surfaces, no frontend) but the capell "never leak API credentials" rule still applies to admin error messages. — `src/Filament/Settings/GA4ReportsSettingsSchema.php`, `src/Support/Insights/GA4ReportsDataClient.php`
- **Performance budget unconfigured.** `capell.json performance`: `frontendRenderBudgetMs: 0` (acceptable — no frontend), `adminQueryBudget: 40`, but `cacheTags: []` and `cacheSafety.cacheable: false` while five widgets run repeated aggregate queries. Either justify uncached reads against the 40-query budget or wire caching + tags (§2). — `capell.json`
- **`commercial.privateDocsRequested: true` but `docs/` is public-style.** The README links siblings and renders public OpenGraph preview cards; confirm what is intended to be private vs marketplace-public. — `capell.json`, `README.md`
- **Done/Shipped: Branding typo "GA4 Reports 4".** The stray "4" copy has been removed from composer, README, language, and command-description surfaces; focused `rg` checks now find no shipped-code copy matches. — `composer.json`, `README.md`, `resources/lang/en/package.php`, `src/Console/Commands/SyncGA4ReportsCommand.php`
- **PHP floor lags house standard.** `composer.json` requires `php: ^8.3`; the capell convention targets PHP 8.4. Low risk (code is 8.3-compatible) but inconsistent with siblings. — `composer.json`
- **i18n.** Strings are translated via `capell-ga4-reports::` keys across `widgets`, `settings`, `sync` (good). Gaps: `Ga4ReportsHealthCheck` and the `MarketingStudioActionData`/`ExtensionManagementSurfaceData` icons use raw strings (acceptable), but the README/docs typo and the persisted enum-like `status` strings (`running`/`succeeded`/`failed`) are raw literals in the model/sync action rather than a backed enum with labels — the capell convention prefers backed enums for persisted values. — `src/Actions/SyncGA4ReportsMetricsAction.php`, `src/Filament/Widgets/GA4ReportsSetupStatusWidget.php`

## 5. Marketplace & Selling

**Current `summary`:** "GA4 Reports provides dashboard reporting for Capell." — too generic; "GA4" only appears in the name, "dashboard reporting" could describe any package, and it names no benefit, metric, or buyer. **Composer `description`:** "GA4 Reports 4 dashboard reporting for Capell." — same weakness plus the "Reports 4" typo.

**Improved 1-sentence summary:**

> Pull Google Analytics 4 traffic, top-page, and conversion snapshots into your Capell admin on a daily schedule — no per-pageview API calls, no leaving the CMS.

**Improved 3–4 sentence description:**

> GA4 Reports brings Google Analytics 4 into the Capell admin as cached daily snapshots, so owners see traffic trends, top pages, sessions, and conversions beside the content they manage. A scheduled sync authenticates with a Google service account, stores a configurable window locally, and powers dashboard widgets and overview stats that never call GA4 at render time. Setup status, sync history, and a swappable data-client contract keep the integration transparent and testable. Built for marketing and growth teams who want analytics signal in the CMS without standing up a separate reporting tool.

**Screenshot/media gaps:** Manifest ships only the marketplace card (`extension-card.jpg`); `docs/screenshots.json` specifies three required captures (dashboard page, setup-status configured/not-configured, settings) but `docs/screenshots/` does not exist and no PNGs are committed. Generate and commit all three; add a hero shot of the traffic-trend chart for the listing.

**Pricing / tier / bundle positioning:** `premium` tier, `Capell Growth` group, `growth` bundle — correct. It sits alongside `insights` (also Growth/growth bundle) which records _first-party_ visits/events/journeys; GA4 Reports is the _third-party_ analytics counterpart. Position GA4 Reports as the "bring your existing GA4 in" on-ramp and `insights` as the "own your first-party data" upgrade.

**Cross-sell (via deps + Extension Suites):**

- **insights** — same bundle; "first-party vs GA4" complementary pairing; bundle discount.
- **dashboard-reports** (Operations) — generic reporting widgets that can host GA4 cards.
- **seo-suite** (Search & SEO) — already has Search Console insights; cross-sell as a "full acquisition picture" (organic search + GA4 behaviour).
- A future **export/digest** feature is a natural hook into a marketing/email package.

**Differentiators / value props / target buyer:** Snapshot architecture (zero GA4 calls at render → fast, quota-safe dashboards); swappable `GA4ReportsDataClientInterface` (fake in tests, OAuth/alternate backend in host); admin-native, no third-party BI tool. Target buyer: marketing/growth lead or site owner on a Capell site who already runs GA4 and wants the headline numbers inside the CMS.

**Keywords/tags:** `ga4`, `google-analytics`, `analytics`, `traffic-reports`, `dashboard`, `top-pages`, `conversions`, `marketing-analytics`, `growth`, `service-account`, `scheduled-sync`, `filament`

## 6. Prioritized Roadmap

| Item                                                                                              | Bucket | Effort | Impact | Section |
| ------------------------------------------------------------------------------------------------- | ------ | ------ | ------ | ------- |
| Fix client binding to fall back to null client when unconfigured (or delete null client + claims) | Shipped | S      | High   | §4      |
| Implement real `Ga4ReportsHealthCheck` (credentials, last-sync recency, API reachability)         | Shipped | M      | High   | §4      |
| Add retry-with-backoff + 429/quota handling to GA4 HTTP calls                                     | Done   | M      | High   | §3, §4  |
| Fix "GA4 Reports 4" typo across composer/README/lang/command                                      | Shipped | S      | Med    | §4      |
| Generate & commit the 3 required screenshots; rewrite marketplace summary + description           | Now    | S      | High   | §5      |
| Remove orphan `GA4ReportsSettingsPage` (or wire it)                                               | Shipped | S      | Med    | §4      |
| Cache dashboard read aggregates; set `cacheTags` + revisit `cacheSafety` in manifest              | Next   | M      | High   | §2, §4  |
| Make overview stats honour the dashboard date range                                               | Next   | S      | Med    | §2      |
| Add period-over-period comparison (deltas) to widgets                                             | Next   | M      | High   | §3      |
| Persisted/cross-process OAuth token cache                                                         | Next   | S      | Med    | §3      |
| Add "Sync now" page action + surface last sync error                                              | Next   | M      | Med    | §2      |
| Rename command to `capell:` convention; populate manifest `commands`                              | Next   | S      | Med    | §2      |
| Configurable sync schedule (frequency/cron via settings)                                          | Next   | S      | Med    | §2      |
| Multi-property support + property picker                                                          | Later  | L      | High   | §3      |
| OAuth user-consent connect flow (alongside service account)                                       | Later  | L      | High   | §3      |
| Events/conversions breakdown widget + CSV/PDF export & scheduled digest                           | Later  | L      | Med    | §3      |
| Configurable report dimensions (channel, country, device)                                         | Later  | L      | Med    | §3      |
| Test that the container binds the real client when configured                                     | Later  | S      | Med    | §4      |
