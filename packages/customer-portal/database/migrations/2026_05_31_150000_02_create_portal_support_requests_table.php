<?php

declare(strict_types=1);

use Capell\CustomerPortal\Enums\SupportRequestPriority;
use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-customer-portal.tables.support_requests', 'portal_support_requests');
        $accountsTableName = config('capell-customer-portal.tables.accounts', 'portal_accounts');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($accountsTableName): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('portal_account_id')->constrained($accountsTableName)->cascadeOnDelete();
            $table->string('status')->default(SupportRequestStatus::Open->value)->index();
            $table->string('priority')->default(SupportRequestPriority::Normal->value)->index();
            $table->longText('subject');
            $table->longText('message');
            $table->longText('requester_email')->nullable();
            $table->string('requester_email_hash', 64)->nullable()->index();
            $table->string('source')->nullable()->index();
            $table->string('external_reference')->nullable()->index();
            $table->longText('context')->nullable();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamp('resolved_at')->nullable()->index();
            $table->timestamp('closed_at')->nullable()->index();
            $table->timestamps();

            $table->index(['site_id', 'status', 'priority']);
            $table->index(['portal_account_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-customer-portal.tables.support_requests', 'portal_support_requests'));
    }
};
