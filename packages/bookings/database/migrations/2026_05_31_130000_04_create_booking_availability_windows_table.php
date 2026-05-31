<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_availability_windows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('service_id')->nullable()->constrained('booking_services')->nullOnDelete();
            $table->foreignId('staff_member_id')->nullable()->constrained('booking_staff_members')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('booking_locations')->nullOnDelete();
            $table->string('status')->default('available')->index();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->string('timezone')->default('UTC');
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->date('effective_from')->nullable();
            $table->date('effective_until')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['status', 'day_of_week', 'starts_at', 'ends_at'], 'booking_availability_lookup_index');
            $table->index(['service_id', 'staff_member_id', 'location_id'], 'booking_availability_resource_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_availability_windows');
    }
};
