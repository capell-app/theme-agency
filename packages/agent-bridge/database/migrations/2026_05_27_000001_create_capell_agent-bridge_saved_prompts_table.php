<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('capell_agent-bridge_saved_prompts')) {
            return;
        }

        Schema::create('capell_agent-bridge_saved_prompts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('form_state');
            $table->longText('prompt');
            $table->morphs('user');
            $table->timestamps();

            $table->index(['user_type', 'user_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capell_agent-bridge_saved_prompts');
    }
};
