<?php

declare(strict_types=1);

use Capell\Bookings\Enums\BookingReviewRequestStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_review_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_request_id')->constrained('appointment_requests')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('portal_account_id')->nullable()->constrained('portal_accounts')->nullOnDelete();
            $table->string('status')->default(BookingReviewRequestStatusEnum::Scheduled->value)->index();
            $table->timestamp('scheduled_for')->index();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('response')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['appointment_request_id', 'scheduled_for'], 'booking_review_requests_dedupe_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_review_requests');
    }
};
