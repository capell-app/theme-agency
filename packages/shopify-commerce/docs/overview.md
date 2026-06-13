# Shopify Commerce

<!-- prettier-ignore-start -->

## What This Plugin Adds

Shopify Commerce is an **Available**, **Schema-owning** Capell package in the **Capell Commerce** product group. It ships as `capell-app/shopify-commerce` and extends these surfaces: admin, console.

Shopify Commerce adds site-scoped Shopify Admin API OAuth, catalog sync, and customer cache foundations for Capell.

After install, admins get package-owned management or reporting surfaces inside Capell.

Status details:

- Status: Available
- Tier: premium
- Bundle: commerce
- Composer package: `capell-app/shopify-commerce`
- Namespace: `Capell\ShopifyCommerce`
- Theme key: not applicable

## Why It Matters

**For developers:** The package gives developers package-owned service providers, Actions, Data objects, models, Laravel routes, Filament classes, and Blade views instead of pushing this behaviour into core or application code.

**For teams:** Connect any Shopify store to Capell in minutes and keep its product catalog and customer data in sync - securely, per site, with no storefront lock-in.

## Screens And Workflow

Screenshot contract: `screenshots.json`.

- Shopify connection page (admin, required).
- Shopify catalog sync state (admin, required).
- Shopify product search (admin, required).

## Technical Shape

- Service providers: `Capell\ShopifyCommerce\Providers\ShopifyCommerceServiceProvider`, `Capell\ShopifyCommerce\Providers\AdminServiceProvider`.
- Config files: `packages/shopify-commerce/config/capell-shopify-commerce.php`.
- Migrations: `packages/shopify-commerce/database/migrations/2026_05_22_000001_create_shopify_connections_table.php`, `packages/shopify-commerce/database/migrations/2026_05_22_000002_create_shopify_oauth_states_table.php`, `packages/shopify-commerce/database/migrations/2026_05_22_000003_create_shopify_products_table.php`, `packages/shopify-commerce/database/migrations/2026_05_22_000004_create_shopify_product_variants_table.php`, `packages/shopify-commerce/database/migrations/2026_06_01_000001_create_shopify_customers_table.php`.
- Settings migrations: `packages/shopify-commerce/database/settings/2026_05_22_000001_create_shopify_commerce_settings.php`.
- Settings classes: `ShopifyCommerceSettings`.
- Models: `ShopifyConnection`, `ShopifyCustomer`, `ShopifyOAuthState`, `ShopifyProduct`, `ShopifyProductVariant`.
- Filament classes: `ShopifyConnectionPage`, `ShopifyCommerceSettingsSchema`.
- Route files: `packages/shopify-commerce/routes/oauth.php`.
- Events: `ShopifyCustomerSynced`.
- Actions: `BuildShopifyCatalogThemeDataAction`, `ContinueShopifyProductBulkSyncAction`, `FetchShopifyProductAction`, `ImportShopifyProductBulkSyncAction`, `InvalidateShopifyProductSearchCacheAction`, `PersistShopifyProductAction`, `PollShopifyProductBulkSyncAction`, `SanitizeShopifySyncErrorAction`, `SearchShopifyProductsAction`, `StartShopifyProductBulkSyncAction`, `SyncShopifyProductsAction`, `SyncShopifyCustomersAction`, `and 16 more`.
- Data objects: `ShopifyCallbackQueryData`, `ShopifyCatalogProductThemeData`, `ShopifyCatalogSummaryThemeData`, `ShopifyCatalogThemeData`, `ShopifyCatalogVariantThemeData`, `ShopifyProductData`, `ShopifyProductOptionData`, `ShopifyProductVariantData`, `ShopifyTokenExchangeResponseData`.
- Command signatures: `capell-shopify-commerce:install`, `capell-shopify-commerce:prune-oauth-states`, `capell-shopify-commerce:sync-customers`.
- Console command classes: `InstallShopifyCommerceCommand`, `PruneExpiredShopifyOAuthStatesCommand`, `SyncShopifyCustomersCommand`, `SyncShopifyProductsCommand`.
- Health checks: `Capell\ShopifyCommerce\Health\ShopifyCommerceHealthCheck`.
- Blade views: `packages/shopify-commerce/resources/views/filament/pages/connection.blade.php`.
- Cache tags: `shopify-commerce`.

