# URL Manager

<!-- prettier-ignore-start -->

## What This Plugin Adds

URL Manager is an **Available**, **Schema-owning** Capell package in the **Capell Search & SEO** product group. It ships as `capell-app/url-manager` and extends these surfaces: admin, frontend.

URL Manager keeps your site's link equity intact when pages move or get renamed. It auto-creates redirects when a page URL changes, resolves exact, prefix, and regex rules on the live request, and tracks hit counts so you can see which redirects matter. Repeated 404s and SEO Suite broken-link findings surface as one-click redirect opportunities, with CSV import/export for bulk migrations. Built for editors and SEO teams who need redirect hygiene without touching server config.

After install, admins get package-owned management surfaces and public users may see package-owned frontend output or routes.

Status details:

- Status: Available
- Tier: premium
- Bundle: search-seo
- Composer package: `capell-app/url-manager`
- Namespace: `Capell\UrlManager`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, and Filament classes instead of pushing this behaviour into core or application code.

**For teams:** Stop losing traffic to broken links - manage redirects, auto-preserve moved page URLs, and turn repeated 404s into recovered SEO.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Create and edit redirect rule form (admin, required).
- Redirect import workflow (admin, required).
- Redirect export workflow (admin, required).
- Redirect health snapshot output (admin, required).

## Screenshot Evidence

These captures are the package-owned visual contract for the admin pages, public pages, actions, workflows, and feature surfaces described above. Keep this section aligned with `docs/screenshots.json` whenever the package surface changes.

### Create and edit redirect rule form

![Create and edit redirect rule form](screenshots/create-edit-redirect-form.png)

- Surface: admin · Target: RedirectRulesPage.
- Documents: An editor creates or updates redirect rules without touching server configuration.
- Capture notes: Capture the redirect rules admin page with the create or edit redirect form open, showing source URL, target URL, match type, status, query preservation, and notes fields.

### Redirect import workflow

![Redirect import workflow](screenshots/redirect-import-workflow.png)

- Surface: admin · Target: RedirectRulesPage/importRedirectRules.
- Documents: A migration team imports redirect mappings in bulk after a site restructure.
- Capture notes: Capture the redirect import action or resulting import preview state for CSV-based bulk redirect migrations.

### Redirect export workflow

![Redirect export workflow](screenshots/redirect-export-workflow.png)

- Surface: admin · Target: RedirectRulesPage/exportRedirectRules.
- Documents: An SEO manager exports redirect rules for review, migration handoff, or audit evidence.
- Capture notes: Capture the redirect export action or CSV export state from the redirect rules table.

### Redirect health snapshot output

![Redirect health snapshot output](screenshots/redirect-health-snapshot-output.png)

- Surface: admin · Target: RedirectRulesPage.
- Documents: An operator verifies URL Manager is installed, tracking redirect health, and ready for SEO remediation work.
- Capture notes: Capture the redirect rules table or package health snapshot showing active redirects, status, and recent hit evidence.

## Technical Shape

- Service providers: `Capell\UrlManager\Providers\UrlManagerServiceProvider`.
- Config files: `packages/url-manager/config/capell-url-manager.php`.
- Migrations: `packages/url-manager/database/migrations/2026_05_31_000001_create_url_manager_redirect_rules_table.php`, `packages/url-manager/database/migrations/2026_05_31_000002_create_url_manager_redirect_hits_table.php`, `packages/url-manager/database/migrations/2026_05_31_000003_create_url_manager_not_found_opportunities_table.php`, `packages/url-manager/database/migrations/2026_06_04_000001_add_priority_to_url_manager_redirect_rules_table.php`.
- Models: `NotFoundOpportunity`, `RedirectHit`, `RedirectRule`.
- Filament classes: `NotFoundOpportunitiesPage`, `RedirectRulesPage`, `RedirectRuleForm`, `NotFoundOpportunitiesTable`, `RedirectRulesTable`.
- Listeners: `RecordRedirectForChangedPageUrl`.
- Actions: `BuildCanonicalUrlAction`, `BuildNotFoundRedirectSuggestionsAction`, `BuildRedirectRulesCsvAction`, `BuildRedirectRulesCsvTemplateAction`, `ConvertNotFoundOpportunityToRedirectAction`, `DeleteRedirectRuleAction`, `ExportRedirectRulesAction`, `ImportRedirectRulesAction`, `ImportSeoSuiteBrokenLinksAction`, `NormalizeManagedUrlAction`, `ParseRedirectRulesCsvAction`, `PrepareRedirectRuleDataAction`, `and 11 more`.
- Data objects: `ChangedUrlRedirectData`, `ConvertNotFoundOpportunityData`, `NotFoundOpportunityData`, `PreparedRedirectRuleData`, `RedirectImportResultData`, `RedirectImportRowData`, `RedirectResolutionData`, `RedirectRuleData`.
- Command signatures: `url-manager:prune-hits`.
- Console command classes: `PruneRedirectHitsCommand`.
- Manifest contributions: `admin-page: Capell\UrlManager\Manifest\NotFoundOpportunitiesPageContribution`, `admin-page: Capell\UrlManager\Manifest\RedirectRulesPageContribution`, `console-command: Capell\UrlManager\Manifest\UrlManagerConsoleCommandsContribution`, `health-check: Capell\UrlManager\Manifest\UrlManagerHealthContribution`, `model: Capell\UrlManager\Manifest\UrlManagerModelsContribution`.
- Health checks: `Capell\UrlManager\Health\UrlManagerHealthCheck`.
- Cache tags: `url-manager`.

## Data Model

- Required tables: `url_manager_redirect_rules`, `url_manager_redirect_hits`, `url_manager_not_found_opportunities`.
- Models: `NotFoundOpportunity`, `RedirectHit`, `RedirectRule`.
- Migration files: `2026_05_31_000001_create_url_manager_redirect_rules_table.php`, `2026_05_31_000002_create_url_manager_redirect_hits_table.php`, `2026_05_31_000003_create_url_manager_not_found_opportunities_table.php`, `2026_06_04_000001_add_priority_to_url_manager_redirect_rules_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `View:RedirectRulesPage`, `Manage:RedirectRules`, `View:NotFoundOpportunitiesPage`, `Manage:NotFoundOpportunities`.
- Public routes: none detected in package route files.
- Database changes: package migrations are declared.
- Settings: no package settings declared.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `url-manager`.
- Commands: `url-manager:prune-hits`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Keep public Blade and cached HTML free of authoring markers, model IDs, permissions, signed editor URLs, and lazy database queries.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |
| Public output leaks unexpected state | Render data, cache variation, or authoring boundary has regressed | Check public Blade, cache tags, and public-output safety tests | Move data loading out of Blade and rerun the package public-output tests |

## Quick Start

1. Install the package: `composer require capell-app/url-manager`.
2. Run the required setup: `php artisan migrate`.
3. Open the related Capell admin surface and verify URL Manager appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Migration Assistant](../../migration-assistant/README.md), [Seo Suite](../../seo-suite/README.md), [Site Discovery](../../site-discovery/README.md), [Wordpress Importer](../../wordpress-importer/README.md).
- Focused tests: `vendor/bin/pest packages/url-manager/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
