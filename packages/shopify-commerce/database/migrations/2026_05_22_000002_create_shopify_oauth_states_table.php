<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shopify_oauth_states', function (Blueprint $table): void {
            $table->id();
            $table->string('nonce', 80)->unique();
            $table->string('shop_domain')->index();
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shopify_oauth_states');
    }
};
