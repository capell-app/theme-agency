<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_notification_logs', function (Blueprint $table): void {
            $table->string('notification_key')->nullable()->after('type');
        });

        DB::table('event_notification_logs')
            ->whereNull('notification_key')
            ->update(['notification_key' => DB::raw('type')]);

        Schema::table('event_notification_logs', function (Blueprint $table): void {
            $table->unique(
                ['event_registration_id', 'type', 'recipient_email', 'notification_key'],
                'event_notification_logs_registration_type_email_key_unique',
            );

            $table->dropUnique('event_notification_logs_registration_type_email_unique');
        });
    }

    public function down(): void
    {
        Schema::table('event_notification_logs', function (Blueprint $table): void {
            $table->unique(
                ['event_registration_id', 'type', 'recipient_email'],
                'event_notification_logs_registration_type_email_unique',
            );

            $table->dropUnique('event_notification_logs_registration_type_email_key_unique');

            $table->dropColumn('notification_key');
        });
    }
};
