<?php

declare(strict_types=1);

use Capell\LiveChat\Enums\ConversationFlow;
use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Enums\LiveChatPriority;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-live-chat.tables.conversations', 'live_chat_conversations');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->unsignedBigInteger('contact_id')->nullable()->index();
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->string('status')->default(ConversationStatus::Active->value)->index();
            $table->string('flow')->default(ConversationFlow::MessageFirst->value)->index();
            $table->string('intent')->nullable()->index();
            $table->string('priority')->default(LiveChatPriority::Normal->value)->index();
            $table->string('assignment_queue')->nullable()->index();
            $table->string('locale', 12)->default('en');
            $table->string('timezone')->default('UTC');
            $table->string('visitor_token_hash', 64)->nullable()->index();
            $table->longText('visitor_name')->nullable();
            $table->longText('visitor_email')->nullable();
            $table->string('visitor_email_hash', 64)->nullable();
            $table->longText('visitor_phone')->nullable();
            $table->string('visitor_phone_hash', 64)->nullable();
            $table->longText('visitor_company')->nullable();
            $table->timestamp('preferred_callback_at')->nullable();
            $table->boolean('processing_consent')->default(false);
            $table->boolean('marketing_consent')->default(false);
            $table->timestamp('contact_captured_at')->nullable()->index();
            $table->timestamp('ai_disclosure_at')->nullable();
            $table->longText('first_page_url')->nullable();
            $table->longText('last_page_url')->nullable();
            $table->longText('referrer_url')->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('escalation_reason')->nullable()->index();
            $table->timestamp('handoff_requested_at')->nullable()->index();
            $table->timestamp('escalated_at')->nullable()->index();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable()->index();
            $table->longText('metadata')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status', 'last_message_at'], 'live_chat_site_status_last_message_index');
            $table->index(['site_id', 'intent', 'created_at'], 'live_chat_site_intent_created_index');
            $table->index(['site_id', 'visitor_email_hash'], 'live_chat_site_email_hash_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-live-chat.tables.conversations', 'live_chat_conversations'));
    }
};
