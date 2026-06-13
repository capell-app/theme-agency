<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lesson_series')) {
            return;
        }

        $portalAccountsTable = (string) config('capell-customer-portal.tables.accounts', 'portal_accounts');

        Schema::create('lesson_series', function (Blueprint $table) use ($portalAccountsTable): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('portal_account_id')->nullable()->constrained($portalAccountsTable)->nullOnDelete();
            $table->foreignId('service_id')->constrained('booking_services')->cascadeOnDelete();
            $table->foreignId('staff_member_id')->nullable()->constrained('booking_staff_members')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('booking_locations')->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email')->index();
            $table->string('customer_phone')->nullable();
            $table->unsignedTinyInteger('day_of_week')->index();
            $table->time('starts_at');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('timezone')->default('UTC');
            $table->unsignedSmallInteger('cadence_weeks')->default(1);
            $table->date('active_from')->index();
            $table->date('active_until')->nullable()->index();
            $table->date('materialized_until')->nullable();
            $table->boolean('auto_confirm_instances')->default(true);
            $table->boolean('active')->default(true)->index();
            $table->json('reminder_preferences')->nullable();
            $table->json('payload')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id', 'portal_account_id']);
            $table->index(['service_id', 'active', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_series');
    }
};
