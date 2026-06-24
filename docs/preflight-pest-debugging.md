# Preflight Pest Debugging Log

Use this when `composer preflight:all`, `composer test`, or `scripts/run-pest-shards.php` starts producing many package failures or takes long enough that rerunning the whole command after every fix wastes time.

## Current Failure Log

Run date: 2026-06-23.

Primary command:

```sh
docker compose run --rm --no-deps --user capell --env HOME=/home/capell --env COMPOSER_HOME=/home/capell/.config/composer app bash -lc 'cd /home/capell/current && php scripts/run-pest-shards.php'
```

Keep Docker services running before shard or broad Pest runs:

```sh
docker compose up -d mysql redis mailpit
```

| Failure                                | Symptom                                                                                                                                           | Root cause                                                                                                          | Fix applied                                                                                                                                                                        | Targeted verification                                                                                                                                                                                |
| -------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `access-gate` package tests            | Package Pest run used host/default database state and failed during setup.                                                                        | The package test case did not force sqlite `:memory:` under the Docker/Testbench run.                               | `packages/access-gate/tests/TestCase.php` now sets sqlite in-memory database config, app key, and typed overrides.                                                                 | `vendor/bin/pest packages/access-gate/tests --configuration=phpunit.xml` passed, 182 tests.                                                                                                          |
| `agent-bridge` selected tests          | Package tests failed during setup for the same database isolation reason.                                                                         | The package test case did not force sqlite `:memory:` under the Docker/Testbench run.                               | `packages/agent-bridge/tests/TestCase.php` now sets sqlite in-memory database config, app key, and typed overrides.                                                                | `vendor/bin/pest packages/agent-bridge/tests/Unit/ManifestRequirementsTest.php packages/agent-bridge/tests/Unit/AgentBridgeHealthCheckTest.php --configuration=phpunit.xml` passed after docs fixes. |
| `privacy-center` manifest/health tests | Expected package docs to describe consent preference center, export/erasure registry, subject inference, and cache-safe public preference center. | Generated docs were behind the manifest test contract.                                                              | `packages/privacy-center/docs/overview.md` now contains the required contract language.                                                                                            | `vendor/bin/pest packages/privacy-center/tests/Unit/ManifestRequirementsTest.php packages/privacy-center/tests/Unit/PrivacyCenterHealthCheckTest.php --configuration=phpunit.xml` passed, 10 tests.  |
| Theme manifest tests                   | Theme docs were missing exact product group, runtime inheritance, dependency, PHPUnit, or public-cache phrases.                                   | Generated theme overview docs did not match the manifest tests.                                                     | Updated docs for `theme-agency`, `theme-estate-agents`, `theme-portfolio`, `theme-restaurant`, and `theme-saas`; updated `packages/theme-restaurant/README.md`.                    | Each affected `ManifestRequirementsTest.php` passed individually.                                                                                                                                    |
| `url-manager` package tests            | FK failures inserting scoped redirect/not-found rows with `site_id`, `language_id`, and `created_by_user_id`.                                     | The package test case created `sites`, `languages`, and `users` tables but did not seed fixture rows used by tests. | `packages/url-manager/tests/UrlManagerTestCase.php` now forces sqlite in-memory config and seeds test rows for IDs used by the package tests.                                      | `vendor/bin/pest packages/url-manager/tests --configuration=phpunit.xml` passed, 54 tests.                                                                                                           |
| `ai-orchestrator` manifest test        | `docs/overview.md` did not contain the exact manifest summary or screenshot note.                                                                 | Overview was older human-facing copy while the manifest test expects marketplace contract text.                     | `packages/ai-orchestrator/docs/overview.md` now includes the canonical summary and empty-screenshot note.                                                                          | Targeted AI Orchestrator and Agent Bridge manifest rerun passed, 7 tests.                                                                                                                            |
| `agent-bridge` manifest test           | README contained `Deletion/retention behaviour: Docs gap`; overview did not mention `capell:agent-bridge-prune-audit`.                            | Docs had not been updated after the audit prune command and retention behavior were added.                          | `packages/agent-bridge/README.md` now describes prune-command retention; `packages/agent-bridge/docs/overview.md` mentions migration impact and `capell:agent-bridge-prune-audit`. | Targeted AI Orchestrator and Agent Bridge manifest rerun passed, 7 tests.                                                                                                                            |
| `ManifestV3CoverageTest`               | `unassignedPackages` contained `ai-creator`.                                                                                                      | `scripts/audit-manifest-v3.php` did not assign `ai-creator` to a migration group.                                   | Added `ai-creator` to the `content-product` migration group.                                                                                                                       | `vendor/bin/pest packages/ai-creator/tests/Unit/ManifestRequirementsTest.php tests/Feature/ManifestV3CoverageTest.php --configuration=phpunit.xml` passed, 5 tests.                                  |
| `ManifestV3CoverageTest`               | `packages/ai-creator/capell.json` failed `healthChecks must be a non-empty list`.                                                                 | Assigning `ai-creator` exposed its missing Diagnostics health check contract.                                       | Added `Capell\AiCreator\Health\AiCreatorHealthCheck` and wired it into `packages/ai-creator/capell.json`.                                                                          | Same targeted AI Creator plus v3 coverage rerun passed, 5 tests.                                                                                                                                     |
| `SeoSuiteBoundaryTest`                 | `ParseError: Unclosed '(' on line 144` in `vendor/orchestra/testbench-core/laravel/bootstrap/cache/services.php`.                                 | Generated Testbench cache became corrupt during interrupted long-running parallel shard runs.                       | Removed `vendor/orchestra/testbench-core/laravel/bootstrap/cache/services.php` and `packages.php`.                                                                                 | `vendor/bin/pest packages/seo-suite/tests/Arch/SeoSuiteBoundaryTest.php --configuration=phpunit.xml` passed, 5 tests.                                                                                |

