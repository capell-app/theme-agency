<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-experiments.tables.allocations', 'experiment_allocations');
        $experimentsTableName = config('capell-experiments.tables.experiments', 'experiments');
        $variantsTableName = config('capell-experiments.tables.variants', 'experiment_variants');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($experimentsTableName, $variantsTableName): void {
            $table->id();
            $table->foreignId('experiment_id')->constrained($experimentsTableName)->cascadeOnDelete();
            $table->foreignId('experiment_variant_id')->constrained($variantsTableName)->cascadeOnDelete();
            $table->string('allocation_key');
            $table->string('allocation_hash', 64);
            $table->string('source')->nullable()->index();
            $table->string('external_id')->nullable()->index();
            $table->json('context')->nullable();
            $table->timestamp('allocated_at')->index();
            $table->timestamps();

            $table->unique(['experiment_id', 'allocation_hash'], 'experiment_allocations_hash_unique');
            $table->index(['experiment_id', 'experiment_variant_id'], 'experiment_allocations_variant_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-experiments.tables.allocations', 'experiment_allocations'));
    }
};
