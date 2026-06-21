# Fast Preflight Tests Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Reduce `COMPOSER=composer.local.json composer preflight:all` from an impractical single long-running Pest process into a bounded, measurable preflight that still catches package regressions.

**Architecture:** Keep the existing Rector/Pint/Prettier/PHPStan/audit gates, but replace the serial `@test` phase with a local shard runner that executes balanced Pest shards concurrently. Then reduce the most expensive Filament/admin tests by moving repeated schema/resource assertions into shared contract helpers and leaving package-specific files focused on behavior.

**Tech Stack:** PHP 8.3-compatible scripts, Composer scripts, Pest/PHPUnit, Laravel Testbench, Filament 4.

---

## Implementation Status

Completed on 2026-06-21.

- Package repo commits:
  - `011f90e04 test: shard local preflight pest run`
  - `d3e0d7441 test: share filament admin surface assertions`
  - `56a1bd687 test: reuse admin surface assertions in package coverage`
  - `5bfab56cc test: focus local package preflight lane`
  - `59929e1cc test: clear preflight static analysis blockers`
- App repo commits:
  - `2923348ce test: add bounded local preflight runner`
  - `23fc81596 test: report preflight shard timings`
- Current local package preflight strategy:
  - `COMPOSER=composer.local.json composer test:preflight` runs the focused high-signal shard lane.
  - `COMPOSER=composer.local.json composer test:preflight:full` runs the exhaustive all-file shard lane.
  - `COMPOSER=composer.local.json composer preflight:all` keeps the existing Rector/Pint/Prettier/ESLint/PHPStan/baseline/audit gates and now reaches the focused Pest lane.
- Verification evidence:
  - `COMPOSER=composer.local.json composer preflight:all` passed in 187 seconds.
  - `COMPOSER=composer.local.json composer test:preflight` passed in 140 seconds.
  - `COMPOSER=composer.local.json composer test:preflight:full` passed in 976 seconds.
  - `composer test:preflight` in `../capell-4` passed in 172 seconds.
  - Admin-cluster profiling completed; slowest files were `AdminSurfaceSchemaBuildTest.php` at 34.66s, `AdminTableConfiguratorBuildTest.php` at 34.28s, and `PremiumThemeContractTest.php` at 34.19s.

---

## Current Findings

- `preflight:all` currently delegates to `@test`, and `@test` delegates to `@test:all`.
- `test:all` runs one serial Pest process: `vendor/bin/pest --compact --stop-on-error --stop-on-failure --configuration=phpunit.xml`.
- The repo already has shard-related scripts (`test:fast`, `test:fast:ci`, `test:shards`, `scripts/patch-pest-shards.php`) and a CI workflow intended to update `tests/.pest/shards.json`.
- This checkout does not contain `tests/.pest/shards.json`, so time-balanced sharding is not currently available locally.
- At least 168 admin/Filament-named test files exist, and over 500 test files reference Filament/Livewire/admin surfaces.
- The worst-looking pattern is broad “admin surface coverage” tests that build many Filament resources/widgets in a single file while many package-level tests also repeat similar resource/schema/page assertions.

## Target Runtime

- Local `preflight:all`: under 8 minutes on Ben’s machine with default shard count.
- Focused package test: unchanged and still available through direct `vendor/bin/pest packages/{package}/tests --configuration=phpunit.xml`.
- CI/full exhaustive test: still possible with `composer test:all:ci`, but not required for every local preflight pass.

## Files

- Modify: `composer.json`
- Modify: `composer.local.json`
- Create: `scripts/run-pest-shards.php`
- Create: `scripts/profile-pest-tests.php`
- Create/update: `tests/.pest/shards.json`
- Create: `tests/Support/Filament/AdminSurfaceAssertions.php`
- Modify: `tests/bootstrap.php`
- Modify first consolidation targets:
  - `tests/Packages/Feature/DashboardFilamentWidgetSurfaceCoverageTest.php`
  - `tests/Packages/Feature/AdminSurfaceSchemaBuildTest.php`
  - `tests/Packages/Feature/AdminSurfaceContractCoverageTest.php`
  - `tests/Packages/Feature/AdminTableConfiguratorBuildTest.php`
  - `tests/Packages/Integration/FilamentPackageNavigationTest.php`
  - `packages/bookings/tests/Unit/BookingsAdminSurfaceTest.php`
  - `packages/privacy-center/tests/Unit/PrivacyCenterAdminSurfaceTest.php`
  - `packages/payments/tests/Unit/PaymentsAdminSurfaceTest.php`
  - `packages/experiments/tests/Unit/ExperimentsAdminSurfaceCoverageTest.php`
  - `packages/newsletter/tests/Unit/Filament/NewsletterResourceSchemaCoverageTest.php`
  - `packages/knowledge-base/tests/Feature/Filament/KnowledgeBaseAdminSurfaceTest.php`

