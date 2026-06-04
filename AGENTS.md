# Codex Guidelines for Capell Packages

Optional add-on packages for the Capell CMS. Companion to `capell-app/capell` (`../capell-4`).

Nearly all new Capell packages should be added to this packages repo under `packages/` unless the user explicitly says otherwise.

## Non-negotiables

- `declare(strict_types=1);` in every PHP file.
- PHP 8.3 minimum. Use PHP 8.3 features deliberately, but do not introduce syntax that requires a newer runtime.
- No single-letter or cryptic variable names — closures, migrations, example prose included.
- All closures must declare parameter and return types explicitly.
- No `php artisan` in this repo — use `vendor/bin/pest` directly.
- User-facing strings via `__('capell-...')`. Filament labels via method overrides, never static string properties.

## Frontend Authoring Safety

- Non-admin frontend users must never be able to tell an in-page editor exists. Public Blade, cached HTML, theme output, and frontend assets must not contain authoring HTML, authoring JavaScript, editable markers, model IDs, field paths, labels, permissions, package names, selectors, or signed editor URLs.
- Frontend authoring is a post-load admin feature. The page loads as ordinary public HTML, the browser calls the beacon, and only an authenticated admin beacon response may add edit controls or signed Filament editor URLs.
- Unique/static HTML caching depends on this rule. Cached HTML must stay safe to serve to anonymous visitors, normal signed-in users, admins, crawlers, and static exports.
- When touching frontend packages, theme packages, page cache, or beacon code, add or preserve tests proving anonymous and non-admin responses expose no authoring surface.
- Public Blade views must not execute database queries or lazy-load relationships. Load public render data in controllers, Actions, Livewire components, view composers, Capell payload builders, or explicit view component classes, then pass hydrated data into views. Treat `::query()`, `DB::`, `loadMissing()`, relationship fallback access like `$model->media->first()`, and direct model lookups in public package views as performance bugs.

## CMS Content Boundaries

- Seeders, demo creators, importers, and package setup commands must not store designed page or widget markup in database content fields. Database content may contain portable HTML only: paragraphs, headings, lists, links, emphasis, and other simple semantic tags that can survive theme changes.
- Presentation HTML, layout wrappers, utility classes, Tailwind classes, component-specific classes, and complex structures belong in Blade views, frontend components, renderable elements, or theme assets.
- For Demo Kit specifically, `DemoCreator` should save minimal editable copy and use Capell Layout Builder elements with package Blade views such as `demo-page-content` or package-owned widget views for rendered public presentation.

## Architecture: Actions + Data

**All domain logic in Actions** (`packages/{pkg}/src/Actions/`, suffix `VerbNounAction`):

- Single `handle()` method. Extend `Lorisleiva\Actions\Action` or use `AsObject`.
- Components, resources, commands call `::run()` — no logic inside them.

**Structured data across boundaries** (`packages/{pkg}/src/Data/`, suffix `Data`):

- Inbound: `Data::from($request)`. Outbound: form state, wire-props, view models.
- Model JSON columns cast via `AsData` / `AsDataCollection`. No bare arrays across layers.

**Enums** (`packages/{pkg}/src/Enums/`):

- Backed enums for persisted values. Implement `HasLabel` for Filament options — never inline arrays.
- PascalCase multi-word cases; UPPER_SNAKE_CASE for status flags only.

## Packages

This repo now contains many Capell add-on packages, not just the original short list. Treat `composer.json` and `composer.local.json` PSR-4 autoload entries as the current source of truth for package namespaces and test namespaces.

Common active packages include `layout-builder`, `blog`, `address`, `ai-orchestrator`, `campaign-studio`, `content-sections`, `frontend-authoring`, `login-audit`, `media-ai`, `publishing-studio`, `seo-suite`, `theme-*`, and others under `packages/`.

**Blog requires LayoutBuilder — install LayoutBuilder first.**

## Package boundaries

- **Core must never import plugin classes** — no `use Capell\Blog\...` from Core. Use events or string command names for cross-plugin coordination.
- Packages must not reach into each other's internals (Arch tests enforce this).
- Minimize inter-package dependencies; only add what's truly needed.

## Extension points (use these, don't bypass them)

| Need                                | How                                                                                               |
| ----------------------------------- | ------------------------------------------------------------------------------------------------- |
| Register type / schema / widget     | `CapellCore::registerPageType\|registerSchema\|registerWidget()` in `ServiceProvider::register()` |
| Inject form fields                  | Implement `PageSchemaExtender`, tag with `PageSchemaExtender::TAG`                                |
| Lifecycle events / validation gates | `CapellAdmin::register()` / `subscribe()` / `ValidationSubscriber`                                |
| Inject HTML into Blade              | `RenderHookRegistry::register(RenderHookLocation::X, ...)`                                        |
| Package settings                    | `SettingsSchemaRegistry::register()` + `registerSettingsClass()`                                  |

Auto-discovered: types in `src/Types/`, schemas in `src/Schemas/`, widgets in `src/Widgets/`.

## PublishingStudio / Draftable

Any model in draft/publish must implement `Capell\Core\Contracts\Draftable` and register in the morph map. Reuse `ReplicateModelAction`, `ReplicatePageAction` — don't reinvent replication.

## Database

- Migrations in `packages/{pkg}/database/migrations/`.
- Settings migrations in `database/settings/`, registered in `InstallCommand`, wrapped in `exists()` checks.
- Writes go through Actions, not model methods.

