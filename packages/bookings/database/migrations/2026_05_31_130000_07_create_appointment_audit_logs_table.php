<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_request_id')->constrained('appointment_requests')->cascadeOnDelete();
            $table->string('event')->index();
            $table->string('status_from')->nullable();
            $table->string('status_to')->nullable();
            $table->text('message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['appointment_request_id', 'event'], 'appointment_audit_request_event_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointment_audit_logs');
    }
};
