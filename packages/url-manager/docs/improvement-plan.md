# URL Manager — Improvement & Growth Plan
> Package: capell-app/url-manager · Kind: package · Tier: premium · Product group: Capell Search & SEO · Bundle: search-seo · Status: Draft

## 1. Snapshot

URL Manager is an action-driven redirect engine for Capell. It surfaces two admin Filament pages (`RedirectRulesPage`, `NotFoundOpportunitiesPage`) and decorates Core's `RedirectResolver` binding on the public request path via `UrlManagerRedirectResolver`. Core domain logic lives in `src/Actions` — `ResolveRedirectRuleAction` (exact/prefix/regex matching + hit recording), `UpsertRedirectRuleAction`, `RecordNotFoundOpportunityAction`, `ConvertNotFoundOpportunityToRedirectAction`, the CSV import/export family, `BuildNotFoundRedirectSuggestionsAction`, and `ImportSeoSuiteBrokenLinksAction`. Three models/tables back it: `url_manager_redirect_rules`, `url_manager_redirect_hits`, `url_manager_not_found_opportunities`. Deps: `capell-app/core`, `capell-app/admin`, `lorisleiva/laravel-actions`, `spatie/laravel-data`; soft-supports `capell-app/seo-suite`.

Current marketplace summary (verbatim): *"Manage redirects, preserve moved URLs, track redirect hits, and turn repeated 404s or SEO Suite broken URL findings into redirect opportunities."* Manifest declares `marketplace.screenshots: []` — **mismatch**: `docs/screenshots/` actually contains 8 PNGs (4 workflows × light/dark), so the marketplace media is present on disk but unreferenced in the manifest.

## 2. Improvements (existing functionality)

- **Lowercase / canonicalise the matched path in normalisation** — why: `NormalizeManagedUrlAction::handle()` trims and rtrim-slashes but does **not** lowercase, so `/Old-Page` and `/old-page` hash to different `source_hash` values and miss the same exact rule; case is a common redirect-miss source. — `src/Actions/NormalizeManagedUrlAction.php` — S
- **Strip the query string before hashing for match (keep it only for `preserve_query` reattachment)** — why: `pathFromUrl()` appends `?query` into the returned path, so the `source_hash` for an exact rule includes the live query string; `/foo?utm=x` will never match a `/foo` rule. The query belongs in the resolver's `targetUrl()` reattach step, not in the match key. — `src/Actions/NormalizeManagedUrlAction.php`, consumed by `ResolveRedirectRuleAction::findExactRule()` — M
- **Make hot-path hit recording asynchronous / deferrable** — why: `ResolveRedirectRuleAction` defaults `$recordHit = true` and the resolver calls it without overriding, so every public redirect does an INSERT into `redirect_hits` + an UPDATE of `redirect_rules` (then `->refresh()`) synchronously before the 301 is issued. Defer to a queued job or `terminating()` callback, or aggregate counts. — `src/Actions/ResolveRedirectRuleAction.php`, `src/Support/Redirects/UrlManagerRedirectResolver.php` — M
- **Avoid `->get()->first(...)` full-table scans for prefix/regex** — why: `findPrefixRule()` and `findRegexRule()` pull **all** prefix/regex rows for the site into memory and filter in PHP on every request. For prefix, push the longest-match into SQL with indexed candidate filtering; for regex, cap and order by a priority column. — `src/Actions/ResolveRedirectRuleAction.php` — M
- **Add a `priority`/ordering column to `redirect_rules`** — why: regex/prefix resolution order is currently implicit (`LENGTH(source_url) DESC` for prefix, arbitrary for regex). Editors cannot control which of two overlapping rules wins. — `database/migrations/2026_05_31_000001_create_url_manager_redirect_rules_table.php`, resolver, form — M
- **Form-level validation parity with the Action** — why: `RedirectRuleForm` only enforces `required` + `maxLength(2048)`; the real rules (valid regex, status-code allowlist, no self-redirect) live in `UpsertRedirectRuleAction` and only surface as thrown `InvalidArgumentException`s, producing poor admin UX. Mirror them as Filament rules/`->rule()` closures. — `src/Filament/Pages/Schemas/RedirectRuleForm.php` — S
- **Index `redirect_hits.hit_at` / rule FK for analytics queries** — why: the table stores per-hit rows but there's no documented retention or aggregation; admin hit analytics will table-scan. Add indexes and a prune command. — `database/migrations/2026_05_31_000002_create_url_manager_redirect_hits_table.php` — S

## 3. Missing Features (gaps)

Mapped to `capabilities[]` in `capell.json`:

