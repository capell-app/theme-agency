# Capell Packages — cross-cutting docs

This directory holds **cross-cutting** documentation for the add-on packages. Per-package docs (API, Database, README) live alongside each package in `packages/<name>/`.

## Per-package references

For the commercial/free grouping, see [Package product groups](product-groups.md).
For package-level upstream credits, services, and acknowledgements, see [Credits and acknowledgements](credits-and-acknowledgements.md).
For the long-running package improvement burn-down, see [Improvement Plan Status](improvement-plan-status.md).
For theme package authoring, see [Creating a Capell theme](creating-a-theme.md).
For screenshot-led theme QA and optimization, see [Theme Screenshot QA Playbook](theme-screenshot-qa-playbook.md).
For the long-running first-party theme improvement workflow, see [Theme Premium Improvement Runbook](theme-premium-improvement-runbook.md).
For planned local-business premium themes, see [Local Business Premium Theme Plan](local-business-premium-theme-plan.md).
For optional package integration rules, see [Optional Package Boundaries](optional-package-boundaries.md).
For split repository contribution flow, see [Split PR Forwarding](split-pr-forwarding.md).
For the package Blade view coverage ratchet, see [Blade View Coverage](blade-view-coverage.md).

Use package `overview.md` pages for search-facing package summaries and task-level orientation. Use focused package docs for API, data, workflow, provider, and extension contracts.

Use [Package Documentation Standard](package-documentation-standard.md) when creating or revising package READMEs and docs. It defines the required workflow value, technical surface, debugging, testing, and review checks.

## Package Docs By Intent

| Intent                                      | Package docs                                                                                                                                                                                                                                  |
| ------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Dashboard reporting and operations signals  | [Dashboard Reports overview](../packages/dashboard-reports/docs/overview.md), [Diagnostics overview](../packages/diagnostics/docs/overview.md), [Login Audit overview](../packages/login-audit/docs/overview.md)                              |
| Analytics, growth, and conversion reporting | [GA4 Reports overview](../packages/ga4-reports/docs/overview.md), [Insights overview](../packages/insights/docs/overview.md), [Campaign Studio overview](../packages/campaign-studio/docs/overview.md)                                        |
| SEO, search, and public discovery           | [SEO Suite overview](../packages/seo-suite/docs/overview.md), [Search overview](../packages/search/docs/overview.md), [Site Discovery README](../packages/site-discovery/README.md)                                                           |
| Public agent-readable content               | [Agent Delivery overview](../packages/agent-delivery/docs/overview.md), [Site Discovery overview](../packages/site-discovery/docs/overview.md), [SEO Suite overview](../packages/seo-suite/docs/overview.md)                                  |
| Demo data and frontend presentation         | [Demo Kit overview](../packages/demo-kit/docs/overview.md), [Foundation Theme overview](../packages/foundation-theme/docs/overview.md), [Creating a Capell theme](creating-a-theme.md)                                                        |
| Editor previews and publishing confidence   | [Capell Filament Peek overview](../packages/filament-peek/docs/overview.md), [Publishing Studio overview](../packages/publishing-studio/docs/overview.md), [Frontend Authoring overview](../packages/frontend-authoring/docs/overview.md)     |
| Engagement and public discussion            | [Comments overview](../packages/comments/docs/overview.md), [Blog overview](../packages/blog/docs/overview.md), [Email Studio overview](../packages/email-studio/docs/overview.md)                                                            |
| Admin access and security                   | [Password Policy overview](../packages/password-policy/docs/overview.md), [Access Gate requests](../packages/access-gate/docs/access-requests.md), [Public Actions integrations](../packages/public-actions/docs/actions-and-integrations.md) |
| Commerce integrations                       | [Shopify Commerce overview](../packages/shopify-commerce/docs/overview.md), [Shopify OAuth and catalog sync](../packages/shopify-commerce/docs/oauth-and-catalog-sync.md)                                                                     |

