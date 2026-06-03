<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('capell_agent_bridge_tokens')) {
            return;
        }

        Schema::create('capell_agent_bridge_tokens', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('token_hash', 64)->unique();
            $table->morphs('user');
            $table->json('scopes');
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('revoked_at')->nullable();
            $table->timestamp('rotated_at')->nullable();
            $table->string('created_from_ip')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capell_agent_bridge_tokens');
    }
};