- **Redirect loop & chain detection** (no capability today; table-stakes) — nothing detects `A→B` + `B→A`, nor `A→B→C` chains that should collapse to `A→C`. `UpsertRedirectRuleAction` only blocks the trivial `source === target` self-redirect. A real product needs cycle detection on save and chain flattening. **Differentiator.**
- **404 capture is unwired in-repo** — `not-found-opportunities` capability depends entirely on a host/Core 404 handler calling `RecordNotFoundOpportunityAction`; a monorepo grep finds **zero** callers outside this package's own src/tests. Ship a Core exception-handler hook, middleware, or documented integration so the capability is reachable out of the box. — ties to `not-found-opportunities`, `not-found-redirect-suggestions`
- **Resolver auto-wiring verification** — `frontend-redirect-resolver` binds `RedirectResolver::class` only if Core's interface exists; grep of `packages/core`/`packages/frontend` shows no `RedirectResolver`/`RedirectDecisionData` usage, so whether Core actually invokes the bound resolver on the request path is unverified. Confirm the consumer exists or the capability is dead.
- **Auto slug-change redirects beyond exact** — `RecordChangedUrlRedirectAction` (via `PageUrlChanged`) only creates **exact** redirects. Moving a parent page does not cascade to child URLs. Add prefix-redirect generation on subtree moves. — extends `changed-url-redirect-detection`
- **Canonical URL management** — categories list `seo` but there is no canonical-tag or trailing-slash/host canonicalisation surface; this is a natural adjacency for an SEO-bundle redirect tool. **Differentiator vs table-stakes.**
- **Bulk operations in admin** — import/export Actions exist (`ImportRedirectRulesAction`, `ExportRedirectRulesAction`, CSV builders) but there is no evidence of bulk enable/disable/delete table actions wired into `RedirectRulesTable`. — extends `redirect-import-export`
- **301 vs 302 guidance / defaults per source** — status code is a free Select (301/302/307/308) with no guidance; auto-suggest 301 for permanent moves, 302 for temporary, and warn on 302 for slug-change redirects. — table-stakes polish
- **Open-redirect allowlist for absolute targets** — `UpsertRedirectRuleAction` normalises target with `allowAbsolute: true` and no host allowlist; needed before exposing import to lower-trust roles (see §4).
- **Health check implementation** (advertised, not built — see §4).

## 4. Issues / Risks

- **Health check is a stub** — `UrlManagerHealthCheck` implements only `compatibleCapellApiVersion(): '^4.0'`; it performs **no** check. The manifest advertises `"URL Manager redirect tables, actions, and provider metadata are discoverable"` at `severity: critical`. This is a manifest-vs-code mismatch that will report healthy regardless of state. — `src/Health/UrlManagerHealthCheck.php`, `capell.json` healthChecks
- **Open-redirect surface** — `UpsertRedirectRuleAction::handle()` calls `NormalizeManagedUrlAction::run($data->targetUrl, allowAbsolute: true)`, which accepts any `http(s)://host` target with no allowlist. Permission `Manage:RedirectRules` plus CSV import (`ImportRedirectRulesAction`) means a rule can 301 visitors to an arbitrary external host. Constrain absolute targets to a configurable host allowlist. — `src/Actions/UpsertRedirectRuleAction.php`, `src/Actions/NormalizeManagedUrlAction.php`
- **Untrusted regex executed on the public hot path** — `ResolveRedirectRuleAction::targetUrl()` / `findRegexRule()` run `@preg_match`/`@preg_replace` with admin-stored patterns on every request, error-suppressed. A pathological pattern (catastrophic backtracking) is a ReDoS vector on the request path. Validate/limit patterns at write time and consider `preg_match` with `pcre.backtrack_limit` guards. — `src/Actions/ResolveRedirectRuleAction.php`
- **No loop detection** — see §3; a self-referential or cyclic rule set can produce redirect loops served to the public.
- **Synchronous double-write per redirect** — see §2; with `performance.frontendRenderBudgetMs: 0` and `cacheSafety.cacheable: false` declared in the manifest, every redirect is uncached and adds two DB writes before responding. Cite `capell.json` → `performance`. The `cacheTags: []` / `invalidationSources: []` mean rule changes have no cache-invalidation story documented.
- **No result caching of resolved rules** — exact-match lookups by `source_hash` are cheap but prefix/regex resolution is O(rows) in PHP per request with no memoisation. Manifest `adminQueryBudget: 40` covers admin, but there is no frontend query budget enforced.
- **Test gaps** — `tests/Unit` covers: exact/prefix/regex resolve + hit count, upsert validation, CSV import/preview, opportunity convert, the `PageUrlChanged` listener, and manifest requirements. **Not covered:** the `UrlManagerRedirectResolver` decorator fallback-chaining (Core decision wins vs URL Manager fallback), `preserve_query` reattachment edge cases, open-redirect rejection, loop/cycle rejection, `ImportSeoSuiteBrokenLinksAction` (guarded by `Schema::hasTable('broken_links')`), and the health check. Add anonymous/non-admin output-safety tests per Capell public-output rules. — `tests/Unit/Actions/*`, `tests/Unit/Filament/RedirectRulesPageTest.php`
- **i18n** — labels are translated (`generic.php`, `table.php`), but Action-thrown exception messages (`'A redirect cannot point to itself.'`, `'Redirect status code must be one of…'`) are hard-coded English. — `src/Actions/UpsertRedirectRuleAction.php`, `src/Actions/PreviewRedirectRulesImportAction.php`
- **No config file** — there is no `config/` directory; status-code allowlist, host allowlist, regex limits, and hit-retention are hard-coded, so operators cannot tune them.

