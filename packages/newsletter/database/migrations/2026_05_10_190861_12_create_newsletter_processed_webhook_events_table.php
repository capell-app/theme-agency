<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_processed_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('provider_connection_id')
                ->constrained('newsletter_provider_connections', 'id', 'newsletter_processed_webhooks_connection_fk')
                ->cascadeOnDelete();
            $table->string('remote_event_id');
            $table->string('event_type');
            $table->timestamp('processed_at')->useCurrent();
            $table->timestamps();

            $table->unique(
                ['provider_connection_id', 'remote_event_id', 'event_type'],
                'newsletter_processed_webhook_events_uniq',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('newsletter_processed_webhook_events');
    }
};
