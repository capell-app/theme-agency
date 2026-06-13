<?php

declare(strict_types=1);

use Capell\LiveChat\Enums\MessageRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-live-chat.tables.messages', 'live_chat_messages');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained(config('capell-live-chat.tables.conversations', 'live_chat_conversations'))
                ->cascadeOnDelete();
            $table->string('role')->default(MessageRole::Visitor->value)->index();
            $table->longText('body');
            $table->string('intent')->nullable()->index();
            $table->decimal('confidence', 5, 4)->nullable();
            $table->boolean('requires_contact')->default(false);
            $table->longText('attachments')->nullable();
            $table->longText('metadata')->nullable();
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at'], 'live_chat_messages_conversation_created_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-live-chat.tables.messages', 'live_chat_messages'));
    }
};
