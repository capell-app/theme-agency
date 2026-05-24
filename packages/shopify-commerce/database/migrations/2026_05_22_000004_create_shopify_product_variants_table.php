<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopify_product_variants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('product_id')->constrained('shopify_products')->cascadeOnDelete();
            $table->string('shopify_gid');
            $table->string('title');
            $table->decimal('price_amount', 18, 6)->default(0);
            $table->char('price_currency', 3)->default('USD');
            $table->boolean('available_for_sale')->default(false);
            $table->json('selected_options')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'shopify_gid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_product_variants');
    }
};
