<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-payments.tables.disputes', 'payment_disputes');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->string('provider')->index();
            $table->string('provider_dispute_id');
            $table->string('provider_payment_intent_id')->nullable()->index();
            $table->string('provider_charge_id')->nullable()->index();
            $table->string('status')->index();
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);
            $table->string('reason')->nullable()->index();
            $table->boolean('is_charge_refundable')->default(false);
            $table->timestamp('evidence_due_at')->nullable()->index();
            $table->longText('provider_payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_dispute_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-payments.tables.disputes', 'payment_disputes'));
    }
};
