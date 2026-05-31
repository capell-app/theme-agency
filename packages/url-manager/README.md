# Capell URL Manager

Capell URL Manager owns managed redirect rules and 404 opportunity tracking.

This first slice is intentionally package-local and action-driven:

- `RedirectRule` stores normalized source and target URLs, match type, status code, active state, hit count, and last hit timestamp.
- `RedirectHit` stores per-hit evidence without storing raw IP addresses or user agents.
- `NotFoundOpportunity` stores repeated 404 paths and a suggested target URL when one can be inferred.
- `ConvertNotFoundOpportunityToRedirectAction` creates a redirect from a reviewed 404 opportunity and marks it converted.
- `RecordChangedUrlRedirectAction` accepts previous/current page paths and creates an exact redirect only when the normalized URL changed.
- Import/export actions use simple array rows so CSV/JSON UI surfaces can be added later without changing the domain model.
- `RedirectRulesPage` and `NotFoundOpportunitiesPage` expose package-owned admin tables. The 404 table calls `ConvertNotFoundOpportunityToRedirectAction` for conversions.

## Integration points

- Frontend/Core request resolution can use `UrlManagerRedirectResolver`, which decorates Core's existing `RedirectResolver` binding and falls back to URL Manager rules when Core `PageUrl` redirects do not resolve.
- Core `PageUrlChanged` events are handled by `RecordRedirectForChangedPageUrl`, which writes the previous URL into URL Manager when the normalized source and target differ.
- Public 404 handling should call `RecordNotFoundOpportunityAction` with the normalized path, site ID, language ID, and lightweight context. Do not render any URL Manager metadata into public HTML.
- Review/admin flows should call `ConvertNotFoundOpportunityToRedirectAction` after an editor chooses a target URL.
- SEO Suite broken URL surfaces can read URL Manager data by using `NotFoundOpportunity` for 404 candidates and `ExportRedirectRulesAction` for redirect coverage. Existing SEO Suite `BrokenLink` rows can be imported by calling `ImportSeoSuiteBrokenLinksAction`, then `BuildNotFoundRedirectSuggestionsAction`.
