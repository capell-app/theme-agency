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
        $tableName = config('capell-privacy-center.tables.consent_policies', 'privacy_consent_policies');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->string('type')->default(PolicyType::Privacy->value)->index();
            $table->string('key')->index();
            $table->string('version');
            $table->string('title');
            $table->string('content_hash', 64)->nullable();
            $table->timestamp('effective_at')->nullable()->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('retired_at')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'key', 'version'], 'privacy_policies_site_key_version_unique');
            $table->index(['site_id', 'type', 'published_at'], 'privacy_policies_site_type_published_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-privacy-center.tables.consent_policies', 'privacy_consent_policies'));
    }
};
