---
title: 'GA4 Reports Overview'
description: 'How the Capell GA4 Reports package syncs Google Analytics 4 metrics into local dashboard snapshots.'
---

# GA4 Reports Overview

GA4 Reports syncs Google Analytics 4 metrics into local Capell tables so admin dashboards can show traffic trends and top pages without calling GA4 on every page load.

Use it for installed sites that need GA4 visibility in the Capell admin panel. The package is intentionally snapshot-based: the sync command pulls a configured window, stores the result, and widgets read the local rows.

## What It Adds

- GA4 settings for property ID, credentials path, route slug, and sync window.
- Local sync runs, daily metrics, and page metrics tables.
- A `ga4-reports:sync` command for refreshing local snapshots.
- A `GA4ReportsPage` Filament extension page under the monitoring navigation group.
- Dashboard widgets for setup status, overview stats, traffic trends, and top pages.
- `GA4ReportsDataClientInterface` so the GA4 client can be swapped or faked in tests.

## Configuration

The publishable config lives at `packages/ga4-reports/config/capell-ga4-reports.php`.

| Key                       | Default             | Purpose                                                       |
| ------------------------- | ------------------- | ------------------------------------------------------------- |
| `enabled`                 | `false`             | Keeps the package inert until GA4 is configured.              |
| `property_id`             | `null`              | GA4 property to query.                                        |
| `credentials_path`        | `null`              | Service account credential path used by the data client.      |
| `http_timeout`            | `20`                | Token and Data API request timeout.                           |
| `http_retry_times`        | `3`                 | Total attempts for retryable token and Data API failures.     |
| `http_retry_delay_ms`     | `250`               | Base retry delay for transient GA4 HTTP failures.             |
| `http_retry_max_delay_ms` | `5000`              | Maximum retry delay, including `Retry-After` delays.          |
| `sync_days`               | `30`                | Number of days included in the sync window.                   |
| `route_slug`              | `ga4-reports`       | Admin route slug for the reports page.                        |
| `tables.*`                | package table names | Allows host apps to override the local table names if needed. |

If the package is not configured, `SyncGA4ReportsMetricsAction` exits cleanly and records no rows.

## Sync Workflow

Run the sync from the host Capell app:

```bash
php artisan ga4-reports:sync
```

The command calls `SyncGA4ReportsMetricsAction`, which:

1. Builds the configured date window.
2. Resolves `GA4ReportsDataClientInterface`.
3. Creates a `GA4ReportsSyncRun` row with `running` status.
4. Persists daily metrics and page metrics.
5. Marks the sync run as `succeeded` or `failed`.

Failures are captured on the sync run and the command still exits successfully, so scheduled jobs can keep running while the dashboard shows the latest known state.

The GA4 data client retries transient connection failures, quota-limited responses, and common upstream error statuses before the sync run is marked failed. Final quota failures are reported as quota exhaustion instead of a generic non-2xx response.

## Stored Data

| Model                   | Purpose                                                                       |
| ----------------------- | ----------------------------------------------------------------------------- |
| `GA4ReportsSyncRun`     | Tracks each sync attempt, status, row counts, date window, and error message. |
| `GA4ReportsDailyMetric` | Stores daily totals for dashboard trend charts.                               |
| `GA4ReportsPageMetric`  | Stores page-level metrics for top-page widgets.                               |

The package stores reporting snapshots, not raw visitor events.

## Extension Notes

Bind a concrete `GA4ReportsDataClientInterface` implementation in the host app or package provider. Tests can fake the interface and exercise `SyncGA4ReportsMetricsAction` directly.

Keep dashboard reads local. New widgets should read `GA4ReportsDailyMetric`, `GA4ReportsPageMetric`, or Action DTOs rather than calling the GA4 API during Filament rendering.

## Admin Surfaces

- Extension page: `GA4ReportsPage`.
- Header widgets: overview stats, traffic trend, top pages, and setup status.
- Footer widget: top pages table.
- Settings schema: `GA4ReportsSettingsSchema`.
- Overview stats: views, sessions, and engagement rate.

## Screenshot Automation

Deployment should read [screenshots.json](screenshots.json), install the package with demo data, resolve the admin page and settings section, and write images to `packages/ga4-reports/docs/screenshots`.
The dashboard, setup-status, and settings captures are committed under `docs/screenshots/` and promoted into the marketplace manifest.
