<?php

declare(strict_types=1);

use Capell\Bookings\Enums\BookingOwnerPromptStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_owner_prompts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('type')->index();
            $table->string('status')->default(BookingOwnerPromptStatusEnum::Proposed->value)->index();
            $table->string('title');
            $table->text('body');
            $table->json('context')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_owner_prompts');
    }
};
