<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_feed_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('connection_id')->constrained('social_feed_connections')->cascadeOnDelete();
            $table->string('provider')->index();
            $table->string('external_id');
            $table->string('type')->index();
            $table->text('text')->nullable();
            $table->text('permalink')->nullable();
            $table->text('media_url')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->string('author_name')->nullable();
            $table->text('author_avatar_url')->nullable();
            $table->json('raw')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['connection_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_feed_items');
    }
};