## Data Model

- Required tables: `shopify_connections`, `shopify_oauth_states`, `shopify_products`, `shopify_product_variants`, `shopify_customers`.
- Protected tables: `shopify_connections`, `shopify_oauth_states`, `shopify_products`, `shopify_product_variants`, `shopify_customers`.
- Models: `ShopifyConnection`, `ShopifyCustomer`, `ShopifyOAuthState`, `ShopifyProduct`, `ShopifyProductVariant`.
- Migration files: `2026_05_22_000001_create_shopify_connections_table.php`, `2026_05_22_000002_create_shopify_oauth_states_table.php`, `2026_05_22_000003_create_shopify_products_table.php`, `2026_05_22_000004_create_shopify_product_variants_table.php`, `2026_06_01_000001_create_shopify_customers_table.php`.
- Migration impact: run host migrations through the package install flow before opening package surfaces.
- Deletion/retention behaviour: Docs gap unless the package has an explicit pruning command, retention setting, or tested cascade path.

## Install Impact

- Admin navigation: adds package-owned Filament classes when registered.
- Permissions: `shopify-commerce.manage`.
- Public routes: route files exist and must be reviewed before public enablement.
- Database changes: package migrations are declared.
- Settings: `Capell\ShopifyCommerce\Settings\ShopifyCommerceSettings`.
- Queues or schedules: none detected in standard package paths.
- Cache tags: `shopify-commerce`.
- Commands: `capell-shopify-commerce:install`, `capell-shopify-commerce:prune-oauth-states`, `capell-shopify-commerce:sync-customers`.

## Common Pitfalls

- Run migrations before opening package resources or public routes.
- Configure package settings before testing production-like workflows.
- Review route middleware, throttling, signed URLs, and public-output safety before exposing routes.
- Run package commands from the host app; in this repository use `vendor/bin/pest` for package tests.
- Keep `composer.json`, `composer.local.json`, `capell.json`, docs, screenshots, and tests aligned when the package surface changes.

## Troubleshooting

| Symptom | Likely cause | Check | Fix |
| --- | --- | --- | --- |
| Package surface is missing after install | Provider or manifest is not loaded | Confirm `capell.json`, package `composer.json`, and provider registration | Reinstall the package, refresh Composer autoload, and clear host caches |
| Admin screen or command fails on missing table | Package migrations have not run | Check the tables listed in `Data Model` | Run host migrations and rerun the focused package test |
| Route returns unexpected output | Route cache, middleware, or signed URL setup does not match the package route file | Check the route files listed in `Technical Shape` | Clear route cache and verify middleware before exposing public routes |
| Background work does not run | Queue worker or scheduled command is not active | Check package jobs, commands, and host scheduler configuration | Start the queue or scheduler, then run the focused command or package test |

## Quick Start

1. Install the package: `composer require capell-app/shopify-commerce`.
2. Run the required setup: `php artisan capell-shopify-commerce:install`.
3. Open the related Capell admin surface and verify Shopify Commerce appears.

## Next Steps

- [Package docs index](README.md)
- [Screenshot contract](screenshots.json)
- [Marketplace assets](assets/marketplace/)
- [Capell content language plan](../../../docs/CONTENT_LANGUAGE_PLAN.md)
- [Capell documentation design system](../../../docs/DESIGN_SYSTEM.md)
- [Capell and package ERD notes](../../../docs/erd/capell-and-package-erds.md)
- Related packages: [Contacts](../../contacts/README.md), [Diagnostics](../../diagnostics/README.md), [Media Library](../../media-library/README.md), [Payments](../../payments/README.md), [Search](../../search/README.md), [Theme Commerce](../../theme-commerce/README.md).
- Focused tests: `vendor/bin/pest packages/shopify-commerce/tests --configuration=phpunit.xml`.

<!-- prettier-ignore-end -->
