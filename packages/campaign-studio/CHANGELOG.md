# Changelog

All notable changes to `capell-app/campaign-studio` will be documented in this file.

## Unreleased

### 2026-06-16

- Changed Campaign Studio's Site Discovery public URL contributor to scan landing pages in chunks and dedupe canonical URLs during sitemap discovery.

### 2026-06-06

- Added `SyncCampaignStatusesAction`, the `capell:campaign-studio-sync-statuses` command, and an every-five-minutes schedule so campaign windows automatically move groups from Scheduled to Active and from Active to Ended.

### 2026-06-04

- Added `BuildCampaignExperimentResultsAction` and typed result data so Campaign Studio can read back synced Experiments winner reports with per-variant conversion rates and lift over control.
- Added configurable conversion attribution lookback via `capell-campaign-studio.attribution.lookback_days`; stale Insights visits no longer populate conversion identity or UTM attribution outside the configured window.
- Counted distinct Insights visits in the campaign overview conversion-rate KPI so campaign groups sharing a `utm_campaign` no longer double-count the same visit.
- Added the public campaign conversion beacon at `/capell/campaigns/conversions` and a post-load tracker that records page-view and CTA-click conversions from public campaign pages.
- Added `CaptureCampaignConversionAction` and `CampaignConversionCaptureData` so beacon requests resolve Insights visits, landing pages, CTA widgets, and conversion goals through a typed package boundary.
- Scoped CTA-click beacon goal resolution to the campaign landing page resolved from the submitted URL, avoiding cross-campaign misattribution when campaigns reuse the same goal key.
- Registered the Campaign Studio tracker through the frontend render-hook registry and marked it as non-cacheable frontend output with UTM variance metadata, aligning the package with the static HTML cache contract.
- Added feature coverage for beacon route registration, page-view capture, CTA-click capture, invalid-origin rejection, full-page public output safety, and cache contribution recording.
- Added UTM fields to the campaign hero widget configurator so hero CTAs can use the same attribution metadata as campaign CTA widgets.
- Routed campaign hero primary and secondary button URLs through `BuildCampaignUrlAction`, preserving existing query strings/fragments while appending missing UTM parameters.
- Added render coverage proving campaign hero buttons emit decorated URLs without leaking numeric campaign identifiers.
- Filtered campaign landing-page variant resolution to linked pages that pass Capell's public `publishedDate()` scope, preventing scheduled or expired pages from being selected as targeted, primary, or fallback variants.

### 2026-06-03

- Rewrote the marketplace summary, package description, and composer description to lead with the buyer outcome rather than a feature list, and aligned all three so the manifest and composer manifest match.
- Promoted six real product screenshots (campaign groups, landing-page variants, conversion-goal form, CTA form, dashboard widgets, and the published landing page) into the `marketplace.screenshots` manifest block; previously only the extension card image was listed.
- Removed the unused `AttributionModel` enum. It was never referenced by `BuildConversionAttributionAction` (which always records both first- and last-touch fields) and shipped as dead API surface; its only consumer was a single test assertion, now removed.
- Hardened the campaign overview conversion-rate KPI: `BuildCampaignOverviewStatsAction` now excludes campaign groups with a null or blank `utm_campaign` from the Insights visit join, preventing unrelated visits from skewing the headline conversion rate.

## Earlier

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
