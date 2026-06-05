# Shopify Commerce — Improvement & Growth Plan

> Package: capell-app/shopify-commerce · Kind: package · Tier: premium · Product group: Capell Commerce · Bundle: commerce · Status: Draft

## 1. Snapshot

Shopify Commerce adds a site-scoped Shopify Admin API integration to Capell: admins connect a `*.myshopify.com` store via OAuth, the encrypted Admin API token is stored per site, and a GraphQL bulk operation syncs products/variants into local cache tables for admin-side catalog lookup. It also ships a customer-cache table and a `ShopifyCustomerSynced` event consumed by the `contacts` package for CRM. It explicitly does **not** own storefront rendering, checkout, carts, orders, or webhook ingestion.

- **Surfaces** (`capell.json`): `admin`, `console` (manifest); plus authenticated OAuth web routes (`routes/oauth.php`), queue (`SyncShopifyProductsAction` as job), and database.
- **Key Actions** (`src/Actions`): OAuth — `BuildShopifyAuthorizeUrlAction`, `CreateShopifyOAuthStateAction`, `ValidateShopifyShopDomainAction`, `ValidateShopifyHmacAction`, `ValidateShopifyOAuthStateAction`, `ExchangeShopifyAuthorizationCodeAction`, `ConnectShopifyStoreAction`, `DisconnectShopifyStoreAction`. Catalog — `SyncShopifyProductsAction`, `StartShopifyProductBulkSyncAction`, `PollShopifyProductBulkSyncAction`, `ImportShopifyProductBulkSyncAction`, `FetchShopifyProductAction`, `SearchShopifyProductsAction`, `InvalidateShopifyProductSearchCacheAction`. GraphQL — `ExecuteShopifyAdminGraphqlAction`. Customers — `UpsertShopifyCustomerAction`. Install — `InstallShopifyCommercePackageAction`, `InstallShopifyCommercePermissionsAction`.
- **Models/tables**: `shopify_connections`, `shopify_oauth_states`, `shopify_products`, `shopify_product_variants`, `shopify_customers` (all registered as protected tables in `ShopifyCommerceServiceProvider::registerProtectedTables`).
- **Dependencies**: requires `capell-app/admin`, `capell-app/core`; `supports: []`. Third-party: `laravel/framework`, `lorisleiva/laravel-actions`, `spatie/laravel-data`, `spatie/laravel-package-tools`, `spatie/laravel-settings`. README lists "Best Used With" `search`, `public-actions`, `diagnostics` but none are declared in `capell.json` `supports`.
- **Marketplace summary (verbatim)**: "Site-scoped Shopify Admin API OAuth, catalog sync, and customer cache foundations for Capell."
- **Screenshots**: **1** (`docs/assets/marketplace/extension-card.jpg`, the extension card only — no real product UI).

---

## 2. Improvements (existing functionality)

