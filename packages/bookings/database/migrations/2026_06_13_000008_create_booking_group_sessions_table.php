<?php

declare(strict_types=1);

use Capell\Bookings\Enums\BookingGroupSessionStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_group_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('service_id')->constrained('booking_services')->cascadeOnDelete();
            $table->foreignId('staff_member_id')->nullable()->constrained('booking_staff_members')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('booking_locations')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default(BookingGroupSessionStatusEnum::Draft->value)->index();
            $table->unsignedSmallInteger('capacity');
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at');
            $table->timestamp('offered_window_starts_at')->nullable();
            $table->timestamp('offered_window_ends_at')->nullable();
            $table->unsignedInteger('fee_pence')->nullable();
            $table->string('external_event_id')->nullable()->index();
            $table->json('social_attendance')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_group_sessions');
    }
};
