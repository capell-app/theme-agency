<?php

declare(strict_types=1);

use Capell\PrivacyCenter\Enums\RetentionAction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-privacy-center.tables.retention_rules', 'privacy_retention_rules');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('data_domain')->index();
            $table->string('record_type')->nullable()->index();
            $table->unsignedInteger('retention_days');
            $table->string('action')->default(RetentionAction::Delete->value)->index();
            $table->string('legal_basis')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'data_domain', 'record_type'], 'privacy_retention_rules_unique_domain');
            $table->index(['site_id', 'is_active', 'action'], 'privacy_retention_rules_active_action_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-privacy-center.tables.retention_rules', 'privacy_retention_rules'));
    }
};
