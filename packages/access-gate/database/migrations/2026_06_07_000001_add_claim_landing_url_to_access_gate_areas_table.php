<?php

declare(strict_types=1);

use Capell\AccessGate\Support\AccessGateSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        $builder = AccessGateSchema::builder();

        if ($builder->hasColumn('access_gate_areas', 'claim_landing_url')) {
            return;
        }

        $builder->table('access_gate_areas', function (Blueprint $table): void {
            $table->string('claim_landing_url')->nullable()->after('gate_view');
        });
    }

    public function down(): void
    {
        $builder = AccessGateSchema::builder();

        if (! $builder->hasColumn('access_gate_areas', 'claim_landing_url')) {
            return;
        }

        $builder->table('access_gate_areas', function (Blueprint $table): void {
            $table->dropColumn('claim_landing_url');
        });
    }
};