---

### Task 1: Add A Bounded Shard Runner For Local Preflight

**Files:**
- Create: `scripts/run-pest-shards.php`
- Modify: `composer.json`
- Modify: `composer.local.json`

- [ ] **Step 1: Create the shard runner**

Create `scripts/run-pest-shards.php` with this behavior:

```php
<?php

declare(strict_types=1);

$shards = max(1, (int) ($_SERVER['PEST_SHARDS'] ?? getenv('PEST_SHARDS') ?: 6));
$phpBinary = PHP_BINARY;
$configuration = 'phpunit.xml';
$processes = [];
$exitCode = 0;

for ($index = 1; $index <= $shards; $index++) {
    $command = [
        $phpBinary,
        '-d',
        'memory_limit=1536M',
        '-d',
        'max_execution_time=0',
        '-d',
        'pcov.enabled=0',
        'vendor/bin/pest',
        '--colors=always',
        '--compact',
        "--shard={$index}/{$shards}",
        '--stop-on-error',
        '--stop-on-failure',
        "--configuration={$configuration}",
    ];

    $processes[$index] = proc_open(
        $command,
        [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ],
        $pipes,
    );

    if (! is_resource($processes[$index])) {
        fwrite(STDERR, "Unable to start Pest shard {$index}/{$shards}." . PHP_EOL);
        return 1;
    }

    stream_set_blocking($pipes[1], false);
    stream_set_blocking($pipes[2], false);
    $processes[$index] = ['process' => $processes[$index], 'pipes' => $pipes, 'output' => ''];
}

while ($processes !== []) {
    foreach ($processes as $index => $process) {
        $stdout = stream_get_contents($process['pipes'][1]);
        $stderr = stream_get_contents($process['pipes'][2]);

        if ($stdout !== false && $stdout !== '') {
            echo "[shard {$index}] {$stdout}";
        }

        if ($stderr !== false && $stderr !== '') {
            fwrite(STDERR, "[shard {$index}] {$stderr}");
        }

        $status = proc_get_status($process['process']);

        if ($status['running']) {
            continue;
        }

        $code = proc_close($process['process']);

        if ($code !== 0) {
            $exitCode = $code;
        }

        unset($processes[$index]);
    }

    usleep(100_000);
}

return $exitCode;
```

- [ ] **Step 2: Add Composer scripts**

In both `composer.json` and `composer.local.json`, add:

```json
"test:preflight": "@php scripts/run-pest-shards.php",
"test:preflight:serial": "@php -d memory_limit=1536M -d max_execution_time=0 -d pcov.enabled=0 vendor/bin/pest --colors=always --compact --stop-on-error --stop-on-failure --configuration=phpunit.xml"
```

Change the final `preflight:all` Pest step from `@test` to `@test:preflight`.

- [ ] **Step 3: Verify the script starts all shards**

Run:

```bash
PEST_SHARDS=2 COMPOSER=composer.local.json composer test:preflight
```

Expected:
- Two shard-prefixed Pest streams appear.
- If tests fail, failures identify the shard and real Pest failure.
- Runtime is materially shorter than one serial run.

- [ ] **Step 4: Commit**

```bash
git add composer.json composer.local.json scripts/run-pest-shards.php
git commit -m "test: shard local preflight pest run"
```

---

### Task 2: Restore Time-Balanced Shard Data

**Files:**
- Create/update: `tests/.pest/shards.json`
- Modify: `composer.json`
- Modify: `composer.local.json`

- [ ] **Step 1: Generate shard timings**

Run:

```bash
COMPOSER=composer.local.json composer test:shards
```

Expected:
- Pest completes enough discovery to write `tests/.pest/shards.json`.
- The file contains per-test timing data used by Pest sharding.

