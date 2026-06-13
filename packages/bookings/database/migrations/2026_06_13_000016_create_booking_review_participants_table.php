<?php

declare(strict_types=1);

use Capell\Bookings\Enums\BookingReviewParticipantStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_review_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_review_request_id')->constrained('booking_review_requests')->cascadeOnDelete();
            $table->foreignId('portal_account_id')->nullable()->constrained('portal_accounts')->nullOnDelete();
            $table->string('role')->index();
            $table->string('name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('status')->default(BookingReviewParticipantStatusEnum::Pending->value)->index();
            $table->boolean('required')->default(true)->index();
            $table->string('token_hash')->nullable();
            $table->timestamp('token_expires_at')->nullable()->index();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('response')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['booking_review_request_id', 'role', 'email'], 'booking_review_participants_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_review_participants');
    }
};
