# Package Documentation Standard

Capell package docs have two jobs: help a developer change the package safely, and help an owner understand why the package belongs in a Capell build. Keep both jobs visible.

## README Shape

Every package README should include these sections, in this order when practical:

| Section                             | Purpose                                                                                                   |
| ----------------------------------- | --------------------------------------------------------------------------------------------------------- |
| Opening sentence                    | Name the package and the workflow it improves.                                                            |
| `At A Glance`                       | Composer package, namespace, surfaces, service providers, dependencies.                                   |
| `Why It Helps Your Capell Workflow` | Concrete business/editor/developer/operator value. This is product copy, but it must be grounded in code. |
| `What It Adds`                      | User-facing and runtime features the package owns.                                                        |
| `Boundaries` or `Maintenance Notes` | What the package owns, what it does not own, and unsafe integration paths to avoid.                       |
| `Runtime Surface` or `Code Map`     | Providers, routes, commands, Actions, Data, models, settings, jobs, views, resources, and tests.          |
| `Docs`                              | Links to all package docs and important neighboring docs.                                                 |
| `Testing` or `Verification`         | Smallest useful package test command and any focused commands for risky areas.                            |
| `Troubleshooting`                   | Symptom table for packages with install, queue, cache, external API, or public output failure modes.      |

## Workflow Value Rules

Write from the reader's job:

- For owners: explain what capability this unlocks in a Capell site, dashboard, workflow, or package bundle.
- For editors: explain what becomes easier to create, approve, reuse, publish, inspect, or recover.
- For developers: explain what extension point, Action, Data object, or provider surface saves custom code.
- For operators: explain what gets monitored, cached, audited, migrated, protected, or debugged.

Avoid vague claims. Use specific outcomes that the code supports. For example:

| Weak                          | Better                                                                                                                  |
| ----------------------------- | ----------------------------------------------------------------------------------------------------------------------- |
| `Adds generic content tools.` | `Adds article, archive, and tag page types so teams can publish editorial content without custom page schemas.`         |
| `Improves performance.`       | `Indexes cached model URLs and exposes admin cache widgets so operators can see stale HTML and refresh affected pages.` |
| `Integrates with Shopify.`    | `Stores site-scoped Shopify OAuth connections and syncs products into local tables for admin-side catalog search.`      |

## Runtime Surface Checklist

Check these source locations before documenting a package:

| Surface                               | Source of truth                                                         |
| ------------------------------------- | ----------------------------------------------------------------------- |
| Composer name and namespace           | `packages/<package>/composer.json`                                      |
| Product group, capabilities, surfaces | `packages/<package>/capell.json`                                        |
| Providers                             | `composer.json.extra.laravel.providers`, `src/Providers`                |
| Config and env vars                   | `config/*.php`, settings classes, tests                                 |
| Routes                                | `routes/*.php`                                                          |
| Commands                              | `src/Console/Commands`                                                  |
| Actions                               | `src/Actions`                                                           |
| Data objects                          | `src/Data`                                                              |
| Models and tables                     | `src/Models`, `database/migrations`                                     |
| Settings                              | `src/Settings`, `database/settings`, Filament settings schemas          |
| Admin surfaces                        | `src/Filament`, registered `CapellAdmin` calls                          |
| Frontend surfaces                     | `resources/views`, `src/Livewire`, render hooks, frontend providers     |
| Extension points                      | contracts, registries, tags, provider registration calls                |
| Tests                                 | `packages/<package>/tests`, shared package tests under `tests/Packages` |

## Extension Point Documentation

For every package-owned extension point, document:

| Field                | Required answer                                                                                                              |
| -------------------- | ---------------------------------------------------------------------------------------------------------------------------- |
| What to implement    | Interface, class, enum, registry payload, or Action call.                                                                    |
| Where to register it | Provider method, registry call, config key, or container tag.                                                                |
| When it runs         | Boot, admin render, public request, queue job, install command, sync command, publish flow, cache invalidation.              |
| Safe fallback        | What happens when no extension is registered or the dependency is not installed.                                             |
| Focused test         | Provider/registry test, Action test, admin render test, public output safety test, cache invalidation test, or failure test. |

Do not invent extension points to make docs feel complete. If the package only exposes Actions today, say that and name the Actions.

## Troubleshooting Standard

Use symptom tables:

| Symptom                                     | Likely cause                       | Check                                                              | Fix                                     |
| ------------------------------------------- | ---------------------------------- | ------------------------------------------------------------------ | --------------------------------------- |
| Concrete failure visible to a user/operator | Specific code/config/runtime cause | Exact command, table, route, cache key, log message, or config key | Smallest safe fix and verification step |

Include exact names when relevant: route names, command signatures, table names, cache key prefixes, queue/job names, config keys, settings keys, and logs.

## Safety Rules

- Public Blade, cached HTML, and theme output must not expose authoring state, package internals, model ids, field paths, permissions, signed admin URLs, tokens, OAuth state, or editor selectors.
- Public Blade views must not query the database or lazy-load relationships. Document render data loaders, Actions, Livewire components, or view components instead.
- Package docs must not advise replacing core classes, bypassing registries, writing provider-side data mutations, or storing designed page/widget markup in seed content.
- Core must not import optional package classes. Cross-package docs should describe events, Actions, registries, string command names, or documented package APIs.

## Review Checklist

| Reviewer       | Questions to answer                                                                                         |
| -------------- | ----------------------------------------------------------------------------------------------------------- |
| Developer      | Are all classes, routes, commands, config keys, env vars, tables, settings, Actions, and examples real?     |
| Content writer | Does the README explain a concrete workflow benefit in plain language without filler or unsupported claims? |
| Owner          | Can a buyer or site owner tell why this package matters and which package bundle it belongs to?             |
| Operator       | Are install, queue, cache, migration, external API, and rollback risks visible where relevant?              |
| Security/cache | Does the doc preserve public-output safety and avoid leaking admin/editor internals?                        |
| QA             | Do local Markdown links resolve, stale package names disappear, and focused package tests pass?             |

## Verification Commands

Use the narrowest useful checks while editing:

```bash
vendor/bin/pest packages/<package>/tests --configuration=phpunit.xml
```

Run repository-level docs checks before finishing a broad documentation pass:

```bash
find packages -maxdepth 2 -name README.md -print
rg -n "$STALE_DOC_PATTERN" packages/*/README.md packages/*/docs docs
```

When a package README documents behavior covered by tests, run the package-local Pest command or the specific test file named in the README.
