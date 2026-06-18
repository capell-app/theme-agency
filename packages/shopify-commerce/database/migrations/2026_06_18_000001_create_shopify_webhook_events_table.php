<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('shopify_webhook_events')) {
            return;
        }

        Schema::create('shopify_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('connection_id')->constrained('shopify_connections')->cascadeOnDelete();
            $table->string('webhook_id');
            $table->string('topic')->index();
            $table->timestamp('triggered_at')->nullable();
            $table->timestamp('received_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['connection_id', 'webhook_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_webhook_events');
    }
};
