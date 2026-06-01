<?php

declare(strict_types=1);

use Capell\AutomationStudio\Enums\AutomationRunStatus;
use Capell\AutomationStudio\Models\AutomationRule;
use Capell\AutomationStudio\Models\AutomationRun;
use Capell\AutomationStudio\Providers\AutomationStudioServiceProvider;

it('declares automation persistence models and manifest tables', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect((new AutomationRule)->getTable())->toBe('automation_rules')
        ->and((new AutomationRun)->getTable())->toBe('automation_runs')
        ->and((new AutomationRun)->isFillable('idempotency_key'))->toBeTrue()
        ->and((new AutomationRun)->isFillable('attempt_number'))->toBeTrue()
        ->and(AutomationRunStatus::Succeeded->value)->toBe('succeeded')
        ->and($manifest['database']['migrations'])->toBeTrue()
        ->and($manifest['database']['requiredTables'])->toBe([
            'automation_rules',
            'automation_runs',
        ])
        ->and($manifest['providers']['runtime'])->toContain(AutomationStudioServiceProvider::class)
        ->and($manifest['capabilities'])->toContain('automation-persistence');
});

it('guards automation persistence migrations for repeatable package installs', function (): void {
    $rulesMigration = (string) file_get_contents(__DIR__ . '/../../database/migrations/2026_05_31_170000_01_create_automation_rules_table.php');
    $runsMigration = (string) file_get_contents(__DIR__ . '/../../database/migrations/2026_05_31_170000_02_create_automation_runs_table.php');

    expect($rulesMigration)->toContain("Schema::hasTable('automation_rules')")
        ->and($runsMigration)->toContain("Schema::hasTable('automation_runs')")
        ->and($runsMigration)->toContain("idempotency_key')->nullable()->unique()")
        ->and($runsMigration)->toContain("attempt_number')->default(1)");
});
