<?php

declare(strict_types=1);

use Capell\AutomationStudio\Enums\AutomationRuleStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('automation_rules')) {
            return;
        }

        Schema::create('automation_rules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('trigger_type')->index();
            $table->string('status')->default(AutomationRuleStatus::Active->value)->index();
            $table->longText('conditions')->nullable();
            $table->longText('actions');
            $table->longText('settings')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'trigger_type', 'status'], 'automation_rules_site_trigger_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rules');
    }
};
