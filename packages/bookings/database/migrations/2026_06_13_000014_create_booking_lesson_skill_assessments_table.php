<?php

declare(strict_types=1);

use Capell\Bookings\Enums\BookingLessonSkillStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_lesson_skill_assessments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_request_id')->constrained('appointment_requests')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('portal_account_id')->nullable()->constrained('portal_accounts')->nullOnDelete();
            $table->string('skill')->index();
            $table->string('status')->default(BookingLessonSkillStatusEnum::Introduced->value)->index();
            $table->unsignedTinyInteger('confidence')->nullable();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['appointment_request_id', 'skill'], 'booking_lesson_skill_assessments_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_lesson_skill_assessments');
    }
};
