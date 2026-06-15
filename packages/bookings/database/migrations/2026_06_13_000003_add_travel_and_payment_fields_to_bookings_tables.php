<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_locations', function (Blueprint $table): void {
            $table->decimal('latitude', 10, 7)->nullable()->after('timezone');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->unsignedSmallInteger('access_overhead_minutes')->default(0)->after('longitude');
            $table->string('service_area')->nullable()->after('access_overhead_minutes')->index();
            $table->text('route_notes')->nullable()->after('service_area');
        });

        Schema::table('appointment_requests', function (Blueprint $table): void {
            $table->unsignedSmallInteger('travel_duration_minutes')->nullable()->after('is_time_pinned');
            $table->decimal('travel_distance_miles', 8, 2)->nullable()->after('travel_duration_minutes');
            $table->unsignedInteger('fuel_allowance_pence')->nullable()->after('travel_distance_miles');
            $table->timestamp('fuel_acknowledged_at')->nullable()->after('fuel_allowance_pence');
            $table->unsignedInteger('payment_required_amount_pence')->nullable()->after('fuel_acknowledged_at');
            $table->string('payment_checkout_session_id')->nullable()->after('payment_required_amount_pence')->index();
            $table->string('payment_provider_reference')->nullable()->after('payment_checkout_session_id')->index();
            $table->timestamp('payment_confirmed_at')->nullable()->after('payment_provider_reference');
            $table->string('attendance_status')->nullable()->after('payment_confirmed_at')->index();
            $table->timestamp('attended_at')->nullable()->after('attendance_status');
        });
    }

    public function down(): void
    {
        Schema::table('appointment_requests', function (Blueprint $table): void {
            $table->dropIndex('appointment_requests_attendance_status_index');
            $table->dropIndex('appointment_requests_payment_checkout_session_id_index');
            $table->dropIndex('appointment_requests_payment_provider_reference_index');
            $table->dropColumn([
                'attended_at',
                'attendance_status',
                'fuel_acknowledged_at',
                'fuel_allowance_pence',
                'payment_checkout_session_id',
                'payment_confirmed_at',
                'payment_provider_reference',
                'payment_required_amount_pence',
                'travel_distance_miles',
                'travel_duration_minutes',
            ]);
        });

        Schema::table('booking_locations', function (Blueprint $table): void {
            $table->dropIndex('booking_locations_service_area_index');
            $table->dropColumn([
                'access_overhead_minutes',
                'latitude',
                'longitude',
                'route_notes',
                'service_area',
            ]);
        });
    }
};
