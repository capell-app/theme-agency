# Claude Code Guidelines for Capell Packages

Optional add-on packages for the Capell CMS. Companion to `capell-app/capell` (`../capell-4`).

Frontend authoring safety is mandatory: non-admin frontend users must never receive editor HTML, JavaScript, metadata, markers, model IDs, field paths, selectors, or signed URLs. The page must load as ordinary public HTML; in-page authoring is discovered and rendered only after an authenticated admin beacon response.

## Non-negotiables

- `declare(strict_types=1);` in every PHP file.
- PHP 8.4 minimum. Use PHP 8.4 features deliberately, but do not introduce syntax that requires a newer runtime.
- No single-letter or cryptic variable names — closures, migrations, example prose included.
- All closures must declare parameter and return types explicitly.
- No `php artisan` in this repo — use `vendor/bin/pest` directly.
- User-facing strings via `__('capell-...')`. Filament labels via method overrides, never static string properties.

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

This repo contains many Capell add-on packages. Treat `composer.json`, `composer.local.json`, package `composer.json` files, and package `capell.json` manifests as the current source of truth for namespaces, dependencies, surfaces, and tests.

Common active packages include `layout-builder`, `blog`, `address`, `ai-orchestrator`, `campaign-studio`, `content-sections`, `frontend-authoring`, `html-cache`, `login-audit`, `media-ai`, `publishing-studio`, `seo-suite`, `theme-*`, and others under `packages/`.

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
- Run single package: `vendor/bin/pest packages/layout-builder/tests --configuration=phpunit.xml`
- Minimum 90% coverage. Full suite: `COMPOSER=composer.local.json composer test`.
- Start with the narrowest useful package or file-level Pest command, then broaden only when the change touches shared behaviour, public rendering, installation, or cross-package contracts.

## Composer local overlay

- Always run Composer commands through the local overlay in this repo: `COMPOSER=composer.local.json composer ...`. Do not run plain `composer ...` unless you explicitly need the public package manifest.
- Common issue: if a package test case class is not found, check `composer.local.json` as well as `composer.json`. The local overlay often needs matching `autoload` and `autoload-dev` PSR-4 entries for package namespaces, then regenerate with `COMPOSER=composer.local.json composer dump-autoload --no-scripts`.
- When changing `composer.json`, update `composer.local.json` in the same change unless the difference is deliberately local-only.

### Bootstrapping a worktree's `vendor/`

Do not whole-directory-symlink `vendor/` entries from the primary checkout into a worktree, even excluding `vendor/composer` and `vendor/autoload.php`. Several packages (`pestphp/pest`, `rector/rector`, `brianium/paratest`) resolve their own install root at runtime via `__DIR__`/`dirname(__DIR__, N)` rather than through Composer's generated `autoload_real.php` `$baseDir`. PHP resolves `__DIR__` through symlinks to the real filesystem path, so a symlinked `vendor/pestphp` run from the worktree actually bootstraps the **primary checkout's** `vendor/composer/*` autoloader — causing "Cannot redeclare class" fatals whenever a test file exists in both checkouts.

Use one of instead:

1. Run a full `COMPOSER=composer.local.json composer install` in the worktree. Slower, but fully correct and isolated.
2. If speed matters, only symlink individual package directories confirmed to be leaf packages with no self-locating runtime logic (no `__DIR__`/`dirname(__DIR__, N)` install-root resolution). Always leave `vendor/bin` as a real directory, never a whole-directory symlink, so bin proxies regenerate fresh and scoped to the worktree.

Never run plain `composer` (without `COMPOSER=composer.local.json`) from inside a worktree that has any `vendor/*` symlinks pointing at the primary checkout — it can write straight through those symlinks into the primary checkout's real `vendor/bin/*` proxies and `vendor/composer/*` files, silently corrupting the primary checkout's local vendor state (its git-tracked state is unaffected, since `vendor/` is gitignored).

## Commands

| Command                                               | Purpose                                                                         |
| ----------------------------------------------------- | ------------------------------------------------------------------------------- |
| `COMPOSER=composer.local.json composer test`          | Pest tests (parallel)                                                           |
| `COMPOSER=composer.local.json composer preflight`     | Composer path check plus changed-file formatting                                |
| `COMPOSER=composer.local.json composer preflight:all` | Rector + full Pint + Prettier + ESLint + PHPStan + audits + tests               |
| `COMPOSER=composer.local.json composer lint`          | Pint only                                                                       |
| `COMPOSER=composer.local.json composer analyze`       | PHPStan only                                                                    |
| `COMPOSER=composer.local.json composer prepare`       | Prepare the Testbench package workbench                                         |
| `COMPOSER=composer.local.json composer serve`         | Build + serve the Orchestra Testbench workbench                                 |
| `vendor/bin/pest packages/{package}/tests`            | Single package tests; add `--configuration=phpunit.xml` for consistency with CI |

`COMPOSER=composer.local.json composer serve` starts an Orchestra Testbench package workbench, not a full installed Capell app. Do not assume `/admin` exists unless `vendor/bin/testbench route:list --no-ansi` shows the relevant Filament routes.

## Git

1. Run the narrowest meaningful Pest command for the changed package or file.
2. Run `COMPOSER=composer.local.json composer preflight` before committing focused work.
3. Run `COMPOSER=composer.local.json composer test` and `COMPOSER=composer.local.json composer preflight:all` before committing broad, shared, installation, public rendering, or release-ready changes.
4. Verify browser-visible or public rendering changes in the relevant workbench or full Capell app when available.
5. Stage only task-related files and commit immediately after verified completion.
6. Branch naming: `feat/`, `fix/`, `docs/`, `chore/`. Target: `4.x`.