## Batch Debugging Workflow

Do not keep rerunning `preflight:all` after every single failure when the command is slow. Batch the failures, fix them together, then rerun targeted groups first.

1. Start broad enough to reveal the failing class of checks:

```sh
docker compose run --rm --no-deps --user capell --env HOME=/home/capell --env COMPOSER_HOME=/home/capell/.config/composer app bash -lc 'cd /home/capell/current && php scripts/run-pest-shards.php'
```

2. If the shard runner is still draining after the first actionable failure, let it continue long enough to collect more failures. Stop it only after output shows no new failure text for a while or when enough failures are known to make a useful batch.

3. Move to package or file-level runs for each failure:

```sh
docker compose run --rm --no-deps --user capell --env HOME=/home/capell --env COMPOSER_HOME=/home/capell/.config/composer app bash -lc 'cd /home/capell/current && vendor/bin/pest packages/url-manager/tests --configuration=phpunit.xml --stop-on-error --stop-on-failure --colors=always --compact'
```

4. Fix the shared cause once. Examples from this run: sqlite test setup belonged in package `TestCase` classes, docs contract failures belonged in package docs, and `ai-creator` manifest failures belonged in manifest audit/health-check metadata.

5. Rerun the targeted file or package until it is green. Record the command and result in this log if the failure was non-obvious or slow.

6. Clear generated Testbench cache before rerunning shards after interrupted parallel runs:

```sh
rm -f vendor/orchestra/testbench-core/laravel/bootstrap/cache/services.php vendor/orchestra/testbench-core/laravel/bootstrap/cache/packages.php
```

7. Rerun `scripts/run-pest-shards.php`. Only move back to `COMPOSER=composer.local.json composer preflight` or `COMPOSER=composer.local.json composer preflight:all` after the focused shard runner is clean.

## Slow Package Notes

`welcome-tour` and `wordpress-importer` are slow under the Docker harness. Do not assume they are hung just because compact Pest output is quiet.

Known timings from this run:

- `packages/welcome-tour/tests/Unit/WelcomeTourSchemaCoverageTest.php`: 4 passed in 28.54s.
- `packages/welcome-tour/tests/Feature/WelcomeTourSettingsTest.php`: 4 passed in 22.57s.
- `packages/welcome-tour/tests/Feature/WelcomeTourTest.php`: 22 passed in 137.87s.
- `packages/wordpress-importer/tests`: 19 passed in 112.44s.

If a slow package is silent for more than a minute, inspect the container before interrupting:

```sh
docker ps --format 'table {{.ID}}\t{{.Names}}\t{{.Status}}\t{{.Command}}'
docker top <container-name> -eo pid,ppid,stat,etime,comm,args
```

An active `php vendor/bin/pest ...` process with increasing elapsed time is usually just slow. Interrupt only when the process is clearly idle, blocked, or preventing failure batching.
