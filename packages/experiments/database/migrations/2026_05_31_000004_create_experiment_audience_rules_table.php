<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-experiments.tables.audience_rules', 'experiment_audience_rules');
        $experimentsTableName = config('capell-experiments.tables.experiments', 'experiments');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($experimentsTableName): void {
            $table->id();
            $table->foreignId('experiment_id')->constrained($experimentsTableName)->cascadeOnDelete();
            $table->string('type')->index();
            $table->string('key')->index();
            $table->string('operator')->index();
            $table->json('value')->nullable();
            $table->boolean('is_required')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['experiment_id', 'is_active', 'sort_order'], 'experiment_audience_rules_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-experiments.tables.audience_rules', 'experiment_audience_rules'));
    }
};
