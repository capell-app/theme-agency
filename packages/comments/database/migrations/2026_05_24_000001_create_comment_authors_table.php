<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comment_authors')) {
            return;
        }

        Schema::create('comment_authors', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->nullableMorphs('user');
            $table->longText('name');
            $table->longText('email')->nullable();
            $table->string('email_hash', 64)->nullable();
            $table->timestamp('email_verified_at')->nullable()->index();
            $table->timestamp('trusted_at')->nullable()->index();
            $table->timestamp('blocked_at')->nullable()->index();
            $table->timestamp('reply_notifications_disabled_at')->nullable()->index();
            $table->text('internal_notes')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'email_hash']);
            $table->unique(['site_id', 'user_type', 'user_id'], 'comment_authors_site_user_unique');
            $table->index(['site_id', 'blocked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_authors');
    }
};
