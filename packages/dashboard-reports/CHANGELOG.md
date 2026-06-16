# Changelog

All notable changes to `capell-app/dashboard-reports` will be documented in this file.

## Unreleased

- Digests: added `capell:dashboard-reports:send-digest`, a scoped email digest Action, queued notification, configurable recipients, and manifest capability metadata.
- Diagnostics: added real Dashboard Reports health checks for install state, content-health provider binding, dashboard widgets, settings contribution, and page-list filter registration.
- Drill-downs: content-health issue counts now deep-link to the Page resource with a package-owned Content Health filter for scheduled, expired, URL-less, and stale pages.
- Publishing trend: the widget now passes the admin dashboard's resolved date range into the Action, and `totalScheduled` is scoped to the same selected range as the scheduled chart series.
- Tests: added health-check, content-health render/gating, filtered deep-link, page-table extender, and period-scoped scheduled total coverage.

## 2026-06-03

- Performance: the Content Health widget now builds its data once per request instead of twice (once for `canView()` and again for `data()`), memoising the result for the duration of the dashboard render.
- Performance: the Publishing Trend chart now resolves all seven buckets with two grouped aggregate queries (published and scheduled) instead of fourteen per-bucket `COUNT` round-trips. Added a query-budget guard test.
- Marketplace: rewrote the `capell.json` marketplace summary and top-level description, and aligned the `composer.json` description, to describe the concrete content-health and publishing-trend value instead of "generic CMS reporting widgets".
- Tests: removed a stale `ContentHealthData::from([...])` fixture that referenced fields no longer present on `ContentHealthData`, and added coverage proving content-health data is built only once per request.
