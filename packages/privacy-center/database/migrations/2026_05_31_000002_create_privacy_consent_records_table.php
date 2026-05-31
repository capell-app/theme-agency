<?php

declare(strict_types=1);

use Capell\PrivacyCenter\Enums\ConsentDecision;
use Capell\PrivacyCenter\Enums\CookieCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-privacy-center.tables.consent_records', 'privacy_consent_records');
        $policiesTableName = config('capell-privacy-center.tables.consent_policies', 'privacy_consent_policies');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($policiesTableName): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->nullableMorphs('subject', 'privacy_consent_records_subject_idx');
            $table->nullableMorphs('source', 'privacy_consent_records_source_idx');
            $table->foreignId('policy_id')->nullable()->constrained($policiesTableName)->nullOnDelete();
            $table->string('policy_version')->nullable()->index();
            $table->string('category')->default(CookieCategory::Essential->value)->index();
            $table->string('decision')->default(ConsentDecision::Denied->value)->index();
            $table->string('jurisdiction')->nullable()->index();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->json('evidence')->nullable();
            $table->timestamp('decided_at')->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('revoked_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'category', 'decision'], 'privacy_consent_records_site_category_idx');
            $table->index(['subject_type', 'subject_id', 'category', 'decided_at'], 'privacy_consent_records_subject_decision_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-privacy-center.tables.consent_records', 'privacy_consent_records'));
    }
};
