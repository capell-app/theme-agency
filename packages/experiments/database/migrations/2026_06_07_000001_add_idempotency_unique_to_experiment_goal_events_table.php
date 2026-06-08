<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = $this->tableName();

        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->unique(
                ['experiment_allocation_id', 'experiment_goal_id', 'event_key'],
                'experiment_goal_events_idempotency_unique',
            );
        });
    }

    public function down(): void
    {
        $tableName = $this->tableName();

        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropUnique('experiment_goal_events_idempotency_unique');
        });
    }

    private function tableName(): string
    {
        $tableName = config('capell-experiments.tables.goal_events', 'experiment_goal_events');

        return is_string($tableName) ? $tableName : 'experiment_goal_events';
    }
};
