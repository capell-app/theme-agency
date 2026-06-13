<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equestrian_venues', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->index();
            $table->string('name');
            $table->string('address_line')->nullable();
            $table->string('postal_code', 32)->nullable()->index();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('google_maps_url')->nullable();
            $table->text('facility_notes')->nullable();
            $table->text('access_notes')->nullable();
            $table->text('parking_notes')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('equestrian_tour_days', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->index();
            $table->foreignId('venue_id')->constrained('equestrian_venues')->cascadeOnDelete();
            $table->string('title');
            $table->string('status')->default('draft')->index();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();
            $table->unsignedSmallInteger('booking_lock_hours')->default(24);
            $table->unsignedSmallInteger('cancellation_refund_hours')->default(48);
            $table->unsignedSmallInteger('minimum_paid_attendees')->default(1);
            $table->unsignedInteger('minimum_revenue_pence')->default(0);
            $table->boolean('is_public')->default(false)->index();
            $table->foreignId('event_occurrence_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('equestrian_tour_day_slots', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_day_id')->constrained('equestrian_tour_days')->cascadeOnDelete();
            $table->string('title');
            $table->string('archetype')->index();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();
            $table->unsignedSmallInteger('capacity_min')->default(1);
            $table->unsignedSmallInteger('capacity_max')->default(1);
            $table->unsignedSmallInteger('booked_count')->default(0);
            $table->unsignedSmallInteger('waitlist_count')->default(0);
            $table->string('skill_tier')->nullable()->index();
            $table->unsignedInteger('price_pence')->default(0);
            $table->unsignedInteger('deposit_pence')->nullable();
            $table->foreignId('booking_service_id')->nullable()->index();
            $table->foreignId('booking_group_session_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tour_day_id', 'starts_at']);
        });

        Schema::create('equestrian_rider_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->index();
            $table->unsignedBigInteger('portal_account_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->date('date_of_birth')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('medical_disclosures')->nullable();
            $table->json('skill_tiers')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_email')->nullable();
            $table->timestamp('cash_approved_at')->nullable()->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('equestrian_horse_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->index();
            $table->unsignedBigInteger('portal_account_id')->nullable()->index();
            $table->string('name');
            $table->unsignedSmallInteger('age_years')->nullable();
            $table->string('fitness_status')->nullable();
            $table->date('vaccinated_until')->nullable()->index();
            $table->unsignedSmallInteger('daily_workload_limit_minutes')->default(120);
            $table->json('suitable_skill_tiers')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('equestrian_facility_resources', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('venue_id')->constrained('equestrian_venues')->cascadeOnDelete();
            $table->string('name');
            $table->string('type')->index();
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->unsignedInteger('price_pence')->default(0);
            $table->boolean('active')->default(true)->index();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('equestrian_facility_bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('facility_resource_id')->constrained('equestrian_facility_resources')->cascadeOnDelete();
            $table->foreignId('tour_day_slot_id')->nullable()->constrained('equestrian_tour_day_slots')->nullOnDelete();
            $table->timestamp('starts_at')->index();
            $table->timestamp('ends_at')->index();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['facility_resource_id', 'starts_at', 'ends_at']);
        });

        Schema::create('equestrian_waiver_signatures', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('rider_profile_id')->constrained('equestrian_rider_profiles')->cascadeOnDelete();
            $table->string('waiver_version');
            $table->string('signer_name');
            $table->string('signer_email')->nullable();
            $table->timestamp('signed_at')->index();
            $table->json('snapshot')->nullable();
            $table->timestamps();

            $table->index(['rider_profile_id', 'waiver_version']);
        });

        Schema::create('equestrian_clinic_credits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->index();
            $table->unsignedBigInteger('portal_account_id')->nullable()->index();
            $table->string('label');
            $table->unsignedInteger('initial_quantity');
            $table->unsignedInteger('remaining_quantity');
            $table->string('eligible_archetype')->nullable()->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('equestrian_host_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->index();
            $table->string('status')->default('new')->index();
            $table->string('requester_name');
            $table->string('requester_email')->index();
            $table->string('requester_phone')->nullable();
            $table->string('venue_name')->nullable();
            $table->string('postal_code', 32)->nullable()->index();
            $table->string('preferred_region')->nullable()->index();
            $table->string('lesson_type')->nullable();
            $table->string('skill_tier')->nullable();
            $table->unsignedSmallInteger('expected_riders')->nullable();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equestrian_host_requests');
        Schema::dropIfExists('equestrian_clinic_credits');
        Schema::dropIfExists('equestrian_waiver_signatures');
        Schema::dropIfExists('equestrian_facility_bookings');
        Schema::dropIfExists('equestrian_facility_resources');
        Schema::dropIfExists('equestrian_horse_profiles');
        Schema::dropIfExists('equestrian_rider_profiles');
        Schema::dropIfExists('equestrian_tour_day_slots');
        Schema::dropIfExists('equestrian_tour_days');
        Schema::dropIfExists('equestrian_venues');
    }
};
