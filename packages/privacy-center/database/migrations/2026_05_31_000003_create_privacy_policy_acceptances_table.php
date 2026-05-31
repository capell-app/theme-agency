<?php

declare(strict_types=1);

use Capell\PrivacyCenter\Enums\PolicyType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-privacy-center.tables.policy_acceptances', 'privacy_policy_acceptances');
        $policiesTableName = config('capell-privacy-center.tables.consent_policies', 'privacy_consent_policies');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($policiesTableName): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->nullableMorphs('subject', 'privacy_policy_acceptances_subject_idx');
            $table->foreignId('policy_id')->nullable()->constrained($policiesTableName)->nullOnDelete();
            $table->string('policy_type')->default(PolicyType::Privacy->value)->index();
            $table->string('policy_key')->index();
            $table->string('policy_version')->index();
            $table->string('context')->nullable()->index();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent_hash', 64)->nullable();
            $table->timestamp('accepted_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'policy_key', 'policy_version'], 'privacy_policy_acceptances_policy_idx');
            $table->index(['subject_type', 'subject_id', 'policy_key'], 'privacy_policy_acceptances_subject_policy_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-privacy-center.tables.policy_acceptances', 'privacy_policy_acceptances'));
    }
};
