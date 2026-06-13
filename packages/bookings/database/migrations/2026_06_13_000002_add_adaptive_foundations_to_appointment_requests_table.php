<?php

declare(strict_types=1);

use Capell\Bookings\Enums\ConfirmationPolicyEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('appointment_requests')) {
            return;
        }

        $portalAccountsTable = (string) config('capell-customer-portal.tables.accounts', 'portal_accounts');

        Schema::table('appointment_requests', function (Blueprint $table) use ($portalAccountsTable): void {
            if (! Schema::hasColumn('appointment_requests', 'site_id')) {
                $table->foreignId('site_id')->nullable()->after('id')->constrained('sites')->nullOnDelete();
            }

            if (! Schema::hasColumn('appointment_requests', 'portal_account_id')) {
                $table->foreignId('portal_account_id')->nullable()->after('site_id')->constrained($portalAccountsTable)->nullOnDelete();
            }

            if (! Schema::hasColumn('appointment_requests', 'lesson_series_id')) {
                $table->foreignId('lesson_series_id')->nullable()->after('portal_account_id')->constrained('lesson_series')->nullOnDelete();
            }

            if (! Schema::hasColumn('appointment_requests', 'series_occurrence_date')) {
                $table->date('series_occurrence_date')->nullable()->after('lesson_series_id');
            }

            if (! Schema::hasColumn('appointment_requests', 'confirmation_policy')) {
                $table->string('confirmation_policy')->default(ConfirmationPolicyEnum::Manual->value)->after('status')->index();
            }

            if (! Schema::hasColumn('appointment_requests', 'hold_expires_at')) {
                $table->timestamp('hold_expires_at')->nullable()->after('confirmation_policy')->index();
            }

            if (! Schema::hasColumn('appointment_requests', 'offered_window_starts_at')) {
                $table->timestamp('offered_window_starts_at')->nullable()->after('requested_ends_at')->index();
            }

            if (! Schema::hasColumn('appointment_requests', 'offered_window_ends_at')) {
                $table->timestamp('offered_window_ends_at')->nullable()->after('offered_window_starts_at')->index();
            }

            if (! Schema::hasColumn('appointment_requests', 'is_time_pinned')) {
                $table->boolean('is_time_pinned')->default(true)->after('offered_window_ends_at')->index();
            }

            $table->index(['site_id', 'portal_account_id'], 'appointment_site_portal_account_index');
            $table->unique(['lesson_series_id', 'series_occurrence_date'], 'appointment_lesson_series_occurrence_unique');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('appointment_requests')) {
            return;
        }

        Schema::table('appointment_requests', function (Blueprint $table): void {
            $table->dropUnique('appointment_lesson_series_occurrence_unique');
            $table->dropIndex('appointment_site_portal_account_index');
            $table->dropIndex('appointment_requests_confirmation_policy_index');
            $table->dropIndex('appointment_requests_hold_expires_at_index');
            $table->dropIndex('appointment_requests_offered_window_starts_at_index');
            $table->dropIndex('appointment_requests_offered_window_ends_at_index');
            $table->dropIndex('appointment_requests_is_time_pinned_index');
            $table->dropConstrainedForeignId('lesson_series_id');
            $table->dropConstrainedForeignId('portal_account_id');
            $table->dropConstrainedForeignId('site_id');
            $table->dropColumn([
                'series_occurrence_date',
                'confirmation_policy',
                'hold_expires_at',
                'offered_window_starts_at',
                'offered_window_ends_at',
                'is_time_pinned',
            ]);
        });
    }
};
