<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comment_reactions')) {
            return;
        }

        Schema::create('comment_reactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->nullableMorphs('user');
            $table->string('visitor_ip_hash', 64)->nullable();
            $table->string('visitor_user_agent_hash', 64)->nullable();
            $table->string('type')->default('like');
            $table->timestamps();

            $table->index(['site_id', 'type']);
            $table->index(['comment_id', 'type']);
            $table->unique(['comment_id', 'type', 'user_type', 'user_id'], 'comment_reactions_comment_user_unique');
            $table->unique(['comment_id', 'type', 'visitor_ip_hash', 'visitor_user_agent_hash'], 'comment_reactions_comment_visitor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_reactions');
    }
};
