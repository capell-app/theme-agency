<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

it('runs the access gate core migrations', function (): void {
    expect(Schema::hasTable('access_gate_areas'))->toBeTrue()
        ->and(Schema::hasTable('access_gate_registrations'))->toBeTrue()
        ->and(Schema::hasTable('access_gate_grants'))->toBeTrue()
        ->and(Schema::hasTable('access_gate_claim_tokens'))->toBeTrue()
        ->and(Schema::hasTable('access_gate_browser_tokens'))->toBeTrue()
        ->and(Schema::hasTable('access_gate_events'))->toBeTrue();
});

it('creates access gate tables on the configured database connection', function (): void {
    Config::set('database.connections.access_gate_testing', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    Config::set('access-gate.connection', 'access_gate_testing');

    foreach (accessGateMigrationFiles() as $migrationFile) {
        /** @var Migration $migration */
        $migration = require $migrationFile;
        assert(method_exists($migration, 'up'));
        $migration->up();
    }

    expect(Schema::connection('access_gate_testing')->hasTable('access_gate_areas'))->toBeTrue()
        ->and(Schema::connection('access_gate_testing')->hasTable('access_gate_registrations'))->toBeTrue()
        ->and(Schema::connection('access_gate_testing')->hasTable('access_gate_grants'))->toBeTrue()
        ->and(Schema::connection('access_gate_testing')->hasTable('access_gate_claim_tokens'))->toBeTrue()
        ->and(Schema::connection('access_gate_testing')->hasTable('access_gate_browser_tokens'))->toBeTrue()
        ->and(Schema::connection('access_gate_testing')->hasTable('access_gate_events'))->toBeTrue();
});

it('includes registration columns needed for policy-safe email deduplication', function (): void {
    expect(Schema::hasColumn('access_gate_registrations', 'email'))->toBeTrue()
        ->and(Schema::hasColumn('access_gate_registrations', 'email_normalized'))->toBeTrue()
        ->and(Schema::hasColumn('access_gate_registrations', 'single_registration_key'))->toBeTrue()
        ->and(Schema::hasColumn('access_gate_registrations', 'field_values'))->toBeTrue();
});

it('includes site scoping on access areas', function (): void {
    expect(Schema::hasColumn('access_gate_areas', 'site_id'))->toBeTrue();
});

it('includes scheduling columns on access areas', function (): void {
    expect(Schema::hasColumn('access_gate_areas', 'opens_at'))->toBeTrue()
        ->and(Schema::hasColumn('access_gate_areas', 'closes_at'))->toBeTrue();
});

it('includes resolver indexes for download access state lookups', function (): void {
    expect(Schema::hasIndex('access_gate_registrations', 'ag_regs_area_email_norm_requested_idx'))->toBeTrue()
        ->and(Schema::hasIndex('access_gate_registrations', 'ag_regs_area_user_requested_idx'))->toBeTrue()
        ->and(Schema::hasIndex('access_gate_registrations', 'ag_regs_area_position_idx'))->toBeTrue()
        ->and(Schema::hasIndex('access_gate_registrations', 'ag_regs_area_approved_idx'))->toBeTrue()
        ->and(Schema::hasIndex('access_gate_grants', 'ag_grants_area_reg_status_idx'))->toBeTrue()
        ->and(Schema::hasIndex('access_gate_grants', 'ag_grants_area_email_status_idx'))->toBeTrue()
        ->and(Schema::hasIndex('access_gate_grants', 'ag_grants_area_user_status_idx'))->toBeTrue();
});

/**
 * @return list<string>
 */
function accessGateMigrationFiles(): array
{
    return [
        __DIR__ . '/../../database/migrations/2026_05_10_190838_01_create_access_gate_areas_table.php',
        __DIR__ . '/../../database/migrations/2026_05_10_190838_02_create_access_gate_registrations_table.php',
        __DIR__ . '/../../database/migrations/2026_05_10_190838_03_create_access_gate_grants_table.php',
        __DIR__ . '/../../database/migrations/2026_05_10_190838_04_create_access_gate_claim_tokens_table.php',
        __DIR__ . '/../../database/migrations/2026_05_10_190838_05_create_access_gate_browser_tokens_table.php',
        __DIR__ . '/../../database/migrations/2026_05_10_190838_06_create_access_gate_events_table.php',
    ];
}
