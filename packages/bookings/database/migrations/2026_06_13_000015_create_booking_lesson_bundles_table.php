<?php

declare(strict_types=1);

use Capell\Bookings\Enums\BookingLessonBundleStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_lesson_bundles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('portal_account_id')->nullable()->constrained('portal_accounts')->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained('booking_services')->nullOnDelete();
            $table->string('status')->default(BookingLessonBundleStatusEnum::Active->value)->index();
            $table->string('name');
            $table->unsignedSmallInteger('credits_purchased');
            $table->unsignedSmallInteger('credits_remaining');
            $table->unsignedInteger('price_paid_pence')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_lesson_bundles');
    }
};
