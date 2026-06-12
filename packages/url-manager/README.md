# Capell URL Manager

URL Manager owns managed redirect rules, canonical URL policy, redirect hit evidence, and 404 opportunity tracking for Capell sites.

## At A Glance

| Field            | Value                                                   |
| ---------------- | ------------------------------------------------------- |
| Composer package | `capell-app/url-manager`                                |
| Namespace        | `Capell\UrlManager`                                     |
| Product group    | Capell Growth/Search                                    |
| Surfaces         | Admin, frontend middleware, console                     |
| Provider         | `Capell\UrlManager\Providers\UrlManagerServiceProvider` |
| Admin pages      | `RedirectRulesPage`, `NotFoundOpportunitiesPage`        |
| Command          | `url-manager:prune-hits`                                |
| Config           | `config/capell-url-manager.php`                         |

## Why It Helps Your Capell Workflow

Owners get a controlled redirect and 404 repair workflow instead of hidden web-server rewrites. Editors can review recurring 404s, convert them into redirects, import/export rules, and keep moved page URLs working.

Developers get Actions for redirect resolution, import preview, canonical URL building, changed-page URL redirects, hit pruning, and SEO Suite broken-link import without coupling frontend routing to admin tables.

## What It Adds

- `RedirectRule`, `RedirectHit`, and `NotFoundOpportunity` models.
- Admin pages for redirect rule management and 404 opportunity review.
- Exact, prefix, and regex redirect resolution with priority support.
- CSV import/export and import preview Actions.
- Changed page URL listener that records redirects for previous page paths.
- 404 capture middleware through the frontend middleware registry.
- Canonical URL policy for scheme, host, path case, trailing slash, and tracking query stripping.
- `PruneRedirectHitsAction` and `url-manager:prune-hits` for retention.

## Boundaries

URL Manager owns managed redirects and 404 opportunities. Core page URL redirects still resolve first where Core provides them; URL Manager decorates/falls back rather than replacing Core routing.

Public output must not render URL Manager metadata, admin URLs, import details, or redirect hit evidence. Absolute redirect targets are allowed only for configured hosts to avoid open redirects.

## Runtime Surface

- Provider: `src/Providers/UrlManagerServiceProvider.php`
- Admin pages: `src/Filament/Pages/`
- Middleware: `src/Http/Middleware/`
- Redirect resolver: `src/Support/Redirects/`
- Actions: `src/Actions/`
- Data objects: `src/Data/`
- Listener: `src/Listeners/RecordRedirectForChangedPageUrl.php`
- Command: `src/Console/Commands/PruneRedirectHitsCommand.php`
- Tests: `packages/url-manager/tests`

## Docs

- [Package docs](docs/README.md)
- [Overview](docs/overview.md)
- [Improvement plan](docs/improvement-plan.md)
- [Screenshots contract](docs/screenshots.json)

## Testing

```bash
vendor/bin/pest packages/url-manager/tests --configuration=phpunit.xml
```

## Troubleshooting

| Symptom                       | Likely cause                                                            | Check                                                                             | Fix                                                                                   |
| ----------------------------- | ----------------------------------------------------------------------- | --------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------- |
| Redirect does not fire        | Rule is inactive, lower priority, invalid regex, or Core resolved first | Check `redirect_rules` status, match type, priority, and normalized source        | Activate/fix the rule and test `ResolveRedirectRuleAction`                            |
| CSV import rejects rows       | Target host, status code, regex, or loop validation failed              | Preview with `PreviewRedirectRulesImportAction`                                   | Fix the CSV row and keep external targets inside the allowlist                        |
| 404 opportunities are missing | Frontend middleware registry is not running the recorder                | Check `RecordNotFoundOpportunityMiddleware` registration and ignored paths config | Enable the frontend middleware contribution or call the Action from the host 404 flow |
