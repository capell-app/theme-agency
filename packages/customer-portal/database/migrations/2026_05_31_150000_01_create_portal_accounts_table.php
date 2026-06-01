<?php

declare(strict_types=1);

use Capell\CustomerPortal\Enums\PortalAccountStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-customer-portal.tables.accounts', 'portal_accounts');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->nullableMorphs('owner');
            $table->longText('email')->nullable();
            $table->string('email_hash', 64)->nullable();
            $table->longText('display_name')->nullable();
            $table->longText('profile')->nullable();
            $table->longText('preferences')->nullable();
            $table->string('status')->default(PortalAccountStatus::Active->value)->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['site_id', 'email_hash']);
            $table->index(['site_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-customer-portal.tables.accounts', 'portal_accounts'));
    }
};