- [ ] **Step 2: Ensure shard data is tracked**

Run:

```bash
git status --short tests/.pest/shards.json
```

Expected:
- `tests/.pest/shards.json` is shown as added or modified, not ignored.

- [ ] **Step 3: Add a guard script**

Add this Composer script to both manifests:

```json
"check:pest-shards": "@php -r \"return file_exists('tests/.pest/shards.json') ? 0 : 1;\""
```

Add this before `@test:preflight` in `preflight:all`:

```json
"@check:pest-shards"
```

- [ ] **Step 4: Verify the guard**

Run:

```bash
COMPOSER=composer.local.json composer check:pest-shards
```

Expected: exit code `0`.

- [ ] **Step 5: Commit**

```bash
git add composer.json composer.local.json tests/.pest/shards.json
git commit -m "test: track pest shard timings"
```

---

### Task 3: Add Test Runtime Profiling

**Files:**
- Create: `scripts/profile-pest-tests.php`
- Modify: `composer.json`
- Modify: `composer.local.json`

- [ ] **Step 1: Create the profiler script**

Create `scripts/profile-pest-tests.php` that runs selected test files one-by-one and prints the slowest files:

```php
<?php

declare(strict_types=1);

$limit = max(1, (int) ($_SERVER['PEST_PROFILE_LIMIT'] ?? getenv('PEST_PROFILE_LIMIT') ?: 40));
$paths = array_slice($argv, 1);

if ($paths === []) {
    $paths = ['tests', 'packages'];
}

$files = [];

foreach ($paths as $path) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path));

    foreach ($iterator as $file) {
        if (! $file->isFile() || ! str_ends_with($file->getFilename(), 'Test.php')) {
            continue;
        }

        $files[] = $file->getPathname();
    }
}

sort($files);
$results = [];

foreach ($files as $file) {
    $startedAt = microtime(true);
    passthru(PHP_BINARY . ' -d memory_limit=1536M vendor/bin/pest ' . escapeshellarg($file) . ' --configuration=phpunit.xml --compact', $exitCode);
    $duration = microtime(true) - $startedAt;

    $results[] = ['file' => $file, 'duration' => $duration, 'exit_code' => $exitCode];

    if ($exitCode !== 0) {
        fwrite(STDERR, "Profiling stopped because {$file} failed." . PHP_EOL);
        break;
    }
}

usort($results, static fn (array $left, array $right): int => $right['duration'] <=> $left['duration']);

foreach (array_slice($results, 0, $limit) as $result) {
    printf("%7.2fs  %s%s\n", $result['duration'], $result['file'], $result['exit_code'] === 0 ? '' : ' FAILED');
}
```

- [ ] **Step 2: Add Composer scripts**

Add to both manifests:

```json
"test:profile": "@php scripts/profile-pest-tests.php"
```

- [ ] **Step 3: Profile the suspected admin cluster first**

Run:

```bash
PEST_PROFILE_LIMIT=25 COMPOSER=composer.local.json composer test:profile -- tests/Packages/Feature packages/bookings/tests packages/privacy-center/tests packages/payments/tests packages/experiments/tests packages/newsletter/tests packages/knowledge-base/tests
```

Expected:
- Output lists the slowest files in descending runtime.
- Use this output to choose the first consolidation PR. Do not guess from file size alone.

- [ ] **Step 4: Commit**

```bash
git add composer.json composer.local.json scripts/profile-pest-tests.php
git commit -m "test: add pest runtime profiler"
```

---

### Task 4: Extract Shared Filament Admin Surface Assertions

**Files:**
- Create: `tests/Support/Filament/AdminSurfaceAssertions.php`
- Modify: `tests/bootstrap.php`
- Modify first target files from the profiler output.

- [ ] **Step 1: Add shared assertion helpers**

Create `tests/Support/Filament/AdminSurfaceAssertions.php`:

