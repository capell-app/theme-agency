<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-experiments.tables.variants', 'experiment_variants');
        $experimentsTableName = config('capell-experiments.tables.experiments', 'experiments');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($experimentsTableName): void {
            $table->id();
            $table->foreignId('experiment_id')->constrained($experimentsTableName)->cascadeOnDelete();
            $table->string('name');
            $table->string('key')->index();
            $table->unsignedInteger('weight')->default(100);
            $table->boolean('is_control')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('payload')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['experiment_id', 'key', 'deleted_at'], 'experiment_variants_key_unique');
            $table->index(['experiment_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-experiments.tables.variants', 'experiment_variants'));
    }
};
