<?php

declare(strict_types=1);

use Capell\PrivacyCenter\Enums\PrivacyRequestStatus;
use Capell\PrivacyCenter\Enums\PrivacyRequestType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-privacy-center.tables.privacy_requests', 'privacy_requests');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->nullableMorphs('requester', 'privacy_requests_requester_idx');
            $table->nullableMorphs('subject', 'privacy_requests_subject_idx');
            $table->string('reference')->unique();
            $table->string('type')->default(PrivacyRequestType::Access->value)->index();
            $table->string('status')->default(PrivacyRequestStatus::Submitted->value)->index();
            $table->string('email_hash', 64)->nullable()->index();
            $table->timestamp('submitted_at')->index();
            $table->timestamp('due_at')->nullable()->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable()->index();
            $table->timestamp('rejected_at')->nullable()->index();
            $table->string('rejection_reason')->nullable();
            $table->json('workflow_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status', 'due_at'], 'privacy_requests_site_status_due_idx');
            $table->index(['subject_type', 'subject_id', 'status'], 'privacy_requests_subject_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-privacy-center.tables.privacy_requests', 'privacy_requests'));
    }
};
