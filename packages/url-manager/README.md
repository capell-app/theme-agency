# Capell URL Manager

Capell URL Manager owns managed redirect rules and 404 opportunity tracking.

The package is intentionally action-driven:

- `RedirectRule` stores normalized source and target URLs, match type, status code, active state, hit count, and last hit timestamp.
- `RedirectHit` stores per-hit evidence without storing raw IP addresses or user agents.
- `NotFoundOpportunity` stores repeated 404 paths and a suggested target URL when one can be inferred.
- `ConvertNotFoundOpportunityToRedirectAction` creates a redirect from a reviewed 404 opportunity and marks it converted.
- `RecordChangedUrlRedirectAction` accepts previous/current page paths and creates an exact redirect only when the normalized URL changed. Parent page moves also create a prefix redirect so child paths continue to resolve.
- Import/export actions use CSV-compatible rows for admin import/export workflows, including redirect priority.
- `RedirectRulesPage` and `NotFoundOpportunitiesPage` expose package-owned admin tables. The 404 table calls `ConvertNotFoundOpportunityToRedirectAction` for conversions.
- `BuildCanonicalUrlAction` applies the package canonical URL policy for scheme, host, path case, trailing slash handling, and tracking-query stripping.
- `PruneRedirectHitsAction` and `url-manager:prune-hits` prune old per-hit rows according to the configured retention window.

## Redirect policy

Managed source paths are normalized to lowercase paths without query strings before hashing. Query strings are preserved only at response time when `preserve_query` is enabled, so `/old-page?utm_source=x` still matches a `/old-page` rule.

Absolute targets are allowed only for configured hosts and, by default, the host from `app.url`. This keeps CSV imports and lower-trust admin workflows from creating open redirects to arbitrary domains. Exact redirect chains are collapsed at write time, and cyclic chains are rejected before save. Regex sources are validated at write time and bounded by `capell-url-manager.redirects.regex.max_pattern_length`; runtime regex resolution checks only the configured number of ordered rules.

Redirect priority controls overlapping prefix and regex matches. Higher priority wins before fallback ordering, then longer prefix paths win inside the same priority.

## Integration points

- Frontend/Core request resolution can use `UrlManagerRedirectResolver`, which decorates Core's existing `RedirectResolver` binding and falls back to URL Manager rules when Core `PageUrl` redirects do not resolve.
- Core `PageUrlChanged` events are handled by `RecordRedirectForChangedPageUrl`, which writes the previous URL into URL Manager when the normalized source and target differ.
- Public 404 handling is captured by `RecordNotFoundOpportunityMiddleware` through the frontend middleware registry. Hosts that do not use the frontend registry can call `RecordNotFoundOpportunityAction` directly with the normalized path, site ID, language ID, and lightweight context. Do not render any URL Manager metadata into public HTML.
- Review/admin flows should call `ConvertNotFoundOpportunityToRedirectAction` after an editor chooses a target URL.
- SEO Suite broken URL surfaces can read URL Manager data by using `NotFoundOpportunity` for 404 candidates and `ExportRedirectRulesAction` for redirect coverage. Existing SEO Suite `BrokenLink` rows can be imported by calling `ImportSeoSuiteBrokenLinksAction`, then `BuildNotFoundRedirectSuggestionsAction`.

## Configuration

`config/capell-url-manager.php` controls:

- allowed redirect status codes;
- absolute target host allowlist;
- regex pattern length and runtime rule scan limits;
- deferred hit recording and hit retention;
- ignored paths for 404 capture;
- canonical URL scheme, host, slash, lowercase, and query stripping policy.

Hit recording is deferred with an application terminating callback by default so public redirects do not wait for the `redirect_hits` insert and rule counter update before returning the redirect response.
