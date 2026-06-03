# Changelog

All notable changes to `capell-app/campaign-studio` will be documented in this file.

## Unreleased

### 2026-06-03

- Rewrote the marketplace summary, package description, and composer description to lead with the buyer outcome rather than a feature list, and aligned all three so the manifest and composer manifest match.
- Promoted six real product screenshots (campaign groups, landing-page variants, conversion-goal form, CTA form, dashboard widgets, and the published landing page) into the `marketplace.screenshots` manifest block; previously only the extension card image was listed.
- Removed the unused `AttributionModel` enum. It was never referenced by `BuildConversionAttributionAction` (which always records both first- and last-touch fields) and shipped as dead API surface; its only consumer was a single test assertion, now removed.
- Hardened the campaign overview conversion-rate KPI: `BuildCampaignOverviewStatsAction` now excludes campaign groups with a null or blank `utm_campaign` from the Insights visit join, preventing unrelated visits from skewing the headline conversion rate.

## Earlier

- Prepared package metadata and documentation for ongoing Capell 4.x package work.