- **Done/Shipped: Bulk sync now continues from Start → Poll → Import.** `SyncShopifyProductsAction` dispatches `ContinueShopifyProductBulkSyncAction` after Shopify accepts the bulk operation. The continuation action polls the operation, imports completed JSONL output, and releases unfinished jobs for another poll using `CAPELL_SHOPIFY_COMMERCE_BULK_SYNC_POLL_DELAY_SECONDS`. Evidence: `SyncShopifyProductsActionTest` covers continuation dispatch, completed poll-to-import composition, and unfinished poll release. `src/Actions/Catalog/SyncShopifyProductsAction.php`, `src/Actions/Catalog/ContinueShopifyProductBulkSyncAction.php` — **M**.
- **`FetchShopifyProductAction` runs live GraphQL inside a `DB::transaction`.** The HTTP call to Shopify happens within the DB transaction wrapper (`src/Actions/Catalog/FetchShopifyProductAction.php`), holding a connection/row lock across a network round-trip. Move the GraphQL fetch outside the transaction and only wrap the `updateOrCreate`. **S**.
- **GraphQL throttle pacing only sleeps _after_ a response, never pre-emptively.** `ExecuteShopifyAdminGraphqlAction::paceForThrottleStatus` reads `extensions.cost.throttleStatus` and sleeps post-hoc, but `->retry(2, 250, throw: false)` does not honour Shopify `429`/`Retry-After` headers and there's no respect for REST/GraphQL leaky-bucket on the _next_ call. Add `Retry-After` handling and carry throttle state forward (e.g. cache last `currentlyAvailable` per connection). `src/Actions/Graphql/ExecuteShopifyAdminGraphqlAction.php` — **M**.
- **`ShopifyGraphqlException` swallows partial-data GraphQL responses.** `throw_if(is_array($errors) && $errors !== [], ...)` treats _any_ `errors` array as fatal, but Shopify often returns `errors` alongside usable `data` (e.g. throttle warnings, deprecation notices). This can fail an otherwise-successful sync. Distinguish fatal `errors` from `extensions`/throttle notices. `src/Actions/Graphql/ExecuteShopifyAdminGraphqlAction.php` — **S/M**.
- **Search "live fallback" persists products without pruning, diverging from import semantics.** `SearchShopifyProductsAction` upserts the 20 live results but never prunes stale rows, while `ImportShopifyProductBulkSyncAction::persistProduct` does prune variants. Live-search rows also store no `options`/`raw_snapshot`, so a product first seen via search has a thinner record than one seen via import. Normalise the persist path (shared `persistProduct`) so search and import write identical shapes. `src/Actions/Catalog/SearchShopifyProductsAction.php` vs `ImportShopifyProductBulkSyncAction.php` — **M**.
- **`access_token` is double-protected but error text can still leak it.** Token is `encrypted` cast (`ShopifyConnection::casts`) — good. But `last_sync_error` stores `$throwable->getMessage()` verbatim (`StartShopifyProductBulkSyncAction`, `ImportShopifyProductBulkSyncAction`), and a Guzzle/transfer exception message can embed the request URL with the token header context. The Blade does `Str::limit(strip_tags(...))` but does not scrub secrets. Sanitise messages before persisting. `src/Actions/Catalog/StartShopifyProductBulkSyncAction.php` — **S**.
- **`SyncShopifyProductsCommand` silently targets "latest active" connection with no `--site` scoping.** `ShopifyConnection::query()->where('status','active')->latest('id')->first()` ignores multi-site entirely, so on a multi-store install the command syncs an arbitrary store. Add `--site=` / `--all` options and iterate. `src/Console/Commands/SyncShopifyProductsCommand.php` — **S**.
- **No bounded search-result cap from config; `SearchShopifyProductsAction` hard-codes `first: 20`.** The page passes `limit=20` but the live GraphQL query string is fixed at `first: 20`, so the `$limit` argument is partly cosmetic. Thread the limit through. `src/Actions/Catalog/SearchShopifyProductsAction.php` — **S**.
- **`sync_status` is a free-form string, not an enum.** Values `queued|running|importing|completed|idle|failed|revoked` are scattered as string literals across `SyncShopifyProductsAction`, `StartShopifyProductBulkSyncAction`, `PollShopifyProductBulkSyncAction`, `ImportShopifyProductBulkSyncAction`, and the Filament page's `in_array(...)` checks. A typo in any literal silently breaks the busy-state guard. Introduce a `ShopifySyncStatus` enum (the project convention; `ShopifyConnectionStatus` already does this). Multiple files — **M**.

---

## 3. Missing Features (gaps)

