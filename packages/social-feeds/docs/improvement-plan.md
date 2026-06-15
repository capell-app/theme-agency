# Social Feeds - Improvement & Growth Plan

> Package: capell-app/social-feeds · Kind: package · Tier: premium · Product group: Capell Growth · Bundle: growth · Status: Active

## 1. Snapshot

Social Feeds is a premium growth package that contributes a `social-feed` Block Library frontend component backed by local cached feed items. It owns connection, item, and OAuth state tables, provider contracts/registry, RSS and configured-feed provider classes, render Data objects, a Blade block view, sync/upsert Actions, Filament connection/item resources, a `capell:social-feeds:sync` command, an opt-in hourly schedule, and screenshot evidence for a seeded frontend carousel, RSS sync state, and provider registry. The strongest shipped path is RSS/Atom: the driver validates absolute HTTP(S) URLs, resolves DNS, blocks private hosts by default, pins cURL resolution, disables redirects, parses XML with `LIBXML_NONET`, sanitizes public links/media URLs, and stores cached items for query-backed rendering. Remaining gaps are product depth rather than package reachability: there is no package Settings surface, no OAuth callback flow, no cached item pruning policy, no moderation workflow, and no native provider API clients beyond RSS-compatible feeds.

## 2. Improvements (existing functionality)

1. **Build the missing admin connection/item surface or remove the admin claim.** `capell.json` declares `admin`, `social-feeds-admin`, and admin screenshots, but no `src/Filament` resources exist. Add resources for `SocialFeedConnection` and `SocialFeedItem`, including provider credential schema rendering, manual sync action, latest sync status/error, and read-only cached item review. Evidence: no `src/Filament`, README docs gap, `capell.json surfaces/capabilities`. - **M** - **Done 2026-06-14:** connection and cached item Filament resources now ship with provider credentials, sync state, manual sync, and read-only item review.

2. **Add a scheduled/command sync path.** `SyncSocialFeedConnectionAction` is tested and has `WithoutOverlapping` middleware, but no command, scheduler, or job dispatches it for connected feeds. Add `capell:social-feeds:sync` with per-connection/all options and optional schedule config, or intentionally document host-owned sync. Evidence: `SyncSocialFeedConnectionAction`, `config/capell-social-feeds.php`, no `src/Console` or `src/Jobs`. - **M** - **Done 2026-06-14:** `capell:social-feeds:sync` now syncs all connected feeds or one connection, and optional hourly scheduling is controlled by `sync_schedule_enabled`.

3. **Reconcile provider marketing with provider reality.** RSS is fully implemented. TikTok, YouTube, Bluesky, Instagram, Facebook, LinkedIn, and X extend `AbstractConfiguredProvider`, which requires `feed_url` and delegates to the RSS driver until native API bridges exist. Update marketplace/docs to say "RSS-compatible feeds for these providers today" or implement native provider clients. Evidence: `AbstractConfiguredProvider`, provider classes, manifest marketplace summary. - **S** - **Done 2026-06-14:** README, overview, and marketplace summary now describe RSS-compatible feeds and provider registry extensibility instead of native social API depth.

4. **Clean manifest traceability drift.** `contributionTraceability.deferredContributions` still listed shipped contribution types after admin resources, the frontend widget, and the scheduled sync path landed. Update traceability when package surfaces ship or are intentionally deferred. Evidence: `capell.json contributionTraceability`. - **S** - **Done 2026-06-15:** admin, frontend, model, and scheduled-job contributions are declared in `capell.json`; the package has no Settings class, so `database.settings`, `settings`, and deferred contributions now all consistently report no settings surface.

5. **Add render budget and no-query Blade coverage.** `FetchSocialFeedRenderDataAction` queries local items and the Blade view renders prepared Data; that matches the public Blade safety rule. Add a zero-query Blade render test after hydrating `SocialFeedRenderData` and a bounded query test for the Action. Evidence: `FetchSocialFeedRenderDataAction`, `resources/views/blocks/social-feed.blade.php`, `SocialFeedBlockRenderTest`. - **S**

6. **Improve public image accessibility.** The block renders feed media images with empty `alt`, which is defensible for decorative thumbnails but weak when captions are hidden or the image is the main content. Add a config option/derived alt text from caption/provider where appropriate, while preserving decorative alt for repeated previews. Evidence: `resources/views/blocks/social-feed.blade.php`. - **S**

## 3. Missing Features (gaps)

Capabilities declared: social feeds, admin, frontend, provider registry, and cached rendering.

- **Connection lifecycle is not operator-ready.** There is no admin creation/edit/testing workflow for credentials, feed URLs, provider selection, sync errors, or stale connections.
- **OAuth states exist but no OAuth route/controller is visible.** The `social_feed_oauth_states` table implies future OAuth support, but no callback/start flow ships.
- **No pruning/retention policy for cached items.** Cached feed items can grow indefinitely unless upsert logic replaces or the host cleans them.
- **No moderation/approval flow.** Public render uses latest cached items directly; there is no "hide this post", pinned posts, or approve-before-display path.
- **No provider-native API depth.** API key/handle schema exists, but configured providers currently require `feed_url` and use RSS semantics.

