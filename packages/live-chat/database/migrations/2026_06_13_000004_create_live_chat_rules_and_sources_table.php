<?php

declare(strict_types=1);

use Capell\LiveChat\Enums\EscalationTriggerType;
use Capell\LiveChat\Enums\KnowledgeSourceStatus;
use Capell\LiveChat\Enums\KnowledgeSourceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rulesTable = config('capell-live-chat.tables.escalation_rules', 'live_chat_escalation_rules');
        $sourcesTable = config('capell-live-chat.tables.knowledge_sources', 'live_chat_knowledge_sources');

        if (! Schema::hasTable($rulesTable)) {
            Schema::create($rulesTable, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
                $table->string('name');
                $table->string('trigger_type')->default(EscalationTriggerType::Keyword->value)->index();
                $table->string('trigger_value')->nullable()->index();
                $table->string('route_to')->nullable()->index();
                $table->string('priority')->default('high')->index();
                $table->boolean('is_active')->default(true)->index();
                $table->longText('metadata')->nullable();
                $table->timestamps();

                $table->index(['site_id', 'trigger_type', 'is_active'], 'live_chat_rules_site_trigger_active_index');
            });
        }

        if (! Schema::hasTable($sourcesTable)) {
            Schema::create($sourcesTable, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
                $table->string('type')->default(KnowledgeSourceType::Website->value)->index();
                $table->string('source_key')->index();
                $table->string('title');
                $table->longText('url')->nullable();
                $table->string('status')->default(KnowledgeSourceStatus::Active->value)->index();
                $table->string('content_hash', 64)->nullable();
                $table->timestamp('last_synced_at')->nullable()->index();
                $table->longText('metadata')->nullable();
                $table->timestamps();

                $table->unique(['site_id', 'source_key'], 'live_chat_sources_site_key_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-live-chat.tables.knowledge_sources', 'live_chat_knowledge_sources'));
        Schema::dropIfExists(config('capell-live-chat.tables.escalation_rules', 'live_chat_escalation_rules'));
    }
};
