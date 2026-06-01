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
        DB::table('event_notification_logs')
            ->select([
                'event_registration_id',
                'type',
                'recipient_email',
                DB::raw('MIN(id) as keep_id'),
            ])
            ->whereNotNull('event_registration_id')
            ->whereNotNull('recipient_email')
            ->groupBy('event_registration_id', 'type', 'recipient_email')
            ->havingRaw('COUNT(*) > 1')
            ->orderBy('keep_id')
            ->each(function (object $duplicateGroup): void {
                DB::table('event_notification_logs')
                    ->where('event_registration_id', $duplicateGroup->event_registration_id)
                    ->where('type', $duplicateGroup->type)
                    ->where('recipient_email', $duplicateGroup->recipient_email)
                    ->where('id', '<>', (int) $duplicateGroup->keep_id)
                    ->delete();
            });

        Schema::table('event_notification_logs', function (Blueprint $table): void {
            $table->unique(
                ['event_registration_id', 'type', 'recipient_email'],
                'event_notification_logs_registration_type_email_unique',
            );
        });
    }

    public function down(): void
    {
        Schema::table('event_notification_logs', function (Blueprint $table): void {
            $table->dropUnique('event_notification_logs_registration_type_email_unique');
        });
    }
};
