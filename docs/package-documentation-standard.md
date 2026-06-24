# Package Documentation Standard

Capell package docs have two jobs: help a developer change the package safely, and help an owner understand why the package belongs in a Capell build. Keep both jobs visible.

## Reader Split

Every package needs both a non-technical overview and a developer deep dive. They can live in one README when the package is small, but the distinction should be obvious.

| Reader                         | What they need                                                                                                                                                                                        |
| ------------------------------ | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Site owner, buyer, or operator | What workflow the package adds, which bundle it belongs to, which screens or public routes appear, and what operational risk it reduces.                                                              |
| Editor or admin user           | What they can create, review, approve, publish, inspect, recover, or hand off without custom development. Editors read `docs/overview.md` and, for Tier 1 and Tier 2 packages, `docs/admin-guide.md`. |
| Developer                      | Real package boundaries: Actions, Data objects, providers, routes, models, settings, extension points, tests, and unsafe integration paths to avoid.                                                  |

Use this split when writing examples:

```md
For teams: Customer Portal gives signed-in customers one dashboard for support requests, preferences, and package-contributed self-service links.

For developers: Packages contribute dashboard cards through `PortalDashboardItemRegistry`; billing, document, event, and access packages keep their own domain actions.
```

Avoid examples that only restate the package name:

```md
Weak: Automation Studio automates workflows.
Better: Automation Studio listens for package events such as form submissions and access approvals, then records each matched rule/action run in `automation_runs`.
```

## README Shape

Every package README should include these sections, in this order when
practical. The current strict audit checks the heading names below.

| Section                 | Purpose                                                                                                   |
| ----------------------- | --------------------------------------------------------------------------------------------------------- |
| Opening H1              | Title the page with the package or plugin name.                                                           |
| `What This Plugin Adds` | Practical job, Capell surface, admin/editor outcome, and status such as Available or Pipeline.            |
| `Why It Matters`        | Separate developer impact from team/editor/operator value.                                                |
| `Screens And Workflow`  | Planned or existing screenshots, diagrams, admin screens, frontend output, and workflow steps.            |
| `Technical Shape`       | Providers, config, migrations, models, resources, routes, Livewire, policies, events, jobs, views, cache. |
| `Data Model`            | Tables, relationships, core records, migration impact, deletion/retention, or no-schema statement.        |
| `Install Impact`        | Admin navigation, permissions, public routes, database changes, config, queues, schedules, cache paths.   |
| `Common Pitfalls`       | Practical issues developers and operators should check.                                                   |
| `Troubleshooting`       | Optional symptom table for packages with routes, commands, jobs, schema, health checks, or external APIs. |
| `Quick Start`           | Three steps only: install, run setup, open/verify the new surface.                                        |
| `Next Steps`            | Links to configuration, screenshots, ERD/schema, extension points, troubleshooting, related package docs. |

Use the shared language guides before rewriting package docs:

- [Capell Content Language Plan](CONTENT_LANGUAGE_PLAN.md)
- [Capell Documentation Design System](DESIGN_SYSTEM.md)
- [Capell and package ERD notes](erd/capell-and-package-erds.md)

## Admin Documentation

The README is the developer and owner front door. The admin docs are the editor and operator front door, written in plain language for someone who runs the feature but does not change the code. Follow the admin voice in the [Content Language Plan](CONTENT_LANGUAGE_PLAN.md#admin-voice-overviewmd-and-admin-guidemd): real on-screen labels in bold, the user's goal first, the nav path named, no class/table/Action names, no `composer`/`artisan`, no fenced code, and no non-ASCII punctuation.

### Admin-first `overview.md`

When a package ships an admin-first overview, it adds a hand-authored fragment at `docs/overview.admin.md` (admin sections only, no H1, no footer). The generator wraps that fragment into `docs/overview.md` with the package H1 and a footer linking the admin guide and developer docs. The developer-shaped content is not lost: it stays in the generated `README.md`. The strict audit then validates `overview.md` in admin mode (H1 required; the developer headings `What This Plugin Adds`, `Technical Shape`, `Data Model`, `Install Impact`, and `Quick Start` are forbidden; install commands are forbidden). Keep the fragment short (about 40 to 70 lines) using plain headings such as:

| Section                | Purpose                                                                                |
| ---------------------- | -------------------------------------------------------------------------------------- |
| `What it does for you` | Two to four sentences, concrete, in editor language. No class names.                   |
| `Your screens`         | The actual admin nav items and pages, in plain words.                                  |
| `What you can do`      | Three to six verbs the user performs: create, schedule, approve, preview, export.      |
| `Where to find it`     | The nav path, for example "Content > Articles" or "Settings > SEO".                    |
| `Good to know`         | One to three gotchas in plain language, such as "Drafts stay private until published". |

Themes and behind-the-scenes packages use their own short headings (see the tier table) rather than this exact set.

### `admin-guide.md` (Tier 1 and Tier 2)

A longer how-to with three parts in one document:

| Section                              | Purpose                                                                                                                                           |
| ------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------- |
| Using `<Package>` (editor how-to)    | Numbered tasks with real button labels, each titled by the user's goal ("How to schedule an article for later").                                  |
| Rolling out `<Package>` (for owners) | What to turn on first, what to add when needed (a Need to Enable table), what not to enable yet, and who does what (role to first useful screen). |
| Troubleshooting for editors          | A What you see / What it means / What to do table in plain language.                                                                              |

### Coverage tiers

| Tier                       | Treatment                                                                                                                                                  |
| -------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1 (full guide)             | Admin-first `overview.md` plus a full `admin-guide.md` (how-to, adoption, troubleshooting).                                                                |
| 2 (operator note)          | Admin-first `overview.md` plus a lighter `admin-guide.md` (what you are looking at, what to do when X, settings and retention).                            |
| 3 (theme template)         | One shared admin-first `overview.md` shape per theme: what this theme gives you, how to use it, what it adds, good to know. No per-theme `admin-guide.md`. |
| 4 (behind-the-scenes note) | Admin-first `overview.md` only: what it does, do I need to do anything, where it shows up. No `admin-guide.md`.                                            |

## Workflow Value Rules

Write from the reader's job:

- For owners: explain what capability this adds in a Capell site, dashboard, workflow, or package bundle.
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
COMPOSER=composer.local.json composer docs:rewrite-readmes:check
COMPOSER=composer.local.json composer docs:check:strict
COMPOSER=composer.local.json composer security:surface-report:check
```

When a package README documents behavior covered by tests, run the package-local Pest command or the specific test file named in the README.
