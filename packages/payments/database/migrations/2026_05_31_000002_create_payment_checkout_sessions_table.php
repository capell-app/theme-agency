<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-payments.tables.checkout_sessions', 'payment_checkout_sessions');
        $customerTableName = config('capell-payments.tables.customers', 'payment_customers');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($customerTableName): void {
            $table->id();
            $table->string('provider')->index();
            $table->string('provider_session_id');
            $table->foreignId('payment_customer_id')->nullable()->constrained($customerTableName)->nullOnDelete();
            $table->foreignId('site_id')->nullable()->index();
            $table->string('mode')->index();
            $table->string('purpose')->index();
            $table->string('status')->index();
            $table->text('url')->nullable();
            $table->string('currency', 3)->nullable();
            $table->unsignedBigInteger('amount_subtotal')->nullable();
            $table->unsignedBigInteger('amount_total')->nullable();
            $table->string('provider_customer_id')->nullable()->index();
            $table->string('provider_payment_intent_id')->nullable()->index();
            $table->string('provider_subscription_id')->nullable()->index();
            $table->string('billable_type')->nullable();
            $table->string('billable_id')->nullable();
            $table->string('payable_type')->nullable();
            $table->string('payable_id')->nullable();
            $table->string('source_type')->nullable();
            $table->string('source_id')->nullable();
            $table->string('reference_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->longText('provider_payload')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('completed_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['provider', 'provider_session_id']);
            $table->index(['billable_type', 'billable_id']);
            $table->index(['payable_type', 'payable_id']);
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-payments.tables.checkout_sessions', 'payment_checkout_sessions'));
    }
};
