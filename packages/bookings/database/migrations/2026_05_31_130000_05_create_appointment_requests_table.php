<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('booking_services')->nullOnDelete();
            $table->foreignId('staff_member_id')->nullable()->constrained('booking_staff_members')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('booking_locations')->nullOnDelete();
            $table->string('status')->default('requested')->index();
            $table->timestamp('requested_starts_at')->index();
            $table->timestamp('requested_ends_at')->index();
            $table->string('timezone')->default('UTC');
            $table->string('customer_name');
            $table->string('customer_email')->index();
            $table->string('customer_phone')->nullable();
            $table->text('notes')->nullable();
            $table->string('source')->nullable()->index();
            $table->string('confirmation_token')->nullable()->unique();
            $table->string('calendar_uid')->unique();
            $table->json('reminder_preferences')->nullable();
            $table->json('payload')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('requested_at')->nullable()->index();
            $table->timestamp('confirmed_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'status', 'requested_starts_at'], 'appointment_service_status_start_index');
            $table->index(['staff_member_id', 'status', 'requested_starts_at'], 'appointment_staff_status_start_index');
            $table->index(['location_id', 'status', 'requested_starts_at'], 'appointment_location_status_start_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_requests');
    }
};
