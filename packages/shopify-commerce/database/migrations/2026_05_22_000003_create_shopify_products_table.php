<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopify_products', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('connection_id')->constrained('shopify_connections')->cascadeOnDelete();
            $table->string('shopify_gid');
            $table->string('handle')->index();
            $table->string('title')->index();
            $table->string('search_text')->nullable()->index();
            $table->string('status')->index();
            $table->json('options')->nullable();
            $table->json('featured_image')->nullable();
            $table->json('raw_snapshot')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
            $table->unique(['connection_id', 'shopify_gid']);
            $table->index(['connection_id', 'search_text']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_products');
    }
};
