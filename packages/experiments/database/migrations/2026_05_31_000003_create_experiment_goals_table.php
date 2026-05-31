<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-experiments.tables.goals', 'experiment_goals');
        $experimentsTableName = config('capell-experiments.tables.experiments', 'experiments');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($experimentsTableName): void {
            $table->id();
            $table->foreignId('experiment_id')->constrained($experimentsTableName)->cascadeOnDelete();
            $table->string('name');
            $table->string('key')->index();
            $table->string('type')->index();
            $table->string('target')->nullable()->index();
            $table->decimal('value_amount', 12, 2)->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['experiment_id', 'key', 'deleted_at'], 'experiment_goals_key_unique');
            $table->index(['experiment_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-experiments.tables.goals', 'experiment_goals'));
    }
};
