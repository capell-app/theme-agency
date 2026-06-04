<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_feed_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('provider')->index();
            $table->string('name');
            $table->string('status')->index();
            $table->json('credentials')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->string('sync_status')->nullable()->index();
            $table->timestamp('last_sync_started_at')->nullable();
            $table->timestamp('last_sync_queued_at')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'provider', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_feed_connections');
    }
};
