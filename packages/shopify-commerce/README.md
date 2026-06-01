# Shopify Commerce

Shopify Admin API connection and catalog sync foundation for Capell CMS.

## At A Glance

- Package: `capell-app/shopify-commerce`
- Namespace: `Capell\ShopifyCommerce\`
- Surfaces: Filament admin, console, authenticated OAuth routes, queue, database
- Service providers: `Capell\ShopifyCommerce\Providers\ShopifyCommerceServiceProvider`, `Capell\ShopifyCommerce\Providers\AdminServiceProvider`
- Capell dependencies: `capell-app/admin`, `capell-app/core`
- Third-party dependencies: `laravel/framework`, `lorisleiva/laravel-actions`, `spatie/laravel-data`, `spatie/laravel-package-tools`, `spatie/laravel-settings`

## Boundaries

This package owns Shopify app configuration, admin OAuth, site-scoped Shopify connections, local catalog cache tables, catalog search, and the first-party admin page for connecting or syncing a store.

It does not own storefront rendering, checkout, cart state, order import, webhook ingestion, product merchandising UI, or public frontend output. It may cache Shopify customer records for CRM integrations. Frontend packages should consume synced catalog/customer records through explicit Actions or package-owned view models, not by leaking Shopify admin tokens or connection metadata into public HTML.

## Why It Helps Your Capell Workflow

- Adds site-scoped Shopify OAuth and product catalog sync so Capell can reference commerce data without storing Shopify credentials in content code.
- Helps owners connect store catalog data to Capell workflows while keeping checkout and storefront ownership outside this package.
- Gives developers Actions for OAuth, GraphQL, bulk sync, import, and local product search with focused test coverage.

## Best Used With

- [Search](../search/README.md)
- [Public Actions](../public-actions/README.md)
- [Diagnostics](../diagnostics/README.md)

## What It Adds

- Admin page: `Capell\ShopifyCommerce\Filament\Pages\ShopifyConnectionPage`.
- OAuth routes: `capell-shopify-commerce.oauth.install` and `capell-shopify-commerce.oauth.callback` under `capell/oauth/shopify`.
- Commands: `capell-shopify-commerce:install` and `capell-shopify-commerce:sync {connection?}`.
- Settings group: `shopify_commerce` through `ShopifyCommerceSettings`.
- Permission: `manage_shopify_commerce`.
- Protected tables: `shopify_connections`, `shopify_oauth_states`, `shopify_products`, `shopify_product_variants`.
- Diagnostics health check: `Capell\ShopifyCommerce\Health\ShopifyCommerceHealthCheck`.

## Install And Configure

Install the package in a host Capell application:

```bash
composer require capell-app/shopify-commerce
php artisan capell-shopify-commerce:install
php artisan migrate
```

Set the Shopify app credentials in the host environment:

```dotenv
CAPELL_SHOPIFY_COMMERCE_ENABLED=true
SHOPIFY_APP_CLIENT_ID=your-shopify-client-id
SHOPIFY_APP_CLIENT_SECRET=your-shopify-client-secret
```

The publishable config lives at `config/capell-shopify-commerce.php` in the host app. The package default config is `packages/shopify-commerce/config/capell-shopify-commerce.php`.

## Runtime Surface

| Surface          | Class or file                        | Job                                                                                                         |
| ---------------- | ------------------------------------ | ----------------------------------------------------------------------------------------------------------- |
| Package provider | `ShopifyCommerceServiceProvider`     | Loads config, routes, views, translations, migrations, settings, models, and protected table registrations. |
| Admin provider   | `AdminServiceProvider`               | Registers the admin page and console commands when the package is installed and enabled.                    |
| Admin page       | `ShopifyConnectionPage`              | Connects a store, dispatches sync, disconnects a store, and searches cached products.                       |
| OAuth install    | `ShopifyInstallController`           | Validates the shop domain, creates a nonce, and redirects to Shopify OAuth.                                 |
| OAuth callback   | `ShopifyCallbackController`          | Validates HMAC and state, exchanges the code, stores the connection, and queues catalog sync.               |
| GraphQL client   | `ExecuteShopifyAdminGraphqlAction`   | Calls Shopify Admin GraphQL and paces requests from throttle metadata.                                      |
| Catalog sync     | `SyncShopifyProductsAction`          | Queues or starts a bulk product sync and prevents overlapping work per connection.                          |
| Catalog import   | `ImportShopifyProductBulkSyncAction` | Imports JSONL bulk output into product and variant tables.                                                  |
| Search           | `SearchShopifyProductsAction`        | Searches local products first and falls back to live Shopify product search when needed.                    |
| Settings         | `ShopifyCommerceSettings`            | Stores API version, default scopes, and catalog search cache TTL.                                           |

## Docs

- [Docs index](docs/README.md)
- [Overview](docs/overview.md)
- [OAuth and catalog sync](docs/oauth-and-catalog-sync.md)
- [Screenshot manifest](docs/screenshots.json)

## Verification

Run package tests from the repository root:

```bash
vendor/bin/pest packages/shopify-commerce/tests --configuration=phpunit.xml
```

Use focused tests while changing one surface:

```bash
vendor/bin/pest packages/shopify-commerce/tests/Feature/OAuth --configuration=phpunit.xml
vendor/bin/pest packages/shopify-commerce/tests/Unit/Actions/SyncShopifyProductsActionTest.php --configuration=phpunit.xml
vendor/bin/pest packages/shopify-commerce/tests/Unit/Actions/SearchShopifyProductsActionTest.php --configuration=phpunit.xml
```

## Troubleshooting

| Symptom                                                 | Likely cause                                                           | Check                                                                                                   | Fix                                                                                                      |
| ------------------------------------------------------- | ---------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------- |
| Admin page is missing                                   | Package is disabled or not installed in Capell package registry        | Check `CAPELL_SHOPIFY_COMMERCE_ENABLED` and package install state for `capell-app/shopify-commerce`     | Enable the package and run the host install flow again.                                                  |
| OAuth install redirects back with a configuration error | `SHOPIFY_APP_CLIENT_ID` or `SHOPIFY_APP_CLIENT_SECRET` is empty        | Inspect host config key `capell-shopify-commerce.client_id` and `capell-shopify-commerce.client_secret` | Set both env vars and clear host config cache.                                                           |
| Callback fails after returning from Shopify             | HMAC mismatch, expired state, wrong user, or invalid shop domain       | Check Laravel logs for `Shopify OAuth failed` and inspect `shopify_oauth_states.expires_at`             | Restart OAuth from the admin page; verify app secret and callback URL.                                   |
| Sync command says no connection exists                  | No active row in `shopify_connections`                                 | Query `shopify_connections.status` and `shopify_connections.sync_status`                                | Connect the store again or pass a specific connection id to `capell-shopify-commerce:sync {connection}`. |
| Search results are stale                                | Search cache version has not changed or sync has not imported new rows | Check cache key prefix `capell-shopify-commerce.search.` and product `synced_at` values                 | Run sync, then confirm `InvalidateShopifyProductSearchCacheAction` bumps the connection version.         |

## Maintenance Notes

- Keep tokens in `shopify_connections.access_token`; never expose them through public frontend output.
- Add new Shopify behavior as Actions and cover the Action directly before wiring admin UI.
- Scope admin queries by `ShopifySiteContext` so site-limited users only see assigned site connections.
- Use `ShopifyCommerceSettings` for runtime API version, default scopes, and search cache TTL instead of hard-coding values in UI code.
