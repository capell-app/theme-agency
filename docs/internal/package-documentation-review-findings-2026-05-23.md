# Package Documentation Review Findings - 2026-05-23

This file records the review and verification pass for the package documentation rebuild.

## Scope Reviewed

- Added a shared package docs standard at `docs/package-documentation-standard.md`.
- Added the package documentation audit matrix at `docs/internal/package-documentation-review-2026-05-23.md`.
- Added package-local docs indexes at `packages/*/docs/README.md` for all 47 packages.
- Added `Why It Helps Your Capell Workflow` and `Best Used With` sections to all 47 package READMEs.
- Added/kept Shopify Commerce README and workflow docs from the first pass.
- Replaced stale legacy extension headings with current package headings.
- Replaced package-catalog language where older wording implied stale distribution concepts.
- Removed broken Markdown image links for generated screenshots that are not currently committed.

## Developer Review

| Check                      | Result | Notes                                                                                                                             |
| -------------------------- | ------ | --------------------------------------------------------------------------------------------------------------------------------- |
| Package coverage           | Pass   | 47 composer package directories; 47 package READMEs; 47 package `docs/README.md` index files.                                     |
| README workflow section    | Pass   | Every package README has `## Why It Helps Your Capell Workflow`.                                                                  |
| README docs index link     | Pass   | Every package README links to `docs/README.md`.                                                                                   |
| Local Markdown links       | Pass   | Checked 340 Markdown files; all 1,110 local targets resolve.                                                                      |
| Stale headings             | Pass   | No remaining legacy extension headings in package docs.                                                                           |
| Generated screenshot links | Fixed  | Broken image links in SEO Suite, Campaign Studio, and Login Audit overview docs were converted to text targets until files exist. |

## Content Writer Review

| Check                       | Result | Notes                                                                                                                                                        |
| --------------------------- | ------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Reader job is visible       | Pass   | README value sections now speak to owner, editor, developer, or operator jobs.                                                                               |
| Benefits are concrete       | Pass   | Value statements name actual package-owned capabilities such as article publishing, cache indexing, OAuth sync, dashboard widgets, and public-output safety. |
| No unsupported superlatives | Pass   | Stale-language scan found no broad marketing filler or unsupported broad claims in package docs.                                                             |
| Related reading             | Pass   | Every README has neighboring package links; every package docs index has `Read Next` links.                                                                  |

## Owner And Operator Review

| Check                        | Result  | Notes                                                                                                                                                                                                                     |
| ---------------------------- | ------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Install reason visible early | Pass    | The workflow value section appears near the top of each README before deep implementation details.                                                                                                                        |
| Package bundle context       | Pass    | Cross-package docs index and review matrix group packages by product workflow.                                                                                                                                            |
| Operational risk visible     | Partial | Existing package docs already cover many config, queue, cache, and external API risks. This pass improves navigation and value framing, but each complex package can still benefit from deeper package-specific runbooks. |
| Cross-package workflow links | Pass    | Growth, search, publishing, operations, theme, and foundation packages now point to adjacent packages.                                                                                                                    |

## Security And Cache Review

| Check                         | Result | Notes                                                                                                                                                                         |
| ----------------------------- | ------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Public-output safety language | Pass   | Value and safety sections preserve the rule that authoring state, tokens, admin URLs, editor selectors, model ids, and internal metadata must not leak to public output.      |
| Frontend/cache packages       | Pass   | Frontend Authoring, HTML Cache, API, Block Library, Layout Builder, Foundation Theme, and Shopify Commerce all include explicit safety boundaries or safe ownership language. |
| Unsafe extension advice       | Pass   | No new docs advise replacing core classes, bypassing registries, or storing designed layout markup in database content fields.                                                |

## Verification Commands

| Command                                                                                                                                                                                                                                                                              | Result                                                                                                                                                                                                                         |
| ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `python3 <local markdown link checker>`                                                                                                                                                                                                                                              | Pass: checked 340 Markdown files; all local links exist across 1,110 local targets.                                                                                                                                            |
| `rg -n "$DOC_STALE_PATTERN" packages/*/README.md packages/*/docs docs/README.md docs/package-documentation-standard.md docs/internal/package-documentation-review-2026-05-23.md docs/internal/package-documentation-review-findings-2026-05-23.md docs/internal/package-doc-gaps.md` | Pass: no matches.                                                                                                                                                                                                              |
| `xargs npx prettier --check < /tmp/capell-docs-owned-md-files.txt`                                                                                                                                                                                                                   | Pass: all matched files use Prettier code style.                                                                                                                                                                               |
| `vendor/bin/pest packages/shopify-commerce/tests --configuration=phpunit.xml`                                                                                                                                                                                                        | Pass: 36 tests, 92 assertions.                                                                                                                                                                                                 |
| `vendor/bin/pest packages/frontend-authoring/tests --configuration=phpunit.xml`                                                                                                                                                                                                      | Pass: 34 tests, 228 assertions.                                                                                                                                                                                                |
| `vendor/bin/pest packages/html-cache/tests --configuration=phpunit.xml`                                                                                                                                                                                                              | Fail: 4 failed, 101 passed. Failures are runtime/test-fixture issues around anonymous users missing `hasRole()`, settings casts, and maintenance cache state; no Markdown files are involved.                                  |
| `vendor/bin/pest packages/publishing-studio/tests --configuration=phpunit.xml`                                                                                                                                                                                                       | Fail: 55 failed, 1 skipped, 507 passed. Failures are runtime/test-fixture issues around page translation URL listeners, settings casts, dashboard widgets, Filament actions, and bridge tests; no Markdown files are involved. |

## Findings

| Severity | Finding                                                                                    | Evidence                                                                                                                                                                                                                                   | Recommendation                                                                                                                                                  |
| -------- | ------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| P2       | Broad package tests currently fail outside the docs changes.                               | HTML Cache and Publishing Studio package test suites failed in PHP runtime/test fixture paths.                                                                                                                                             | Track separately from this docs pass; do not block documentation merge solely on these existing runtime failures unless the branch requires full package green. |
| P3       | Some complex package docs still deserve deeper package-specific runbooks beyond this pass. | README value sections and docs indexes are now present everywhere, but packages such as Publishing Studio, SEO Suite, Access Gate, Migration Assistant, and Email Studio have enough surface area for deeper symptom tables per subsystem. | Use `docs/internal/package-documentation-review-2026-05-23.md` as the backlog for the next package-by-package deepening pass.                                   |

## Review Outcome

The documentation navigation and workflow-value requirements are met for every package. The remaining risk is depth, not coverage: complex packages now have a clearer entry point, but their detailed troubleshooting pages can continue to improve as package behavior changes.
