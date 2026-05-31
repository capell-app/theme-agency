<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('url_manager_not_found_opportunities')) {
            return;
        }

        Schema::create('url_manager_not_found_opportunities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->foreignId('language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->string('source_url', 2048);
            $table->char('source_hash', 64);
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->dateTime('first_seen_at');
            $table->dateTime('last_seen_at');
            $table->string('suggested_target_url', 2048)->nullable();
            $table->foreignId('redirect_rule_id')->nullable()->constrained('url_manager_redirect_rules')->nullOnDelete();
            $table->string('status')->default('open');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'language_id', 'source_hash'], 'url_manager_not_found_unique_source');
            $table->index(['site_id', 'language_id', 'status'], 'url_manager_not_found_site_language_status_idx');
            $table->index(['status', 'hit_count'], 'url_manager_not_found_status_hits_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('url_manager_not_found_opportunities');
    }
};
