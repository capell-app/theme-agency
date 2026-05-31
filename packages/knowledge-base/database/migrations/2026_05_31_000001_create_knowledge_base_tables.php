<?php

declare(strict_types=1);

use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Enums\KnowledgeBaseRelatedArticleType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('knowledge_base_collections')) {
            Schema::create('knowledge_base_collections', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('parent_id')->nullable()->constrained('knowledge_base_collections')->nullOnDelete();
                $table->string('key')->unique();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->boolean('is_public')->default(true)->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('knowledge_base_articles')) {
            Schema::create('knowledge_base_articles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('collection_id')->constrained('knowledge_base_collections')->cascadeOnDelete();
                $table->foreignId('current_version_id')->nullable();
                $table->string('title');
                $table->string('slug');
                $table->text('summary')->nullable();
                $table->string('status')->default(KnowledgeBaseArticleStatus::Draft->value)->index();
                $table->unsignedSmallInteger('search_weight')->default(50)->index();
                $table->boolean('is_ai_readable')->default(true)->index();
                $table->timestamp('published_at')->nullable()->index();
                $table->timestamps();

                $table->unique(['collection_id', 'slug'], 'knowledge_base_article_collection_slug_unique');
                $table->index(['status', 'published_at'], 'knowledge_base_article_public_index');
            });
        }

        if (! Schema::hasTable('knowledge_base_article_versions')) {
            Schema::create('knowledge_base_article_versions', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('article_id')->constrained('knowledge_base_articles')->cascadeOnDelete();
                $table->string('version');
                $table->string('title');
                $table->text('summary')->nullable();
                $table->mediumText('body');
                $table->nullableMorphs('author');
                $table->timestamp('published_at')->nullable()->index();
                $table->timestamps();

                $table->unique(['article_id', 'version'], 'knowledge_base_article_version_unique');
            });
        }

        if (! Schema::hasTable('knowledge_base_article_feedback')) {
            Schema::create('knowledge_base_article_feedback', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('article_id')->constrained('knowledge_base_articles')->cascadeOnDelete();
                $table->foreignId('article_version_id')->nullable()->constrained('knowledge_base_article_versions')->nullOnDelete();
                $table->boolean('helpful')->index();
                $table->text('comment')->nullable();
                $table->string('visitor_hash', 64)->nullable()->index();
                $table->string('user_agent_hash', 64)->nullable();
                $table->timestamp('submitted_at')->index();
                $table->timestamps();

                $table->index(['article_id', 'helpful'], 'knowledge_base_article_feedback_summary_index');
            });
        }

        if (! Schema::hasTable('knowledge_base_related_articles')) {
            Schema::create('knowledge_base_related_articles', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('article_id')->constrained('knowledge_base_articles')->cascadeOnDelete();
                $table->foreignId('related_article_id')->constrained('knowledge_base_articles')->cascadeOnDelete();
                $table->string('relation_type')->default(KnowledgeBaseRelatedArticleType::Related->value)->index();
                $table->unsignedInteger('sort_order')->default(0)->index();
                $table->timestamps();

                $table->unique(['article_id', 'related_article_id'], 'knowledge_base_related_article_unique');
            });
        }

        if (Schema::hasTable('knowledge_base_articles')) {
            Schema::table('knowledge_base_articles', function (Blueprint $table): void {
                $table->foreign('current_version_id', 'knowledge_base_articles_current_version_foreign')
                    ->references('id')
                    ->on('knowledge_base_article_versions')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('knowledge_base_articles')) {
            Schema::table('knowledge_base_articles', function (Blueprint $table): void {
                $table->dropForeign('knowledge_base_articles_current_version_foreign');
            });
        }

        Schema::dropIfExists('knowledge_base_related_articles');
        Schema::dropIfExists('knowledge_base_article_feedback');
        Schema::dropIfExists('knowledge_base_article_versions');
        Schema::dropIfExists('knowledge_base_articles');
        Schema::dropIfExists('knowledge_base_collections');
    }
};
