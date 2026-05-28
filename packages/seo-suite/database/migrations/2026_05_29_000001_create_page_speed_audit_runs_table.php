<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('page_speed_audit_runs')) {
            return;
        }

        Schema::create('page_speed_audit_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('trigger')->index();
            $table->string('status')->index();
            $table->json('scope')->nullable();
            $table->unsignedInteger('target_count')->default(0);
            $table->unsignedInteger('success_count')->default(0);
            $table->unsignedInteger('failure_count')->default(0);
            $table->nullableMorphs('requested_by', 'page_speed_audit_runs_requested_by_idx');
            $table->string('notification_status')->default('not_requested');
            $table->text('error_message')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_speed_audit_runs');
    }
};
