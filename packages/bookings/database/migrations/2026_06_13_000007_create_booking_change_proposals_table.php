<?php

declare(strict_types=1);

use Capell\Bookings\Enums\BookingChangeProposalPartyStatusEnum;
use Capell\Bookings\Enums\BookingChangeProposalStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_change_proposals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('appointment_request_id')->constrained('appointment_requests')->cascadeOnDelete();
            $table->timestamp('proposed_starts_at')->index();
            $table->timestamp('proposed_ends_at');
            $table->string('status')->default(BookingChangeProposalStatusEnum::Pending->value)->index();
            $table->text('reason')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_change_proposal_parties', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('booking_change_proposal_id')->constrained('booking_change_proposals')->cascadeOnDelete();
            $table->foreignId('portal_account_id')->nullable()->constrained('portal_accounts')->nullOnDelete();
            $table->string('party')->index();
            $table->string('status')->default(BookingChangeProposalPartyStatusEnum::Pending->value)->index();
            $table->string('token_hash', 64)->nullable()->index();
            $table->timestamp('token_expires_at')->nullable()->index();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['booking_change_proposal_id', 'party'], 'booking_change_proposal_parties_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_change_proposal_parties');
        Schema::dropIfExists('booking_change_proposals');
    }
};
