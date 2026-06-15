<?php

declare(strict_types=1);

use Capell\Bookings\Enums\BookingMessageStatusEnum;
use Capell\Bookings\Enums\MessagingConsentStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_messaging_consents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('portal_account_id')->constrained('portal_accounts')->cascadeOnDelete();
            $table->string('channel')->index();
            $table->string('recipient')->nullable();
            $table->string('status')->default(MessagingConsentStatusEnum::Granted->value)->index();
            $table->json('evidence')->nullable();
            $table->timestamp('consented_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'portal_account_id', 'channel'], 'booking_messaging_consents_unique');
        });

        Schema::create('booking_message_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_request_id')->nullable()->constrained('appointment_requests')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('portal_account_id')->nullable()->constrained('portal_accounts')->nullOnDelete();
            $table->string('channel')->index();
            $table->string('type')->index();
            $table->string('status')->default(BookingMessageStatusEnum::Pending->value)->index();
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->timestamp('scheduled_for')->nullable()->index();
            $table->timestamp('sent_at')->nullable()->index();
            $table->string('provider_message_id')->nullable()->index();
            $table->text('error')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['appointment_request_id', 'channel', 'type'], 'booking_message_logs_dedupe_unique');
            $table->index(['site_id', 'portal_account_id', 'created_at'], 'booking_message_logs_portal_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_message_logs');
        Schema::dropIfExists('booking_messaging_consents');
    }
};