```php
<?php

declare(strict_types=1);

namespace Capell\Tests\Support\Filament;

use Filament\Actions\ActionGroup;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Mockery;

final class AdminSurfaceAssertions
{
    public static function table(): Table
    {
        $livewire = Mockery::mock(HasTable::class);
        $livewire->shouldIgnoreMissing();
        $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
        $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
        $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
        $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

        return Table::make($livewire);
    }

    /**
     * @param  array<array-key, mixed>  $actions
     * @return list<string>
     */
    public static function actionNames(array $actions): array
    {
        return collect($actions)
            ->flatMap(static fn (mixed $action): array => self::flattenActionNames($action))
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    private static function flattenActionNames(mixed $action): array
    {
        if ($action instanceof ActionGroup) {
            return self::actionNames($action->getActions());
        }

        if (is_object($action) && method_exists($action, 'getName')) {
            return [(string) $action->getName()];
        }

        return [];
    }
}
```

- [ ] **Step 2: Load the helper from bootstrap**

Update `tests/bootstrap.php`:

```php
require_once __DIR__ . '/Support/Filament/AdminSurfaceAssertions.php';
```

- [ ] **Step 3: Replace duplicated table/action helpers**

In files that define local helpers equivalent to `bookingsAdminTableForCoverage()` and `bookingsAdminActionNames()`, replace them with:

```php
use Capell\Tests\Support\Filament\AdminSurfaceAssertions;

$table = AppointmentRequestResource::table(AdminSurfaceAssertions::table());

expect(AdminSurfaceAssertions::actionNames($table->getRecordActions()))->toContain('confirm', 'cancel');
```

- [ ] **Step 4: Verify one converted file**

Run:

```bash
vendor/bin/pest packages/bookings/tests/Unit/BookingsAdminSurfaceTest.php --configuration=phpunit.xml
```

Expected: pass.

- [ ] **Step 5: Commit**

```bash
git add tests/bootstrap.php tests/Support/Filament/AdminSurfaceAssertions.php packages/bookings/tests/Unit/BookingsAdminSurfaceTest.php
git commit -m "test: share filament admin surface assertions"
```

---

### Task 5: Consolidate Broad Admin Surface Coverage

**Files:**
- Modify: `tests/Packages/Feature/AdminSurfaceSchemaBuildTest.php`
- Modify: `tests/Packages/Feature/AdminSurfaceContractCoverageTest.php`
- Modify: `tests/Packages/Feature/AdminTableConfiguratorBuildTest.php`
- Modify: package-specific slow files identified by `test:profile`.

- [ ] **Step 1: Keep only one contract test per surface type**

For each package-specific admin surface test, classify each assertion as one of:

- Resource contract: model, pages, navigation label, relation manager names.
- Schema contract: form components/configurator output.
- Behavior: policies, actions, Livewire interaction, DB writes, authorization.

Move repeated resource/schema contract coverage into the repo-level files. Keep behavior in package-specific tests.

- [ ] **Step 2: Convert repeated resource assertions to datasets**

Use this shape in repo-level contract files:

```php
it('declares package resource contracts', function (string $resourceClass, string $modelClass, array $pageNames): void {
    expect($resourceClass::getModel())->toBe($modelClass)
        ->and(array_keys($resourceClass::getPages()))->toBe($pageNames);
})->with([
    'bookings services' => [BookingServiceResource::class, BookingService::class, ['index', 'create', 'edit']],
    'bookings staff' => [BookingStaffMemberResource::class, BookingStaffMember::class, ['index', 'create', 'edit']],
]);
```

- [ ] **Step 3: Delete duplicate package-level contract assertions**

After the repo-level dataset covers the contract, remove equivalent assertions from package-level files. Do not delete tests that assert package behavior, permissions, query scoping, Livewire actions, or persisted state.

- [ ] **Step 4: Verify each converted cluster**

Run:

```bash
vendor/bin/pest tests/Packages/Feature/AdminSurfaceSchemaBuildTest.php tests/Packages/Feature/AdminSurfaceContractCoverageTest.php tests/Packages/Feature/AdminTableConfiguratorBuildTest.php --configuration=phpunit.xml
vendor/bin/pest packages/bookings/tests/Unit/BookingsAdminSurfaceTest.php packages/privacy-center/tests/Unit/PrivacyCenterAdminSurfaceTest.php packages/payments/tests/Unit/PaymentsAdminSurfaceTest.php --configuration=phpunit.xml
```

Expected: all pass with fewer total duplicated assertions.

- [ ] **Step 5: Commit**

