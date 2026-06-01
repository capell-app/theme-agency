<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-payments.tables.download_entitlements', 'payment_download_entitlements');
        $checkoutSessionTableName = config('capell-payments.tables.checkout_sessions', 'payment_checkout_sessions');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($checkoutSessionTableName): void {
            $table->id();
            $table->foreignId('checkout_session_id')->constrained($checkoutSessionTableName)->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->index();
            $table->string('download_key')->index();
            $table->string('download_name');
            $table->string('disk')->default('local');
            $table->text('path');
            $table->string('file_name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('fulfilled_at')->nullable()->index();
            $table->unsignedInteger('download_count')->default(0);
            $table->timestamp('last_downloaded_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['checkout_session_id', 'download_key'], 'payment_download_entitlements_session_key_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-payments.tables.download_entitlements', 'payment_download_entitlements'));
    }
};