- **Webhooks (table-stakes).** No webhook ingestion at all (confirmed: only doc mentions disclaiming it). The declared capability `shopify-commerce-catalog-sync` is poll/bulk-only, so catalog drift is inevitable between manual syncs. Buyers integrating Shopify expect `products/update`, `products/delete`, `app/uninstalled`, and (for the customer cache) `customers/update` webhooks with HMAC verification. The HMAC primitive (`ValidateShopifyHmacAction`) already exists and could be reused for webhook signature checks. **Table-stakes.**
- **A producer for the customer cache (table-stakes for `shopify-commerce-customer-cache` / `-customer-synced-event`).** `UpsertShopifyCustomerAction` and `ShopifyCustomerSynced` exist and are consumed by `contacts`, but **nothing populates `shopify_customers`** — no command, no webhook, no bulk customer query. The capability is declared but unreachable end-to-end. Either a `customers` bulk sync or a `customers/*` webhook is required to make the capability real. **Table-stakes.**
- **Automated/scheduled sync (table-stakes).** No scheduler entry (`NO_SCHEDULER` confirmed). Catalog freshness depends on an admin clicking "Sync now". A scheduled per-connection sync (with backoff and the existing `WithoutOverlapping` lock) is expected of a "catalog sync" product. **Table-stakes.**
- **`app/uninstalled` handling / token-revocation reconciliation (table-stakes).** If a merchant uninstalls the app in Shopify, the local connection stays `Active` with a now-dead token; sync just errors. No reconciliation marks it `Revoked`. **Table-stakes.**
- **Multi-currency presentment (differentiator).** Variants store a single `price_amount` + `price_currency` (`shopify_product_variants`), taken from `priceV2`. `config.default_currency='USD'` is a blunt fallback. Shopify supports presentment currencies / price ranges; storing only one shop currency limits any storefront consumer. **Differentiator.**
- **Inventory levels (differentiator).** Only `available_for_sale` (bool) is captured. No `inventoryQuantity`, locations, or `inventory_policy`. E-commerce buyers expect stock counts for merchandising. **Differentiator.**
- **Collections / product types / tags (differentiator).** Sync covers products + variants + options + featured image only. No collections, tags, vendor, or product type — all standard merchandising primitives. **Differentiator.**
- **A read API / view-model for consumers (table-stakes given README claims).** README says frontends "should consume synced catalog/customer records through explicit Actions or package-owned view models", but the package exposes no public read Action or DTO for downstream packages — only the admin page uses `SearchShopifyProductsAction`. The promised integration surface doesn't exist yet. **Table-stakes.**
- **Diagnostics health check is a stub (table-stakes).** `ShopifyCommerceHealthCheck` only implements `compatibleCapellApiVersion()`; it performs no actual probe (token validity, last successful sync age, stuck `running` operations). The manifest advertises it as `severity: critical`. **Table-stakes.**
- **Connection test / "verify token" action (differentiator).** No way for an admin to validate that a stored token still works without triggering a full sync. A lightweight `shop { name }` probe would surface dead tokens immediately. **Differentiator.**

---

## 4. Issues / Risks