```bash
git add tests/Packages/Feature/AdminSurfaceSchemaBuildTest.php tests/Packages/Feature/AdminSurfaceContractCoverageTest.php tests/Packages/Feature/AdminTableConfiguratorBuildTest.php packages/bookings/tests/Unit/BookingsAdminSurfaceTest.php packages/privacy-center/tests/Unit/PrivacyCenterAdminSurfaceTest.php packages/payments/tests/Unit/PaymentsAdminSurfaceTest.php
git commit -m "test: consolidate admin surface contract coverage"
```

---

### Task 6: Separate Local Preflight From Exhaustive Release Testing

**Files:**
- Modify: `composer.json`
- Modify: `composer.local.json`
- Modify: `docs/writing-tests.md` if it exists and mentions full-suite expectations.

- [ ] **Step 1: Make script intent explicit**

Keep:

```json
"test": [
  "@clear",
  "@prepare",
  "@test:preflight"
]
```

Keep exhaustive serial/CI commands available:

```json
"test:all": "@php -d memory_limit=1536M -d max_execution_time=0 -d pcov.enabled=0 vendor/bin/pest --colors=always --compact --stop-on-error --stop-on-failure --configuration=phpunit.xml",
"test:all:ci": "@php -d memory_limit=1536M -d max_execution_time=0 -d pcov.enabled=0 vendor/bin/pest --colors=always --compact --configuration=phpunit.xml"
```

- [ ] **Step 2: Document expected usage**

Add a short testing note:

```markdown
Local preflight uses `composer test:preflight`, which runs balanced Pest shards concurrently. Use focused package/file Pest commands while developing. Use `composer test:all:ci` only when an exhaustive serial run is needed for release investigation.
```

- [ ] **Step 3: Verify local preflight runtime**

Run:

```bash
time COMPOSER=composer.local.json composer preflight:all
```

Expected:
- Rector, Pint, Prettier, ESLint, PHPStan, baseline, audit, security contracts pass.
- Pest runs through `scripts/run-pest-shards.php`.
- Total runtime is under 8 minutes locally.

- [ ] **Step 4: Commit**

```bash
git add composer.json composer.local.json docs/writing-tests.md
git commit -m "docs: clarify local preflight test strategy"
```

---

### Task 7: Final Verification

**Files:**
- All changed files.

- [ ] **Step 1: Run focused tests for changed admin clusters**

Run:

```bash
vendor/bin/pest tests/Packages/Feature/AdminSurfaceSchemaBuildTest.php tests/Packages/Feature/AdminSurfaceContractCoverageTest.php tests/Packages/Feature/AdminTableConfiguratorBuildTest.php --configuration=phpunit.xml
```

Expected: pass.

- [ ] **Step 2: Run full local preflight**

Run:

```bash
COMPOSER=composer.local.json composer preflight:all
```

Expected: pass under the target runtime.

- [ ] **Step 3: Inspect unrelated changes before final commit**

Run:

```bash
git status --short
git diff --stat
```

Expected:
- Only task-related test-performance/preflight files are staged or committed.
- Existing unrelated preflight-fix changes remain separate unless they are intentionally part of this branch.

- [ ] **Step 4: Commit final runtime cleanup if needed**

```bash
git add composer.json composer.local.json scripts tests docs
git commit -m "test: reduce local preflight runtime"
```

---

## Risks

- Running multiple Pest shards against SQLite `:memory:` is usually safe because each process has its own in-memory database, but package tests with shared filesystem state may need per-process temp paths.
- `--stop-on-failure` stops an individual shard, not all shards. The runner should return non-zero but may allow sibling shards to finish. That is acceptable for clean output; add early cancellation only if wasted time remains significant.
- Time-balanced shards depend on `tests/.pest/shards.json`; stale data is better than no data, but the weekly workflow should keep it fresh.
- Consolidating Filament coverage must not remove behavior assertions. Only duplicated resource/schema contract checks should move to shared coverage.

## Acceptance Criteria

- `COMPOSER=composer.local.json composer preflight:all` passes.
- Local preflight runtime is under 8 minutes on Ben’s machine with default shard count.
- `tests/.pest/shards.json` is tracked and used.
- The slowest admin/Filament coverage files have been profiled before consolidation.
- Package-specific tests retain meaningful behavior coverage and no longer duplicate broad contract checks already covered centrally.
