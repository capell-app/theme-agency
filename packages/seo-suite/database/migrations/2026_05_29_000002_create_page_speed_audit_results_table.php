<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_speed_audit_results')) {
            return;
        }

        Schema::create('page_speed_audit_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_speed_audit_run_id')->constrained('page_speed_audit_runs')->cascadeOnDelete();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('url');
            $table->string('strategy');
            $table->string('status')->index();
            $table->unsignedTinyInteger('performance_score')->nullable();
            $table->unsignedTinyInteger('accessibility_score')->nullable();
            $table->unsignedTinyInteger('best_practices_score')->nullable();
            $table->unsignedTinyInteger('seo_score')->nullable();
            $table->json('metrics')->nullable();
            $table->json('opportunities')->nullable();
            $table->json('diagnostics')->nullable();
            $table->string('lighthouse_version')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->index(['page_id', 'language_id', 'strategy', 'fetched_at'], 'page_speed_result_latest_idx');
            $table->index(['site_id', 'language_id', 'performance_score'], 'page_speed_result_score_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_speed_audit_results');
    }
};
