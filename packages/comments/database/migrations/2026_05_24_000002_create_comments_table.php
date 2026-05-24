<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comments')) {
            return;
        }

        Schema::create('comments', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->foreignId('comment_author_id')->constrained('comment_authors')->cascadeOnDelete();
            $table->nullableMorphs('commentable');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->foreignId('root_id')->nullable()->constrained('comments')->cascadeOnDelete();
            $table->unsignedTinyInteger('depth')->default(0);
            $table->string('status')->index();
            $table->longText('body');
            $table->string('visitor_ip_hash', 64)->nullable()->index();
            $table->string('visitor_user_agent_hash', 64)->nullable();
            $table->unsignedTinyInteger('link_count')->default(0);
            $table->json('spam_reasons')->nullable();
            $table->timestamp('submitted_at')->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('approved_at')->nullable()->index();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('marked_spam_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('moderation_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'status', 'submitted_at']);
            $table->index(['site_id', 'submitted_at']);
            $table->index(['commentable_type', 'commentable_id', 'status', 'submitted_at'], 'comments_commentable_status_submitted_index');
            $table->index(['root_id', 'parent_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
