<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-payments.tables.payment_intents', 'payment_intents');
        $customerTableName = config('capell-payments.tables.customers', 'payment_customers');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($customerTableName): void {
            $table->id();
            $table->string('provider')->index();
            $table->string('provider_payment_intent_id');
            $table->foreignId('payment_customer_id')->nullable()->constrained($customerTableName)->nullOnDelete();
            $table->string('provider_customer_id')->nullable()->index();
            $table->string('provider_session_id')->nullable()->index();
            $table->string('status')->index();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);
            $table->json('metadata')->nullable();
            $table->longText('provider_payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_payment_intent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-payments.tables.payment_intents', 'payment_intents'));
    }
};
