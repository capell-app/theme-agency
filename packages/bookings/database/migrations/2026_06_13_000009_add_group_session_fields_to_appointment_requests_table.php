<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointment_requests', function (Blueprint $table): void {
            $table->foreignId('group_session_id')
                ->nullable()
                ->after('lesson_series_id')
                ->constrained('booking_group_sessions')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('appointment_requests', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('group_session_id');
        });
    }
};
