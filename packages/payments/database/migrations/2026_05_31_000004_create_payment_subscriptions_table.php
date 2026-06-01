<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-payments.tables.subscriptions', 'payment_subscriptions');
        $customerTableName = config('capell-payments.tables.customers', 'payment_customers');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($customerTableName): void {
            $table->id();
            $table->string('provider')->index();
            $table->string('provider_subscription_id');
            $table->foreignId('payment_customer_id')->nullable()->constrained($customerTableName)->nullOnDelete();
            $table->string('provider_customer_id')->nullable()->index();
            $table->string('provider_session_id')->nullable()->index();
            $table->string('status')->index();
            $table->json('metadata')->nullable();
            $table->longText('provider_payload')->nullable();
            $table->timestamp('trial_ends_at')->nullable()->index();
            $table->timestamp('current_period_starts_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable()->index();
            $table->timestamp('cancel_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_subscription_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-payments.tables.subscriptions', 'payment_subscriptions'));
    }
};
