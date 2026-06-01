<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_staff_members', function (Blueprint $table): void {
            $table->id();
            $table->string('display_name');
            $table->string('title')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable();
            $table->string('timezone')->default('UTC');
            $table->string('profile_url')->nullable();
            $table->string('calendar_feed_token', 80)->nullable()->unique();
            $table->boolean('active')->default(true)->index();
            $table->json('settings')->nullable();
            $table->json('meta')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['active', 'display_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_staff_members');
    }
};
