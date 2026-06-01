<?php

declare(strict_types=1);

use Capell\AutomationStudio\Enums\AutomationRunStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('automation_runs')) {
            return;
        }

        Schema::create('automation_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('automation_rule_id')->nullable()->constrained('automation_rules')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('rule_key')->nullable()->index();
            $table->string('action_key')->nullable()->index();
            $table->string('trigger_type')->index();
            $table->string('action_type')->nullable()->index();
            $table->string('source_type')->nullable()->index();
            $table->string('source_id')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->unsignedSmallInteger('attempt_number')->default(1);
            $table->unsignedSmallInteger('max_attempts')->nullable();
            $table->timestamp('queued_at')->nullable()->index();
            $table->string('status')->default(AutomationRunStatus::Pending->value)->index();
            $table->text('message')->nullable();
            $table->longText('payload')->nullable();
            $table->longText('context')->nullable();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable()->index();
            $table->timestamps();

            $table->index(['site_id', 'trigger_type', 'status'], 'automation_runs_site_trigger_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_runs');
    }
};
