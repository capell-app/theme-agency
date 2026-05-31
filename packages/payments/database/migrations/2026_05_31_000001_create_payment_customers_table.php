<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-payments.tables.customers', 'payment_customers');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->string('provider')->index();
            $table->string('provider_customer_id')->nullable();
            $table->foreignId('site_id')->nullable()->index();
            $table->string('billable_type')->nullable();
            $table->string('billable_id')->nullable();
            $table->text('email')->nullable();
            $table->string('name')->nullable();
            $table->json('metadata')->nullable();
            $table->longText('provider_payload')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_customer_id']);
            $table->index(['billable_type', 'billable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-payments.tables.customers', 'payment_customers'));
    }
};
