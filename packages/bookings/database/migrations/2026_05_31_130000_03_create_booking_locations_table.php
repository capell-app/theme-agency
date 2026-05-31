<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('physical')->index();
            $table->string('line1', 128)->nullable();
            $table->string('line2', 128)->nullable();
            $table->string('city', 64)->nullable();
            $table->string('state', 64)->nullable();
            $table->string('postal_code', 32)->nullable();
            $table->string('country', 64)->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('virtual_url')->nullable();
            $table->text('instructions')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->json('settings')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['active', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_locations');
    }
};
