<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-payments.tables.webhook_events', 'payment_webhook_events');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->string('provider')->index();
            $table->string('provider_event_id');
            $table->string('event_type')->index();
            $table->boolean('livemode')->default(false)->index();
            $table->string('api_version')->nullable();
            $table->string('status')->index();
            $table->string('signature_header_hash')->nullable();
            $table->longText('payload');
            $table->timestamp('received_at')->nullable()->index();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamp('failed_at')->nullable()->index();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-payments.tables.webhook_events', 'payment_webhook_events'));
    }
};