## Testing

- Test actions directly: `MyAction::run($input)` — not through HTTP.
- Named test doubles must be real PSR-4 fixture classes under `tests/Fixtures` or `tests/Packages/Fixtures`, not classes declared inside Pest files. See `docs/writing-tests.md`.
- Start with the narrowest useful Pest command, usually one test file or one package: `vendor/bin/pest packages/{package}/tests --configuration=phpunit.xml`.
- Run single package: `vendor/bin/pest packages/layout-builder/tests --configuration=phpunit.xml`
- Minimum 80% coverage. Full suite: `composer test`.

## Composer local overlay

- Always run Composer commands through the local overlay in this repo: `COMPOSER=composer.local.json composer ...`. Do not run plain `composer ...` unless Ben explicitly asks for the public package manifest.
- When changing `composer.json`, update `composer.local.json` in the same change, and vice versa. Keep dependencies, scripts, autoload, autoload-dev, repositories, and package metadata aligned unless there is a deliberate local-only overlay difference.
- Common issue: if a package test case class is not found, check `composer.local.json` as well as `composer.json`. The local overlay often needs matching `autoload` and `autoload-dev` PSR-4 entries for package namespaces, then regenerate with `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.
- For local development, `composer.local.json` is often the faster daily-driver overlay because it path-links sibling Capell packages and may use fail-fast test settings.

## Commands

| Command                                    | Purpose                                                                             |
| ------------------------------------------ | ----------------------------------------------------------------------------------- |
| `COMPOSER=composer.local.json composer test`          | Pest tests (parallel)                                                               |
| `COMPOSER=composer.local.json composer preflight`     | Changed-file formatting plus full PHPStan via `../capell-4/scripts/lint-changed.sh` |
| `COMPOSER=composer.local.json composer preflight:all` | Rector + full Pint + PHPStan + tests                                                |
| `COMPOSER=composer.local.json composer lint`          | Pint only                                                                           |
| `COMPOSER=composer.local.json composer analyze`       | PHPStan only                                                                        |
| `COMPOSER=composer.local.json composer prepare`       | Seed demo workbench                                                                 |
| `COMPOSER=composer.local.json composer serve`         | Build + serve localhost:8000                                                        |
| `vendor/bin/pest packages/{package}/tests` | Single package tests                                                                |

## Package Workbench Browser QA

- `COMPOSER=composer.local.json composer serve` in this repo starts an Orchestra Testbench package workbench, not a full installed Capell app.
- Do not assume `http://127.0.0.1:8000/admin` exists here. In this packages repo it commonly returns 404 because the Filament admin panel page routes are not registered for the generic workbench surface.
- Before browser-testing admin UI from this repo, run `COMPOSER=composer.local.json composer build` and then verify available routes with `vendor/bin/testbench route:list --no-ansi`. Only open `/admin` if the route list actually contains the Filament admin panel routes.
- If the route list only shows support routes such as `admin/api/page-tree`, `admin/avatar/{initials}.svg`, `admin/theme-preview/{theme}/{site}/{page}`, `filament-peek/preview`, and the catch-all frontend route, there is no usable admin page surface in this workbench. Do not keep probing URLs.
- `_workbench/login/{userId}` also requires a migrated and seeded workbench database. If it fails with `no such table: users` or `_workbench/user` returns `[]`, browser login is not available from the current workbench state.
- For package UI changes, prefer focused Livewire/Pest coverage against the package test case first. Use the sibling full Capell app (`../capell-4`) or the Docker harness when real browser QA of the Filament admin panel is required.

## Docker Harness

- Use the Docker harness when you need a clean, repeatable agent environment or when host PHP/Node/Composer state is suspect.
- It is the supported local package-development runtime for Capell package work. Do not recreate old `capell-app.test` host routing unless Ben explicitly asks for it.
- Start it with `./capell up`; run checks with `./capell test`, `./capell analyze`, or a narrower package/file Pest command through `./capell pest ...`.
- For one-off tool checks without starting services, use `./capell run <command>`. Use `./capell compose <args>` when direct Docker Compose access is needed.
- The harness mounts this repo at `/home/capell/current` and the sibling Capell package directory at `/home/capell/packages/capell`, so local path repositories work when `capell-4` and `capell-packages-4` are beside each other.
- The image file is `.docker/Dockerfile`. It may use a Debian-based PHP toolchain internally, but agents should treat that as an implementation detail and avoid adding distro-specific behaviour unless the toolchain requires it.

## Agent Speed

- Keep task branches focused. This repo can accumulate very large dirty trees across many packages, and that slows agents because they must preserve unrelated user work.
- Prefer package-level or file-level Pest runs during implementation; reserve `COMPOSER=composer.local.json composer test`, `COMPOSER=composer.local.json composer analyze`, and `COMPOSER=composer.local.json composer preflight:all` for final verification.
- Avoid broad repo exploration when the target package or failing command is known. Start from the package, test, or class named in the request.
- Exclude heavy local paths from Spotlight/antivirus/indexing where practical: `vendor`, `node_modules`, `.git`, `storage`, `coverage`, `.phpunit.cache`, and framework/build caches.

## Git

1. `composer test` — 100% pass before committing.
2. `composer preflight` — clean before committing.
3. Verify in demo workbench (`composer serve`).
4. Commit immediately after task completion.
5. Branch naming: `feat/`, `fix/`, `docs/`, `chore/`. Target: `4.x`.
