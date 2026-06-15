<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('booking_review_requests', function (Blueprint $table): void {
            if (! Schema::hasColumn('booking_review_requests', 'token_hash')) {
                $table->string('token_hash')->nullable()->unique()->after('status');
            }

            if (! Schema::hasColumn('booking_review_requests', 'token_expires_at')) {
                $table->timestamp('token_expires_at')->nullable()->index()->after('token_hash');
            }
        });
    }

    public function down(): void
    {
        Schema::table('booking_review_requests', function (Blueprint $table): void {
            if (Schema::hasColumn('booking_review_requests', 'token_expires_at')) {
                $table->dropIndex('booking_review_requests_token_expires_at_index');
                $table->dropColumn('token_expires_at');
            }

            if (Schema::hasColumn('booking_review_requests', 'token_hash')) {
                $table->dropUnique('booking_review_requests_token_hash_unique');
                $table->dropColumn('token_hash');
            }
        });
    }
};
