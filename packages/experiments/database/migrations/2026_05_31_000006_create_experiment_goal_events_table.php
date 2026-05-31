<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-experiments.tables.goal_events', 'experiment_goal_events');
        $experimentsTableName = config('capell-experiments.tables.experiments', 'experiments');
        $goalsTableName = config('capell-experiments.tables.goals', 'experiment_goals');
        $allocationsTableName = config('capell-experiments.tables.allocations', 'experiment_allocations');
        $variantsTableName = config('capell-experiments.tables.variants', 'experiment_variants');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($allocationsTableName, $experimentsTableName, $goalsTableName, $variantsTableName): void {
            $table->id();
            $table->foreignId('experiment_id')->constrained($experimentsTableName)->cascadeOnDelete();
            $table->foreignId('experiment_variant_id')->constrained($variantsTableName)->cascadeOnDelete();
            $table->foreignId('experiment_goal_id')->constrained($goalsTableName)->cascadeOnDelete();
            $table->foreignId('experiment_allocation_id')->nullable()->constrained($allocationsTableName)->nullOnDelete();
            $table->string('event_key')->nullable()->index();
            $table->decimal('value_amount', 12, 2)->nullable();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['experiment_id', 'experiment_variant_id', 'experiment_goal_id'], 'experiment_goal_events_rollup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-experiments.tables.goal_events', 'experiment_goal_events'));
    }
};
