<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_webhook_events', function (Blueprint $table): void {
            $table->id();
            $table->string('provider')->index();
            $table->string('provider_event_id')->index();
            $table->string('type')->index();
            $table->string('status')->default('received')->index();
            $table->json('payload')->nullable();
            $table->timestamp('received_at')->index();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['provider', 'provider_event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_webhook_events');
    }
};
