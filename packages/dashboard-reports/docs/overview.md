---
title: 'Dashboard Reports Overview'
description: 'How the Capell Dashboard Reports package adds reusable admin dashboard health and publishing trend widgets.'
---

# Dashboard Reports Overview

Dashboard Reports adds reusable admin dashboard widgets for Capell sites that need editorial health and publishing activity surfaced in one place.

Use it when a project needs a package-owned reporting layer, but does not need a bespoke analytics package. The package stays admin-only and reads existing Capell page state instead of creating new content records.

## What It Adds

- Content health reporting for scheduled pages, expired pages, pages without URLs, and stale published pages.
- Publishing trend reporting across common date windows.
- Filtered deep-links from content-health issue counts into the Page resource.
- Dashboard widgets registered into the main Capell admin dashboard.
- A `ContentHealthDataProvider` implementation that can replace the admin package's null provider when the package is installed.
- Dashboard settings contribution for report visibility, plus package config for the stale-page threshold.
- Diagnostics checks for package install state, provider binding, dashboard widget registration, dashboard settings contribution, and page-table filter registration.

## Admin Surface

Dashboard Reports registers these widgets through `CapellAdmin::registerDashboardWidget(...)`:

| Widget                       | Purpose                                                |
| ---------------------------- | ------------------------------------------------------ |
| `ContentHealthWidget`        | Shows content issues that need editorial attention.    |
| `PublishingTrendChartWidget` | Shows published and scheduled page activity over time. |

`ContentHealthWidget` is only visible when the resolved content health provider returns at least one issue. Its data is computed and cached by Livewire for 300 seconds, and the package also memoises the provider result for the current request so `canView()` and `data()` share the same build.

Content-health issue counts link to the Page resource with the package-owned `dashboard_reports_health` table filter preselected. The filter is registered through the admin `PageTableExtender` contract and covers scheduled pages, expired pages, pages without URLs, and stale published pages.

The stale-page threshold defaults to 90 days and can be changed with `capell-dashboard-reports.stale_page_threshold_days`. The package clamps the resolved value to 1-3650 days, and the Content Health widget and Page resource drill-down filter share the same resolved threshold.

## Frontend Surface

Dashboard Reports is admin-only. It does not register public frontend routes, public Blade renders, render hooks, or frontend assets in the current implementation.

## Screenshot Coverage

The screenshot contract is stored in [screenshots.json](screenshots.json). Final capture should seed enough page state to show both the publishing trend chart and content health widget on the admin dashboard.

## Data Sources

The package reads Capell core `Page` records through `SiteScope::applyForCurrentActor(..., denyWhenMissingActor: true)`, so editors only see counts for sites they can access and non-admin/anonymous execution resolves empty report counts instead of unscoped site data.

| Report             | Source                                                                      |
| ------------------ | --------------------------------------------------------------------------- |
| Scheduled pages    | `Page::pending()`                                                           |
| Expired pages      | `Page::expired()`                                                           |
| Pages without URLs | Pages without related page URL records                                      |
| Stale pages        | Published pages older than the configured stale-day threshold               |
| Publishing trend   | Published and scheduled page counts bucketed across the selected date range |

Dashboard Reports does not create reporting tables. It computes the current dashboard state from the installed site's page records. The publishing trend Action receives the dashboard's resolved date range from the widget, so chart buckets and headline totals use the same selected window.

## Diagnostics

`DashboardReportsHealthCheck` reports real Diagnostics results for:

- Package installed state.
- Content health provider availability.
- Publishing Trend and Content Health widget registration.
- Dashboard settings contributor registration.
- Content-health Page resource filter registration.

## Extension Notes

If a project needs different content health rules, bind `Capell\Admin\Contracts\Dashboard\ContentHealthDataProvider` before the Dashboard Reports admin provider boots. The package only replaces the admin null provider; it will not override a real provider already registered by the host app or another package.

Put new report calculations in `src/Actions/Dashboard/` and keep Filament widgets thin. Tests should target the Action output first, then widget visibility where needed.

## Install And Verify

Install the package in a host Capell app:

```bash
composer require capell-app/dashboard-reports
```

Then verify the package in this repository with:

```bash
vendor/bin/pest packages/dashboard-reports/tests --configuration=phpunit.xml
```
