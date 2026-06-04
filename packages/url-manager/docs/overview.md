# URL Manager Overview

URL Manager is Capell's package-owned redirect and broken-link remediation layer. It stores redirect rules, records lightweight hit evidence, captures repeated 404 opportunities, imports SEO Suite broken-link rows, and exposes admin pages for editors to review and convert redirect candidates.

## Runtime Flow

`UrlManagerRedirectResolver` decorates Core's `RedirectResolver` contract. Core decisions win first; if Core has no redirect decision and the current `PageUrl` is not a real page, URL Manager resolves active exact, prefix, and regex rules for the current site and language. Exact rules match by a normalized path hash. Prefix rules use bounded candidate paths and priority ordering. Regex rules are validated at write time, ordered by priority, and capped by `capell-url-manager.redirects.regex.max_rules_checked`.

Hit recording is deferred by default. The redirect response can return immediately, then the application terminating callback writes `url_manager_redirect_hits` and updates the rule counter. Set `capell-url-manager.hit_recording.defer` to `false` only for diagnostics or tests that need synchronous writes.

## Redirect Safety

Source URLs are lowercased and stripped of query strings before matching. Query strings are only reattached by `UrlManagerRedirectResolver` when `preserve_query` is enabled and the target URL has no query string of its own.

Absolute targets are constrained to configured hosts plus the `app.url` host by default. Configure extra hosts through `capell-url-manager.redirects.absolute_target_allowed_hosts`. Exact redirect chains are collapsed to their final active target when a rule is saved, and loops or cycles are rejected before persistence. Regex sources are bounded by maximum pattern length and validated before a rule is saved.

## Changed URLs And 404 Capture

`RecordRedirectForChangedPageUrl` listens for Core `PageUrlChanged` events. It creates an exact redirect for the changed path and, for parent moves, a prefix redirect so child URLs continue to resolve.

`RecordNotFoundOpportunityMiddleware` registers with Capell Frontend's middleware registry and records public 404 responses as `NotFoundOpportunity` rows. Ignored path prefixes, such as admin and Livewire paths, live in `capell-url-manager.not_found.ignored_path_prefixes`.

## Canonical URLs

`BuildCanonicalUrlAction` provides the package canonical URL policy. It can force a scheme or host, lowercase paths, add or remove trailing slashes, and strip configured tracking query parameters. The action does not render tags by itself; consumers can call it when they need URL Manager's canonical policy.

## Admin And CSV

The Redirect Rules admin table supports create, edit, enable, disable, delete, bulk lifecycle actions, CSV import/export, and template download. CSV rows include `priority` so migration exports preserve overlap ordering. The 404 Opportunities page lets editors ignore, reopen, or convert opportunities into redirect rules.

## Retention

`PruneRedirectHitsAction` deletes old per-hit evidence according to `capell-url-manager.hit_recording.retention_days`. Operators can run `url-manager:prune-hits` or pass `--days=` for a one-off retention window.