- **Broken autonomous sync loop (functional bug).** As in §2/§3: `Start` is never followed by `Poll`/`Import` anywhere in the codebase. `OAuth And Catalog Sync > Troubleshooting` even documents "Sync stays running → Run or schedule the poll/import workflow" as expected operator behaviour — i.e. the package ships without the loop closed. `src/Actions/Catalog/SyncShopifyProductsAction.php`, `PollShopifyProductBulkSyncAction.php`, `ImportShopifyProductBulkSyncAction.php`.
- **Network call inside DB transaction.** `FetchShopifyProductAction` holds a transaction around `ExecuteShopifyAdminGraphqlAction::run(...)`. Under load this ties a DB connection to Shopify latency and risks lock timeouts. `src/Actions/Catalog/FetchShopifyProductAction.php`.
- **Thin/missing test coverage in high-risk areas:**
    - **No test for `PollShopifyProductBulkSyncAction` standalone, no test for the full Start→Poll→Import chain.** `SyncShopifyProductsActionTest` tests Start, Poll, and Import as _separate_ fakes; nothing proves they compose, which is exactly where the loop is broken. `tests/Unit/Actions/SyncShopifyProductsActionTest.php`.
    - **No public-output-safety test.** The Capell convention (and this package's own `cacheSafety.sensitiveOutput=true`) requires a test proving anonymous/non-admin HTML never leaks `access_token`, OAuth state, `raw_snapshot`, GraphQL errors, or admin URLs. The doc's "Focused Test Recipes" lists this as a recipe "in the consuming package" but the package ships none itself. No `tests/Arch` directory exists at all. `tests/` (absent).
    - **No test for `ExecuteShopifyAdminGraphqlAction` throttle/`paceForThrottleStatus` or retry behaviour.** The pacing math (`src/Actions/Graphql/ExecuteShopifyAdminGraphqlAction.php`) is untested.
    - **No test for `DisconnectShopifyStoreAction`** (token nulling / status transition) despite it being a security-relevant path. `src/Actions/OAuth/DisconnectShopifyStoreAction.php`.
    - **`UpsertShopifyCustomerAction` is tested in isolation but has no producer**, so its mapping (`moneyAmount`, `marketingState`) is never exercised against a real Shopify payload shape.
- **Performance-budget risk.** Manifest sets `adminQueryBudget: 40`. The Filament page (`ShopifyConnectionPage`) calls `getManageableConnection()` repeatedly — in the Blade it's invoked at top _and_ again inside `hasCachedProducts()`, `isSyncBusy()`, `siteOptions()` — each re-running the connection query and site-scope resolution per render. Combined with `ShopifySiteContext` re-querying `Site` on every call, this can approach the budget on multi-site installs. Memoise the resolved connection on the component. `src/Filament/Pages/ShopifyConnectionPage.php`, `src/Support/ShopifySiteContext.php`.
- **Secret leakage into `last_sync_error`** (see §2): persisted exception messages are not scrubbed. `cacheSafety.sensitiveOutput=true` is declared but enforcement is manual (`strip_tags` + `Str::limit` only) in the Blade. `src/Actions/Catalog/StartShopifyProductBulkSyncAction.php`, `resources/views/filament/pages/connection.blade.php`.
- **OAuth state replay window.** `CreateShopifyOAuthStateAction` does not delete prior unused states for the same `(user, shop)`, so stale rows accumulate until expiry and there's no cleanup command/scheduler for expired `shopify_oauth_states`. Low severity but unbounded growth. `src/Actions/OAuth/CreateShopifyOAuthStateAction.php`.
- **No idempotency key on bulk import.** `ImportShopifyProductBulkSyncAction` re-downloads `bulk_operation_url` and upserts; if run twice concurrently the cache lock (`capell-shopify-commerce.sync.{id}`, 300s block 10s) protects it, but a stale `bulk_operation_url` from a _previous_ operation is not validated against the current `bulk_operation_id` before import. `src/Actions/Catalog/ImportShopifyProductBulkSyncAction.php`.
- **i18n.** Only `resources/lang/en/capell-shopify-commerce.php` exists. All status labels, notifications, and errors are English-only — acceptable for a first-party premium package but a gap for international buyers. `ShopifyGraphqlException` message ("Shopify Admin API request failed.") is hard-coded, not translated.
- **`http_timeout` default of 15s for a bulk JSONL download.** `ImportShopifyProductBulkSyncAction` uses the same `http_timeout` (15s) for downloading a potentially large bulk JSONL file as for a single GraphQL call. Large catalogs will time out. Use a separate, larger streaming timeout for the JSONL sink. `config/capell-shopify-commerce.php`, `src/Actions/Catalog/ImportShopifyProductBulkSyncAction.php`.

---

## 5. Marketplace & Selling

**Critique of current copy.** The `capell.json` `marketplace.summary` ("Site-scoped Shopify Admin API OAuth, catalog sync, and customer cache foundations for Capell.") and the composer `description` ("Shopify Admin API connection and catalog sync foundation for Capell CMS.") are accurate but read like internal architecture notes: "foundations", "site-scoped", "Admin API OAuth" describe _plumbing_, not buyer value. They lead with implementation and the word "foundations" signals "incomplete" — a poor look for a `premium`/`paid` listing. Neither says what an admin can _do_ or why it beats hand-rolling a Shopify integration.

**Improved 1-sentence summary:**

> Connect any Shopify store to Capell in minutes and keep its product catalog and customer data in sync — securely, per site, with no storefront lock-in.

**Improved 3–4 sentence listing description:**

> Shopify Commerce links your Shopify store to Capell with a guided, per-site OAuth connection — your Admin API token is encrypted at rest and never touches content code. It syncs your product catalog (products, variants, options, images, pricing) into fast local tables you can search and reference directly from the admin, and caches customer records for CRM and contact workflows. Designed to coexist with your existing storefront and checkout, it owns the integration layer so your team doesn't have to maintain Shopify API glue. Multi-store ready, permission-gated, and observable through Capell Diagnostics.

(Note: the customer-cache and "sync" claims should only ship in copy once §3's producer/webhook gaps are closed — today they over-promise relative to code.)

**Screenshot/media gaps.** Only one asset, and it's the generic extension card. Add: (1) the connection screen pre-connect (shop-domain entry + site picker), (2) a connected store showing granted scopes + last-sync time + Sync now/Disconnect, (3) the cached-catalog search returning product rows, (4) the Settings panel (API version, scopes, search TTL), (5) the Diagnostics health-check row. A short GIF of the OAuth connect → sync → search flow would convert far better than a static card.

**Pricing/tier/bundle positioning.** `premium` + `commerce` bundle is defensible _once the sync loop and a customer producer ship_; in its current "Start-only, no webhooks, no autonomous sync, stub health check" state it's closer to a `standard` connector than a premium product. The `commerce` bundle and `Capell Commerce` group are right. Cross-sell is under-wired: `capell.json` `supports` is empty, yet README points to `search`, `public-actions`, and `diagnostics`, and the `contacts` package already consumes `ShopifyCustomerSynced`. Declare `supports: ["capell-app/contacts", "capell-app/search", "capell-app/diagnostics"]` so the marketplace can surface the Extension Suite and bundle pricing. A natural upsell: a future `shopify-storefront`/merchandising package that consumes this catalog.

**Top differentiators / value props.** (1) Encrypted, per-site token storage with no credentials in content code; (2) multi-store/site scoping with permission gating (`manage_shopify_commerce`) and global-vs-assigned-site logic baked in (`ShopifySiteContext`); (3) Actions-first design that lets other Capell packages consume catalog/customer data cleanly; (4) "no storefront lock-in" — coexists with any checkout.

**Target buyer persona.** Agencies and in-house teams running **multi-brand/multi-store Capell sites backed by Shopify** who want catalog and customer data available inside the CMS admin (for content, search, CRM) without rebuilding the storefront on Shopify or maintaining bespoke API integrations.

**Marketplace search keywords/tags (8–12):** `shopify`, `shopify admin api`, `ecommerce`, `product catalog sync`, `oauth`, `multi-store`, `commerce integration`, `customer sync`, `crm`, `graphql`, `bulk operations`, `inventory`.

---

## 6. Prioritized Roadmap

| Item                                                                                    | Bucket | Effort | Impact                | Section ref |
| --------------------------------------------------------------------------------------- | ------ | ------ | --------------------- | ----------- |
| Done/Shipped: Close the Start→Poll→Import sync loop (queued chain or scheduler). Evidence: sync start dispatches a continuation job; continuation polls, imports completed operations, and releases unfinished operations for another poll. | Done | M | Critical | §2, §3, §4 |
| Add public-output-safety + full-chain composition tests; add `tests/Arch`               | Now    | M      | High                  | §4          |
| Ship a producer for `shopify_customers` (bulk query or `customers/*` webhook)           | Now    | M      | High                  | §3          |
| Implement the Diagnostics health check probe (token, last-sync age, stuck ops)          | Now    | S      | High                  | §3, §4      |
| Move GraphQL fetch out of `DB::transaction` in `FetchShopifyProductAction`              | Now    | S      | Med                   | §2, §4      |
| Scrub secrets from `last_sync_error` before persisting                                  | Now    | S      | Med (security)        | §2, §4      |
| Webhook ingestion (`products/*`, `app/uninstalled`) reusing `ValidateShopifyHmacAction` | Next   | L      | High                  | §3          |
| Scheduled per-connection sync with backoff + `WithoutOverlapping`                       | Next   | M      | High                  | §3          |
| Introduce `ShopifySyncStatus` enum to replace string literals                           | Next   | M      | Med                   | §2          |
| Memoise resolved connection in `ShopifyConnectionPage`; cut redundant queries           | Next   | S      | Med (perf budget)     | §4          |
| Unify search/import persist path (prune + identical record shape)                       | Next   | M      | Med                   | §2          |
| `Retry-After`/429 handling + carry-forward throttle state in GraphQL action             | Next   | M      | Med                   | §2          |
| Connection "verify token" probe action + admin button                                   | Next   | S      | Med                   | §3          |
| Multi-currency presentment + inventory levels + collections/tags in sync                | Later  | L      | High (differentiator) | §3          |
| Public read API / view-model DTO for downstream consumers                               | Later  | M      | Med                   | §3          |
| Declare `supports` deps; refresh marketplace summary/description/screenshots            | Later  | S      | Med (sales)           | §5          |
| Expired `shopify_oauth_states` cleanup command/scheduler; i18n beyond `en`              | Later  | S      | Low                   | §4          |
