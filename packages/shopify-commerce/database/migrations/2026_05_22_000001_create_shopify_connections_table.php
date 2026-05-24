<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopify_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('shop_domain');
            $table->string('status')->index();
            $table->text('access_token')->nullable();
            $table->json('scopes')->nullable();
            $table->unsignedBigInteger('connected_by_user_id')->nullable()->index();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status')->nullable()->index();
            $table->timestamp('last_sync_started_at')->nullable();
            $table->timestamp('last_sync_queued_at')->nullable();
            $table->string('bulk_operation_id')->nullable()->index();
            $table->text('bulk_operation_url')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'shop_domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_connections');
    }
};