## 4. Issues / Risks

1. **Done/Shipped: advertised admin capability has concrete admin resources.** Buyers/operators can manage connections, inspect cached items, and run manual syncs from Capell admin. - **P2**

2. **Done/Shipped: cached rendering has a command and opt-in schedule.** `capell:social-feeds:sync` can sync all connected feeds or one connection, and `capell-social-feeds.sync_schedule_enabled` enables hourly scheduled sync. Remaining recommendation: add stale-sync health diagnostics. - **P2**

3. **Important gap: provider list can over-sell native integrations.** Provider names are registered, but most providers are feed-url wrappers. Recommended fix: align docs/copy or implement native API clients. - **P2**

4. **Done/Shipped: manifest traceability matches shipped contribution state.** Deferred contributions are empty, scheduled-job metadata is explicit, and settings metadata accurately says no package Settings class ships. - **P3**

5. **Done/Shipped: docs explain admin, command, schedule, and settings state.** README and overview list the Filament resources, command, scheduled-job contribution, opt-in config flag, and absence of package settings. - **P3**

## 5. Marketplace & Positioning

Social Feeds belongs in `Capell Growth` because it turns external social proof into controlled, cached website content. For owners, the value is keeping campaign or social proof sections fresh without embedding third-party scripts into the public page. For editors, the future value is choosing approved feeds/widgets and reviewing cached posts. For developers, the differentiator is a provider registry and local render data that keeps public HTML cache-safe.

**Current summary:** "Place branded, cached social feeds anywhere in Capell with RSS, TikTok, YouTube, Bluesky, and custom provider support."

**Safer near-term summary:** "Place cached RSS-compatible social feeds in Capell pages with a provider registry and clean public rendering that avoids third-party embed scripts."

**Target description after admin/sync work:** "Social Feeds lets teams connect approved RSS-compatible feeds, sync posts into local Capell tables, and render branded list, carousel, slideshow, or paginated widgets through Block Library. Public pages read cached items instead of loading third-party embed scripts, keeping output fast and cache-safe. Developers can register custom providers through a package contract, while operators can review sync state and cached posts in admin. Native provider API bridges can layer on later without changing the public rendering contract."

**Media status:** The existing runner-backed frontend carousel, RSS sync, and provider registry screenshots are useful and already promoted. After admin resources land, recapture a true admin connection/sync screen rather than a package-state fixture.

**Cross-sell:** Block Library is the required rendering surface. Frontend Optimizer benefits from cached/no-third-party public output. Privacy Center can document social content/privacy boundaries. Site Monitor can monitor feed endpoints. AI Orchestrator could summarize social posts later.

**Keywords/tags:** `social-feeds`, `rss`, `social-proof`, `cached-rendering`, `block-library`, `carousel`, `provider-registry`, `frontend`, `growth`, `widgets`.

## 6. Prioritized Roadmap

| Item                                                                            | Bucket | Effort | Impact | Section ref |
| ------------------------------------------------------------------------------- | ------ | ------ | ------ | ----------- |
| Add Social Feed connection and cached item Filament resources with manual sync  | Done   | M      | High   | §2.1, §4.1  |
| Add `capell:social-feeds:sync` command plus optional scheduled/queued sync      | Done   | M      | High   | §2.2, §4.2  |
| Align provider docs/marketplace copy with RSS-compatible implementation reality | Done   | S      | Medium | §2.3, §4.3  |
| Fix `contributionTraceability` for shipped/deferred surfaces                    | Done   | S      | Medium | §2.4        |
| Align scheduled-job/settings metadata and docs                                  | Done   | S      | Medium | §2.4, §4    |
| Add stale-sync health diagnostics                                               | Next   | S      | Medium | §2.2, §4.2  |
| Add render budget/no-query public Blade coverage                                | Next   | S      | Medium | §2.5        |
| Add cached item pruning/retention                                               | Next   | M      | Medium | §3          |
| Add image alt strategy for media-heavy feeds                                    | Next   | S      | Low    | §2.6        |
| Add moderation/approval/pinning controls                                        | Later  | M      | Medium | §3          |
| Add OAuth flow or remove OAuth state table from active docs until used          | Later  | L      | Medium | §3          |
| Add native provider clients for the highest-value networks                      | Later  | L      | High   | §3, §5      |

## 7. Verification

Focused implementation verification passed:

```bash
vendor/bin/pest packages/social-feeds/tests/Feature/RssFeedSyncTest.php packages/social-feeds/tests/Feature/SocialFeedBlockRenderTest.php --configuration=phpunit.xml
```

Full package verification passed:

```bash
vendor/bin/pest packages/social-feeds/tests --configuration=phpunit.xml
```

Result: 19 tests, 105 assertions passed.

## 8. Completion Checklist

- [x] Package plan created from current code, manifest, docs, screenshots, and tests.
- [x] Comprehensive local review pass completed for provider registry, RSS sync safety, public render path, manifest, docs, and tests.
- [x] Capell audience pass completed for site owners, editors/operators, and package developers.
- [x] Approved implementation slices shipped.
- [x] Focused Social Feeds verification passed.
- [x] Package tests passed.
- [ ] Repo preflight passed for changed files.
