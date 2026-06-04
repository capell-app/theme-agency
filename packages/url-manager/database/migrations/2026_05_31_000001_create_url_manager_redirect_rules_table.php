<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('url_manager_redirect_rules')) {
            return;
        }

        Schema::create('url_manager_redirect_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->foreignId('language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->string('source_url', 2048);
            $table->char('source_hash', 64);
            $table->string('target_url', 2048);
            $table->char('target_hash', 64);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->string('match_type')->default('exact');
            $table->string('status')->default('active');
            $table->integer('priority')->default(0);
            $table->boolean('preserve_query')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('hit_count')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['site_id', 'language_id', 'source_hash', 'match_type'], 'url_manager_redirect_rules_unique_source');
            $table->index(['site_id', 'language_id', 'status']);
            $table->index(['status', 'match_type']);
            $table->index(['status', 'match_type', 'priority'], 'url_manager_redirect_rules_status_match_priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('url_manager_redirect_rules');
    }
};