| Package              | Local reference                                                                                                                                      |
| -------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------- |
| Access Gate          | [`packages/access-gate/README.md`](../packages/access-gate/README.md), [`docs`](../packages/access-gate/docs/overview.md)                            |
| Address              | [`packages/address/README.md`](../packages/address/README.md), [`docs`](../packages/address/docs/overview.md)                                        |
| Agent Bridge         | [`packages/agent-bridge/README.md`](../packages/agent-bridge/README.md), [`docs`](../packages/agent-bridge/docs/overview.md)                         |
| Agent Delivery       | [`packages/agent-delivery/README.md`](../packages/agent-delivery/README.md), [`docs`](../packages/agent-delivery/docs/overview.md)                   |
| AI Orchestrator      | [`packages/ai-orchestrator/README.md`](../packages/ai-orchestrator/README.md), [`docs`](../packages/ai-orchestrator/docs/overview.md)                |
| API                  | [`packages/api/README.md`](../packages/api/README.md), [`docs`](../packages/api/docs/overview.md)                                                    |
| Widget Library       | [`packages/widget-library/README.md`](../packages/widget-library/README.md), [`docs`](../packages/widget-library/docs/overview.md)                   |
| Blog                 | [`packages/blog/README.md`](../packages/blog/README.md), [`docs`](../packages/blog/docs/overview.md)                                                 |
| Campaign Studio      | [`packages/campaign-studio/README.md`](../packages/campaign-studio/README.md), [`docs`](../packages/campaign-studio/docs/overview.md)                |
| Comments             | [`packages/comments/README.md`](../packages/comments/README.md), [`docs`](../packages/comments/docs/overview.md)                                     |
| Content Sections     | [`packages/content-sections/README.md`](../packages/content-sections/README.md), [`docs`](../packages/content-sections/docs/overview.md)             |
| Dashboard Reports    | [`packages/dashboard-reports/README.md`](../packages/dashboard-reports/README.md), [`docs`](../packages/dashboard-reports/docs/overview.md)          |
| Demo Kit             | [`packages/demo-kit/README.md`](../packages/demo-kit/README.md), [`docs`](../packages/demo-kit/docs/overview.md)                                     |
| Deployments          | [`packages/deployments/README.md`](../packages/deployments/README.md), [`docs`](../packages/deployments/docs/overview.md)                            |
| Diagnostics          | [`packages/diagnostics/README.md`](../packages/diagnostics/README.md), [`docs`](../packages/diagnostics/docs/overview.md)                            |
| Document Lifecycle   | [`packages/document-lifecycle/README.md`](../packages/document-lifecycle/README.md), [`docs`](../packages/document-lifecycle/docs/overview.md)       |
| Email Studio         | [`packages/email-studio/README.md`](../packages/email-studio/README.md), [`docs`](../packages/email-studio/docs/overview.md)                         |
| Events               | [`packages/events/README.md`](../packages/events/README.md), [`docs`](../packages/events/docs/overview.md)                                           |
| Filament Peek        | [`packages/filament-peek/README.md`](../packages/filament-peek/README.md), [`docs`](../packages/filament-peek/docs/overview.md)                      |
| Form Builder         | [`packages/form-builder/README.md`](../packages/form-builder/README.md), [`docs`](../packages/form-builder/docs/overview.md)                         |
| Foundation Theme     | [`packages/foundation-theme/README.md`](../packages/foundation-theme/README.md), [`docs`](../packages/foundation-theme/docs/overview.md)             |
| Frontend Authoring   | [`packages/frontend-authoring/README.md`](../packages/frontend-authoring/README.md), [`docs`](../packages/frontend-authoring/docs/overview.md)       |
| Frontend Optimizer   | [`packages/frontend-optimizer/README.md`](../packages/frontend-optimizer/README.md), [`docs`](../packages/frontend-optimizer/docs/overview.md)       |
| GA4 Reports          | [`packages/ga4-reports/README.md`](../packages/ga4-reports/README.md), [`docs`](../packages/ga4-reports/docs/overview.md)                            |
| Hero                 | [`packages/hero/README.md`](../packages/hero/README.md), [`docs`](../packages/hero/docs/overview.md)                                                 |
| HTML Cache           | [`packages/html-cache/README.md`](../packages/html-cache/README.md), [`docs`](../packages/html-cache/docs/overview.md)                               |
| Insights             | [`packages/insights/README.md`](../packages/insights/README.md), [`docs`](../packages/insights/docs/overview.md)                                     |
| Layout Builder       | [`packages/layout-builder/README.md`](../packages/layout-builder/README.md), [`docs`](../packages/layout-builder/docs/overview.md)                   |
| Login Audit          | [`packages/login-audit/README.md`](../packages/login-audit/README.md), [`docs`](../packages/login-audit/docs/overview.md)                            |
| Media AI             | [`packages/media-ai/README.md`](../packages/media-ai/README.md), [`docs`](../packages/media-ai/docs/overview.md)                                     |
| Media Library        | [`packages/media-library/README.md`](../packages/media-library/README.md), [`docs`](../packages/media-library/docs/overview.md)                      |
| Migration Assistant  | [`packages/migration-assistant/README.md`](../packages/migration-assistant/README.md), [`docs`](../packages/migration-assistant/docs/overview.md)    |
| Navigation           | [`packages/navigation/README.md`](../packages/navigation/README.md), [`docs`](../packages/navigation/docs/overview.md)                               |
| Newsletter           | [`packages/newsletter/README.md`](../packages/newsletter/README.md), [`docs`](../packages/newsletter/docs/overview.md)                               |
| Notes                | [`packages/notes/README.md`](../packages/notes/README.md), [`docs`](../packages/notes/docs/overview.md)                                              |
| Password Policy      | [`packages/password-policy/README.md`](../packages/password-policy/README.md), [`docs`](../packages/password-policy/docs/overview.md)                |
| Public Actions       | [`packages/public-actions/README.md`](../packages/public-actions/README.md), [`docs`](../packages/public-actions/docs/overview.md)                   |
| Publishing Studio    | [`packages/publishing-studio/README.md`](../packages/publishing-studio/README.md), [`docs`](../packages/publishing-studio/docs/overview.md)          |
| Search               | [`packages/search/README.md`](../packages/search/README.md), [`docs`](../packages/search/docs/overview.md)                                           |
| SEO Suite            | [`packages/seo-suite/README.md`](../packages/seo-suite/README.md), [`docs`](../packages/seo-suite/docs/overview.md)                                  |
| Shopify Commerce     | [`packages/shopify-commerce/README.md`](../packages/shopify-commerce/README.md), [`docs`](../packages/shopify-commerce/docs/README.md)               |
| Site Discovery       | [`packages/site-discovery/README.md`](../packages/site-discovery/README.md), [`docs`](../packages/site-discovery/docs/overview.md)                   |
| Tags                 | [`packages/tags/README.md`](../packages/tags/README.md), [`docs`](../packages/tags/docs/overview.md)                                                 |
| Theme Agency         | [`packages/theme-agency/README.md`](../packages/theme-agency/README.md), [`docs`](../packages/theme-agency/docs/overview.md)                         |
| Theme Commerce       | [`packages/theme-commerce/README.md`](../packages/theme-commerce/README.md), [`docs`](../packages/theme-commerce/docs/overview.md)                   |
| Theme Corporate      | [`packages/theme-corporate/README.md`](../packages/theme-corporate/README.md), [`docs`](../packages/theme-corporate/docs/overview.md)                |
| Theme Education      | [`packages/theme-education/README.md`](../packages/theme-education/README.md), [`docs`](../packages/theme-education/docs/overview.md)                |
| Theme Healthcare     | [`packages/theme-healthcare/README.md`](../packages/theme-healthcare/README.md), [`docs`](../packages/theme-healthcare/docs/overview.md)             |
| Theme Knowledge      | [`packages/theme-knowledge/README.md`](../packages/theme-knowledge/README.md), [`docs`](../packages/theme-knowledge/docs/overview.md)                |
| Theme Local Services | [`packages/theme-local-services/README.md`](../packages/theme-local-services/README.md), [`docs`](../packages/theme-local-services/docs/overview.md) |
| Theme Nonprofit      | [`packages/theme-nonprofit/README.md`](../packages/theme-nonprofit/README.md), [`docs`](../packages/theme-nonprofit/docs/overview.md)                |
| Theme Portfolio      | [`packages/theme-portfolio/README.md`](../packages/theme-portfolio/README.md), [`docs`](../packages/theme-portfolio/docs/overview.md)                |
| Theme SaaS           | [`packages/theme-saas/README.md`](../packages/theme-saas/README.md), [`docs`](../packages/theme-saas/docs/overview.md)                               |
| Translation Manager  | [`packages/translation-manager/README.md`](../packages/translation-manager/README.md), [`docs`](../packages/translation-manager/docs/overview.md)    |
| Welcome Tour         | [`packages/welcome-tour/README.md`](../packages/welcome-tour/README.md), [`docs`](../packages/welcome-tour/docs/overview.md)                         |
| WordPress Importer   | [`packages/wordpress-importer/README.md`](../packages/wordpress-importer/README.md), [`docs`](../packages/wordpress-importer/docs/overview.md)       |

For the full documentation site, see [docs.capell.app](https://docs.capell.app). For the package overview and dependency matrix, see the [repository README](../README.md).

## Cross-package install order

- Blog depends on Layout Builder; install `capell-app/layout-builder` before `capell-app/blog`.
- Theme packages extend Foundation Theme; install `capell-app/layout-builder`, then `capell-app/foundation-theme`, then the theme package such as `capell-app/theme-agency`, `capell-app/theme-commerce`, `capell-app/theme-corporate`, `capell-app/theme-education`, `capell-app/theme-healthcare`, `capell-app/theme-knowledge`, `capell-app/theme-local-services`, `capell-app/theme-nonprofit`, `capell-app/theme-portfolio`, or `capell-app/theme-saas`.
- WordPress Importer registers a source for Migration Assistant; install `capell-app/migration-assistant` before `capell-app/wordpress-importer`.

## Screenshot Automation

Package screenshots are generated from committed manifests during deployment.
See [Package Screenshot Automation](package-screenshot-automation.md) for the contract
and expected output path.
GitHub Actions provides a `Screenshot Manifests` workflow that validates the committed
manifests stay in sync.
