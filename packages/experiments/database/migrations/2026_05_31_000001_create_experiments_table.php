<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-experiments.tables.experiments', 'experiments');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('name');
            $table->string('key')->index();
            $table->string('status')->index();
            $table->string('subject_type')->index();
            $table->string('subject_class')->nullable()->index();
            $table->unsignedBigInteger('subject_id')->nullable()->index();
            $table->string('allocation_strategy')->index();
            $table->unsignedTinyInteger('traffic_percentage')->default(100);
            $table->timestamp('starts_at')->nullable()->index();
            $table->timestamp('ends_at')->nullable()->index();
            $table->unsignedBigInteger('winning_variant_id')->nullable()->index();
            $table->timestamp('winner_declared_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['site_id', 'key', 'deleted_at'], 'experiments_site_key_unique');
            $table->index(['subject_type', 'subject_class', 'subject_id'], 'experiments_subject_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-experiments.tables.experiments', 'experiments'));
    }
};
