<?php

declare(strict_types=1);

use Capell\LiveChat\Enums\LiveChatSourcePolicy;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $installationsTable = config('capell-live-chat.tables.installations', 'live_chat_installations');
        $conversationsTable = config('capell-live-chat.tables.conversations', 'live_chat_conversations');

        if (! Schema::hasTable($installationsTable)) {
            Schema::create($installationsTable, function (Blueprint $table): void {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
                $table->string('name');
                $table->string('public_key')->unique();
                $table->longText('allowed_domains')->nullable();
                $table->string('timezone')->default('UTC');
                $table->longText('widget_settings')->nullable();
                $table->string('source_policy')->default(LiveChatSourcePolicy::Manual->value)->index();
                $table->boolean('is_active')->default(true)->index();
                $table->longText('metadata')->nullable();
                $table->timestamps();

                $table->index(['site_id', 'is_active'], 'live_chat_installations_site_active_index');
            });
        }

        if (Schema::hasTable($conversationsTable) && ! Schema::hasColumn($conversationsTable, 'installation_id')) {
            Schema::table($conversationsTable, function (Blueprint $table) use ($installationsTable): void {
                $table->foreignId('installation_id')
                    ->nullable()
                    ->after('site_id')
                    ->constrained($installationsTable)
                    ->nullOnDelete();

                $table->index(['installation_id', 'status', 'last_message_at'], 'live_chat_installation_status_last_message_index');
            });
        }
    }

    public function down(): void
    {
        $installationsTable = config('capell-live-chat.tables.installations', 'live_chat_installations');
        $conversationsTable = config('capell-live-chat.tables.conversations', 'live_chat_conversations');

        if (Schema::hasTable($conversationsTable) && Schema::hasColumn($conversationsTable, 'installation_id')) {
            Schema::table($conversationsTable, function (Blueprint $table): void {
                $table->dropIndex('live_chat_installation_status_last_message_index');
                $table->dropConstrainedForeignId('installation_id');
            });
        }

        Schema::dropIfExists($installationsTable);
    }
};
