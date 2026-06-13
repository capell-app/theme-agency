<?php

declare(strict_types=1);

use Capell\LiveChat\Enums\LiveChatAIRunStatus;
use Capell\LiveChat\Enums\LiveChatKnowledgeGapStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $sourcesTable = $this->table('knowledge_sources', 'live_chat_knowledge_sources');
        $documentsTable = $this->table('knowledge_documents', 'live_chat_knowledge_documents');
        $aiRunsTable = $this->table('ai_runs', 'live_chat_ai_runs');
        $gapsTable = $this->table('knowledge_gaps', 'live_chat_knowledge_gaps');
        $installationsTable = $this->table('installations', 'live_chat_installations');
        $conversationsTable = $this->table('conversations', 'live_chat_conversations');
        $messagesTable = $this->table('messages', 'live_chat_messages');

        if (Schema::hasTable($sourcesTable) && ! Schema::hasColumn($sourcesTable, 'content')) {
            Schema::table($sourcesTable, function (Blueprint $table): void {
                $table->longText('content')->nullable()->after('url');
            });
        }

        if (! Schema::hasTable($documentsTable)) {
            Schema::create($documentsTable, function (Blueprint $table) use ($installationsTable, $sourcesTable): void {
                $table->id();
                $table->foreignId('installation_id')->nullable()->constrained($installationsTable)->cascadeOnDelete();
                $table->foreignId('source_id')->nullable()->constrained($sourcesTable)->nullOnDelete();
                $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
                $table->string('source_type')->index();
                $table->string('source_key')->index();
                $table->unsignedInteger('chunk_index')->default(0);
                $table->string('title');
                $table->longText('url')->nullable();
                $table->longText('content');
                $table->string('content_hash', 64)->index();
                $table->string('status')->default('active')->index();
                $table->timestamp('last_synced_at')->nullable()->index();
                $table->longText('metadata')->nullable();
                $table->timestamps();

                $table->unique(['installation_id', 'source_type', 'source_key', 'chunk_index'], 'live_chat_documents_installation_source_chunk_unique');
                $table->index(['installation_id', 'status', 'source_type'], 'live_chat_documents_installation_status_type_index');
                $table->index(['site_id', 'status', 'source_type'], 'live_chat_documents_site_status_type_index');
            });
        }

        if (! Schema::hasTable($aiRunsTable)) {
            Schema::create($aiRunsTable, function (Blueprint $table) use ($installationsTable, $conversationsTable, $messagesTable): void {
                $table->id();
                $table->foreignId('installation_id')->nullable()->constrained($installationsTable)->nullOnDelete();
                $table->foreignId('conversation_id')->nullable()->constrained($conversationsTable)->nullOnDelete();
                $table->foreignId('message_id')->nullable()->constrained($messagesTable)->nullOnDelete();
                $table->string('capability_key')->index();
                $table->string('model_tier')->default('local')->index();
                $table->decimal('confidence', 5, 4)->nullable();
                $table->unsignedInteger('latency_ms')->nullable();
                $table->string('status')->default(LiveChatAIRunStatus::Succeeded->value)->index();
                $table->longText('source_document_ids')->nullable();
                $table->longText('refusal_reason')->nullable();
                $table->longText('error_message')->nullable();
                $table->longText('input_payload')->nullable();
                $table->longText('output_payload')->nullable();
                $table->timestamps();

                $table->index(['installation_id', 'capability_key', 'created_at'], 'live_chat_ai_runs_installation_capability_created_index');
                $table->index(['conversation_id', 'created_at'], 'live_chat_ai_runs_conversation_created_index');
            });
        }

        if (! Schema::hasTable($gapsTable)) {
            Schema::create($gapsTable, function (Blueprint $table) use ($installationsTable, $conversationsTable, $messagesTable): void {
                $table->id();
                $table->foreignId('installation_id')->nullable()->constrained($installationsTable)->nullOnDelete();
                $table->foreignId('conversation_id')->nullable()->constrained($conversationsTable)->nullOnDelete();
                $table->foreignId('message_id')->nullable()->constrained($messagesTable)->nullOnDelete();
                $table->string('question_hash', 64)->index();
                $table->longText('question')->nullable();
                $table->string('source_area')->nullable()->index();
                $table->string('status')->default(LiveChatKnowledgeGapStatus::Open->value)->index();
                $table->unsignedInteger('occurrence_count')->default(1);
                $table->timestamp('first_seen_at')->nullable()->index();
                $table->timestamp('last_seen_at')->nullable()->index();
                $table->longText('metadata')->nullable();
                $table->timestamps();

                $table->unique(['installation_id', 'question_hash'], 'live_chat_gaps_installation_question_unique');
                $table->index(['installation_id', 'status', 'last_seen_at'], 'live_chat_gaps_installation_status_seen_index');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists($this->table('knowledge_gaps', 'live_chat_knowledge_gaps'));
        Schema::dropIfExists($this->table('ai_runs', 'live_chat_ai_runs'));
        Schema::dropIfExists($this->table('knowledge_documents', 'live_chat_knowledge_documents'));

        $sourcesTable = $this->table('knowledge_sources', 'live_chat_knowledge_sources');

        if (Schema::hasTable($sourcesTable) && Schema::hasColumn($sourcesTable, 'content')) {
            Schema::table($sourcesTable, function (Blueprint $table): void {
                $table->dropColumn('content');
            });
        }
    }

    private function table(string $key, string $fallback): string
    {
        $tableName = config('capell-live-chat.tables.' . $key);

        return is_string($tableName) && $tableName !== '' ? $tableName : $fallback;
    }
};
