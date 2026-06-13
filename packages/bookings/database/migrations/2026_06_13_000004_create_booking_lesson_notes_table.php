<?php

declare(strict_types=1);

use Capell\Bookings\Enums\LessonNoteVisibilityEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_lesson_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_request_id')->constrained('appointment_requests')->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignId('portal_account_id')->nullable()->constrained('portal_accounts')->nullOnDelete();
            $table->string('visibility')->default(LessonNoteVisibilityEnum::Private->value)->index();
            $table->string('summary')->nullable();
            $table->text('body')->nullable();
            $table->json('photo_media_ids')->nullable();
            $table->timestamp('metadata_stripped_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['site_id', 'portal_account_id', 'occurred_at'], 'booking_lesson_notes_portal_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_lesson_notes');
    }
};
