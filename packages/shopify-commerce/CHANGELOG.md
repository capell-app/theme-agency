# Changelog

All notable changes to `capell-app/shopify-commerce` will be documented in this file.

## Unreleased

- Unified live search and bulk import product persistence through `PersistShopifyProductAction`, so cached search results now store options, raw snapshots, variants, synced timestamps, and prune stale variants consistently.
- Scrub Shopify sync failure messages before persisting `last_sync_error`, removing raw Shopify URLs, shop domains, Admin API tokens, and token headers from stored admin diagnostics.

### 2026-06-03

- Replaced the stubbed `ShopifyCommerceHealthCheck` with Diagnostics checks for storage tables, Shopify app credentials, active connection token presence, and stale queued/running/importing sync operations.
- Rewrote the marketplace summary, listing description, and Composer description with the improved buyer-facing copy from the package improvement plan.
