<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopify_customers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('connection_id')->constrained('shopify_connections')->cascadeOnDelete();
            $table->string('shopify_gid');
            $table->text('email')->nullable();
            $table->string('email_hash')->nullable()->index();
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('phone')->nullable();
            $table->boolean('accepts_marketing')->default(false)->index();
            $table->string('marketing_state')->nullable()->index();
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('total_spent_amount', 18, 6)->default(0);
            $table->char('total_spent_currency', 3)->nullable();
            $table->json('raw_snapshot')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['connection_id', 'shopify_gid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_customers');
    }
};
