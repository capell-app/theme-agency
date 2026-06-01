<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_availability_exceptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('booking_services')->nullOnDelete();
            $table->foreignId('staff_member_id')->nullable()->constrained('booking_staff_members')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('booking_locations')->nullOnDelete();
            $table->string('status')->index();
            $table->date('date');
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->string('reason')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['date', 'status'], 'booking_availability_exception_date_status_index');
            $table->index(['service_id', 'staff_member_id', 'location_id'], 'booking_availability_exception_resource_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_availability_exceptions');
    }
};
