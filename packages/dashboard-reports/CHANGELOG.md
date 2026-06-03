# Changelog

All notable changes to `capell-app/dashboard-reports` will be documented in this file.

## Unreleased

- Prepared package metadata and documentation for ongoing Capell 4.x package work.

## 2026-06-03

- Performance: the Content Health widget now builds its data once per request instead of twice (once for `canView()` and again for `data()`), memoising the result for the duration of the dashboard render.
- Performance: the Publishing Trend chart now resolves all seven buckets with two grouped aggregate queries (published and scheduled) instead of fourteen per-bucket `COUNT` round-trips. Added a query-budget guard test.
- Marketplace: rewrote the `capell.json` marketplace summary and top-level description, and aligned the `composer.json` description, to describe the concrete content-health and publishing-trend value instead of "generic CMS reporting widgets".
- Tests: removed a stale `ContentHealthData::from([...])` fixture that referenced fields no longer present on `ContentHealthData`, and added coverage proving content-health data is built only once per request.
