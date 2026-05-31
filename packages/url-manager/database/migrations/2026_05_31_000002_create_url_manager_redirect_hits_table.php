<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('url_manager_redirect_hits')) {
            return;
        }

        Schema::create('url_manager_redirect_hits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('redirect_rule_id')->constrained('url_manager_redirect_rules')->cascadeOnDelete();
            $table->string('request_url', 2048);
            $table->string('referer_url', 2048)->nullable();
            $table->char('user_agent_hash', 64)->nullable();
            $table->char('ip_hash', 64)->nullable();
            $table->timestamp('hit_at');
            $table->timestamps();

            $table->index(['redirect_rule_id', 'hit_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('url_manager_redirect_hits');
    }
};
