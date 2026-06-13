# Package Screenshot Automation

Deployment can generate package screenshots from the committed screenshot manifests. Each package owns a `packages/{package}/docs/screenshots.json` file, and the aggregate manifest is [package-screenshot-manifest.json](package-screenshot-manifest.json).

## How Deployment Should Use It

1. Install the package and its declared dependencies from `capell.json`.
2. If the screenshot manifest declares `composerRequires`, Composer require every listed package before seeding demo data. This is required for cross-package screenshots such as frontend authoring, where the editable page depends on core, admin, frontend, a theme beacon, and the authoring package.
3. Run migrations, then package install, setup, and demo commands from `capell.json` in that order when present. `commands.demoParams` declares the prompt/options the runner must provide so captures use populated package demo data.
4. Authenticate as an admin user with the required role or permission.
5. Resolve `admin-surface` targets through Filament resources or pages.
6. Resolve `frontend-url` targets through seeded demo routes or package route names.
7. Capture desktop and mobile screenshots. Use `scripts/capture-admin-screenshots.mjs` for admin surfaces when a manifest declares it, and keep `SCREENSHOT_FULL_PAGE=true` so long admin form builders are captured in full.
8. Execute any `browserTests` declared by the package manifest. These tests must run against the installed browser surface, not only server-rendered Blade.
9. Write files to `packages/{package}/docs/screenshots`.

## Manifest Contract

- `package`: package slug.
- `composerName`: Composer package name where available.
- `composerRequires`: optional list of Composer packages the screenshot/demo environment must require before capture.
- `outputDirectory`: deployment output path.
- `entries[].surface`: `admin` or `frontend`.
- `entries[].targetType`: `admin-surface` or `frontend-url`.
- `entries[].target`: resource/page class name when known, otherwise deployment resolves from seeded content.
- `entries[].user`: optional screenshot actor. Set `false` for anonymous frontend captures; `null` is treated as the runner default user.
- `entries[].docsPage`: optional markdown page where the screenshot is referenced.
- `entries[].output`: optional concrete output file when the package commits a docs screenshot or requires a stable filename.
- `browserTests`: optional browser scenario contracts the deployment runner must execute after package installation.
- `runner`: optional screenshot runner path, such as `scripts/capture-admin-screenshots.mjs`.
- `capture.fullPage`: when true, the runner should capture the full scrollable page instead of only the viewport.
- Aggregate package entries include `installCommand`, `setupCommand`, `demoCommand`, `demoParams`, and `doctorCommand` copied from each package `capell.json`. The screenshot runner should treat those fields as the authoritative demo lifecycle before capture.

## Marketplace Visual Assets

Package marketplace screenshots declared in `capell.json` must point at committed files under `docs/assets/marketplace/`. The package-side validator checks those paths so marketplace pages cannot ship with broken gallery images.

## Theme Demo Layout Fixture Policy

Routine Pest runs must not overwrite committed PNG fixtures. By default, theme demo layout screenshot tests write generated images to `storage/framework/testing/theme-demo-layout-screenshots/screenshots`, compare the generated filenames for the current run, and leave `tests/Packages/Fixtures/theme-demo-layout-screenshots` untouched.

To refresh intentional baselines, run the target test with `CAPELL_REFRESH_THEME_SCREENSHOT_FIXTURES=1`, visually review the changed PNGs, then stage only the accepted fixture changes.

## Notes

The package repo does not need to run a browser during docs generation. It commits the contract that the demo/docs deployment can consume after package installation.

## Local Persistent Prepared App

For local package screenshot QA, reuse the prepared app at `/Users/ben/Sites/packages/capell/capell-screenshot-runner` instead of recreating it for every run. The runner app is intentionally persistent, so do not reset or rebuild it unless the task explicitly requires a fresh state.

Use base URLs without `/admin`; the screenshot runner appends `/admin/login` when it authenticates:

```bash
export CAPELL_SCREENSHOT_RUNNER_PATH=/Users/ben/Sites/packages/capell/capell-screenshot-runner
export CAPELL_ADMIN_URL=http://127.0.0.1:8145
export CAPELL_FRONTEND_URL=http://127.0.0.1:8145
export CAPELL_SCREENSHOT_ADMIN_EMAIL=test@example.com
export CAPELL_SCREENSHOT_ADMIN_PASSWORD=password
export CACHE_STORE=array
```

Start the prepared app once from the runner checkout:

```bash
cd /Users/ben/Sites/packages/capell/capell-screenshot-runner
php artisan serve --host=127.0.0.1 --port=8145
```

Then capture a package from this repo without preparing, reseeding, or rebuilding the app:

```bash
scripts/local-package-screenshots.sh --package layout-builder --reuse-app
```

`--reuse-app` also clears `storage/framework/cache/data` in the prepared app before capture. That removes stale serialized DTOs from previous package/code revisions while preserving seeded content and compiled assets.

If the persistent database is missing public page data, seed it once from the runner checkout before using `--reuse-app`:

```bash
php -d memory_limit=512M artisan capell:admin-demo \
  --url=http://127.0.0.1:8145 \
  --user=test@example.com \
  --sites=capell-screenshots \
  --languages=en \
  --page-count=4 \
  --seed=8145 \
  --reset
```

## GitHub Automation

`Package Screenshots` validates screenshot manifests on pull requests that touch `packages/**/docs/screenshots.json`. It rebuilds `docs/package-screenshot-manifest.json`, validates package manifests, and fails when the aggregate manifest is not committed.

After changes land on `4.x`, the same workflow captures screenshots for packages whose `docs/screenshots.json` changed and uploads the generated files as a workflow artifact. The runner receives the package lifecycle commands through `docs/package-screenshot-manifest.json`, so package demos should be installed before admin or frontend screenshots are taken. The workflow can also be run manually for a package:

```bash
gh workflow run screenshots.yml \
  -f package=blog \
  -f package_repository=capell-app/blog \
  -f package_ref=main
```

Split package repositories can call the workflow with `workflow_call` and pass their repository name, package slug, and ref. The workflow checks out `capell-app/capell`, `capell-app/capell-packages`, and `capell-app/capell-screenshot-runner` from GitHub inside the runner workspace, then overlays the split package into `packages/{package}` before capture.
