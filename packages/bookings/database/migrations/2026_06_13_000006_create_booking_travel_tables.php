<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_travel_observations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('origin_location_id')->nullable()->constrained('booking_locations')->nullOnDelete();
            $table->foreignId('destination_location_id')->nullable()->constrained('booking_locations')->nullOnDelete();
            $table->unsignedSmallInteger('duration_minutes');
            $table->decimal('distance_miles', 8, 2)->default(0);
            $table->string('source')->default('observed')->index();
            $table->json('meta')->nullable();
            $table->timestamp('observed_at')->index();
            $table->timestamps();

            $table->index(['origin_location_id', 'destination_location_id', 'observed_at'], 'booking_travel_observations_route_index');
        });

        Schema::create('booking_travel_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('origin_location_id')->nullable()->constrained('booking_locations')->nullOnDelete();
            $table->foreignId('destination_location_id')->nullable()->constrained('booking_locations')->nullOnDelete();
            $table->smallInteger('extra_minutes');
            $table->string('reason')->nullable();
            $table->timestamp('effective_from')->nullable()->index();
            $table->timestamp('effective_until')->nullable()->index();
            $table->boolean('active')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_work_zones', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('service_area')->nullable()->index();
            $table->json('postal_code_prefixes')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_work_zones');
        Schema::dropIfExists('booking_travel_adjustments');
        Schema::dropIfExists('booking_travel_observations');
    }
};