## 5. Marketplace & Selling

**Critique.** The manifest `summary` is accurate but reads as a feature list, not a value statement, and buries the strongest hook (recovering lost SEO traffic). The composer `description` — *"Managed redirects and URL opportunity tracking for Capell"* — is generic and omits the 404→redirect and SEO Suite integration that differentiate it. `marketplace.screenshots` is empty despite 8 screenshots existing on disk — a pure config miss that loses the visual sell.

**Improved 1-sentence summary:** *Stop losing traffic to broken links — manage redirects, auto-preserve moved page URLs, and turn repeated 404s into recovered SEO.*

**Improved 3–4 sentence description:** *URL Manager keeps your site's link equity intact when pages move or get renamed. It auto-creates redirects when a page URL changes, resolves exact, prefix, and regex rules on the live request, and tracks hit counts so you can see which redirects matter. Repeated 404s and SEO Suite broken-link findings surface as one-click redirect opportunities, with CSV import/export for bulk migrations. Built for editors and SEO teams who need redirect hygiene without touching server config.*

**Screenshot/media gaps:** populate `marketplace.screenshots` with the existing `docs/screenshots/*` set (create/edit form, import workflow, export workflow, health snapshot). Add two missing shots: the 404 Opportunities table with a convert action, and a redirect-hit analytics view (once §2 analytics lands).

**Pricing / tier / bundle positioning:** `premium` tier inside the `search-seo` bundle is right. Cross-sell is the lever: it `supports` `seo-suite` and consumes its `BrokenLink` rows, so position as the *remediation* half of an SEO loop (SEO Suite finds, URL Manager fixes). Strong fit with the **migration-assistant** and **wordpress-importer** Extension Suites — bulk redirect import is exactly what platform migrations need; surface `ImportRedirectRulesAction` as their redirect-mapping target. Bundle it as the default redirect layer whenever migration-assistant is purchased.

**Differentiators / value props / target buyer:** differentiator is the closed loop — automatic slug-change capture + 404 mining + SEO Suite import feeding suggested redirects, all inside the CMS. Target buyer: SEO managers and content teams on multi-site Capell installs who run frequent content reorganisations and migrations.

**Keywords/tags:** `redirects`, `301 redirect`, `404 management`, `url management`, `seo`, `link equity`, `slug change`, `redirect import`, `broken links`, `site migration`, `regex redirect`, `multi-site`.

## 6. Prioritized Roadmap

| Item | Bucket | Effort | Impact | Section ref |
|------|--------|--------|--------|-------------|
| Implement real `UrlManagerHealthCheck` (tables/actions/provider discoverable) | Now | S | High | §4 |
| Populate `marketplace.screenshots` from existing `docs/screenshots/*` | Now | S | High | §5 |
| Strip query from match key; lowercase normalisation | Now | M | High | §2 |
| Open-redirect host allowlist for absolute targets | Now | M | High | §3, §4 |
| Defer hot-path hit recording (queue / terminating) | Now | M | High | §2, §4 |
| Validate & bound regex patterns at write time (ReDoS) | Next | M | High | §4 |
| Redirect loop & chain detection on save | Next | M | High | §3 |
| Wire 404 capture into Core handler / documented hook | Next | M | High | §3 |
| Verify/confirm Core invokes the bound `RedirectResolver` | Next | S | High | §3, §4 |
| Add `priority` ordering column + resolver/form support | Next | M | Med | §2, §3 |
| Replace `get()->first()` prefix/regex scans with SQL | Next | M | Med | §2, §4 |
| Tests: resolver fallback, preserve_query, open-redirect, loops, SEO import | Next | M | High | §4 |
| Add `config/` for status codes, host allowlist, regex/retention | Later | S | Med | §4 |
| Prefix-cascade auto-redirects on page-subtree moves | Later | M | Med | §3 |
| Canonical URL management surface | Later | L | Med | §3 |
| Translate Action exception messages | Later | S | Low | §4 |
