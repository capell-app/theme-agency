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

        Schema::create('equestrian_staff_members', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->index();
            $table->string('name');
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->json('roles')->nullable();
            $table->json('availability')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
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

        Schema::create('equestrian_slot_bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_day_slot_id')->constrained('equestrian_tour_day_slots')->cascadeOnDelete();
            $table->foreignId('rider_profile_id')->constrained('equestrian_rider_profiles')->cascadeOnDelete();
            $table->foreignId('horse_profile_id')->nullable()->constrained('equestrian_horse_profiles')->nullOnDelete();
            $table->string('status')->default('held')->index();
            $table->string('payment_provider')->nullable()->index();
            $table->string('payment_status')->default('pending')->index();
            $table->boolean('cash_payment')->default(false)->index();
            $table->unsignedInteger('quoted_total_pence')->default(0);
            $table->timestamp('hold_expires_at')->nullable()->index();
            $table->timestamp('refund_available_until')->nullable()->index();
            $table->timestamp('confirmed_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tour_day_slot_id', 'status']);
            $table->index(['rider_profile_id', 'status']);
        });

        Schema::create('equestrian_slot_waitlist_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_day_slot_id')->constrained('equestrian_tour_day_slots')->cascadeOnDelete();
            $table->foreignId('rider_profile_id')->constrained('equestrian_rider_profiles')->cascadeOnDelete();
            $table->foreignId('horse_profile_id')->nullable()->constrained('equestrian_horse_profiles')->nullOnDelete();
            $table->string('status')->default('waiting')->index();
            $table->unsignedInteger('quoted_total_pence')->default(0);
            $table->timestamp('offered_at')->nullable()->index();
            $table->timestamp('offer_expires_at')->nullable()->index();
            $table->timestamp('claimed_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['tour_day_slot_id', 'status']);
            $table->index(['rider_profile_id', 'status']);
        });

        Schema::create('equestrian_horse_care_tasks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('horse_profile_id')->constrained('equestrian_horse_profiles')->cascadeOnDelete();
            $table->foreignId('assigned_staff_member_id')->nullable()->constrained('equestrian_staff_members')->nullOnDelete();
            $table->string('type')->index();
            $table->string('title');
            $table->text('instructions')->nullable();
            $table->timestamp('due_at')->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->unsignedInteger('billable_pence')->default(0);
            $table->json('recurrence')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['horse_profile_id', 'due_at']);
        });

        Schema::create('equestrian_horse_health_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('horse_profile_id')->constrained('equestrian_horse_profiles')->cascadeOnDelete();
            $table->foreignId('recorded_by_staff_member_id')->nullable()->constrained('equestrian_staff_members')->nullOnDelete();
            $table->string('type')->index();
            $table->timestamp('occurred_at')->index();
            $table->timestamp('due_next_at')->nullable()->index();
            $table->string('provider_name')->nullable();
            $table->string('summary');
            $table->text('notes')->nullable();
            $table->unsignedInteger('billable_pence')->default(0);
            $table->json('documents')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['horse_profile_id', 'type']);
        });

        Schema::create('equestrian_competition_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_day_id')->nullable()->constrained('equestrian_tour_days')->nullOnDelete();
            $table->foreignId('rider_profile_id')->constrained('equestrian_rider_profiles')->cascadeOnDelete();
            $table->foreignId('horse_profile_id')->nullable()->constrained('equestrian_horse_profiles')->nullOnDelete();
            $table->string('discipline')->nullable()->index();
            $table->string('class_name');
            $table->string('score')->nullable();
            $table->string('placing')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->text('result_notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['rider_profile_id', 'occurred_at']);
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

        Schema::create('equestrian_commercial_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->index();
            $table->string('type')->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('price_pence')->default(0);
            $table->unsignedInteger('credit_quantity')->nullable();
            $table->string('eligible_archetype')->nullable()->index();
            $table->boolean('active')->default(true)->index();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('equestrian_billing_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->index();
            $table->unsignedBigInteger('portal_account_id')->nullable()->index();
            $table->string('source_type')->nullable()->index();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('status')->default('draft')->index();
            $table->string('label');
            $table->unsignedInteger('amount_pence');
            $table->string('invoice_reference')->nullable()->index();
            $table->timestamp('exported_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['source_type', 'source_id']);
        });

        Schema::create('equestrian_communication_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tour_day_id')->nullable()->constrained('equestrian_tour_days')->nullOnDelete();
            $table->foreignId('tour_day_slot_id')->nullable()->constrained('equestrian_tour_day_slots')->nullOnDelete();
            $table->string('channel')->index();
            $table->string('audience')->index();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->unsignedInteger('recipient_count')->default(0);
            $table->json('recipients')->nullable();
            $table->timestamp('sent_at')->nullable()->index();
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
        Schema::dropIfExists('equestrian_communication_logs');
        Schema::dropIfExists('equestrian_billing_entries');
        Schema::dropIfExists('equestrian_commercial_products');
        Schema::dropIfExists('equestrian_host_requests');
        Schema::dropIfExists('equestrian_clinic_credits');
        Schema::dropIfExists('equestrian_waiver_signatures');
        Schema::dropIfExists('equestrian_competition_results');
        Schema::dropIfExists('equestrian_horse_health_records');
        Schema::dropIfExists('equestrian_horse_care_tasks');
        Schema::dropIfExists('equestrian_slot_waitlist_entries');
        Schema::dropIfExists('equestrian_slot_bookings');
        Schema::dropIfExists('equestrian_facility_bookings');
        Schema::dropIfExists('equestrian_facility_resources');
        Schema::dropIfExists('equestrian_horse_profiles');
        Schema::dropIfExists('equestrian_rider_profiles');
        Schema::dropIfExists('equestrian_staff_members');
        Schema::dropIfExists('equestrian_tour_day_slots');
        Schema::dropIfExists('equestrian_tour_days');
        Schema::dropIfExists('equestrian_venues');
    }
};
