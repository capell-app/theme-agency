<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $windowsTable = Config::string('capell-live-chat.tables.availability_windows', 'live_chat_availability_windows');
        $exceptionsTable = Config::string('capell-live-chat.tables.availability_exceptions', 'live_chat_availability_exceptions');

        if (! Schema::hasTable($windowsTable)) {
            Schema::create($windowsTable, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
                $table->unsignedTinyInteger('day_of_week')->index();
                $table->time('opens_at');
                $table->time('closes_at');
                $table->string('timezone')->default('UTC');
                $table->string('label')->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();

                $table->index(['site_id', 'day_of_week', 'is_active'], 'live_chat_availability_site_day_active_index');
            });
        }

        if (! Schema::hasTable($exceptionsTable)) {
            Schema::create($exceptionsTable, function (Blueprint $table): void {
                $table->id();
                $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
                $table->date('date')->index();
                $table->boolean('is_available')->default(false)->index();
                $table->time('opens_at')->nullable();
                $table->time('closes_at')->nullable();
                $table->string('timezone')->default('UTC');
                $table->longText('message')->nullable();
                $table->timestamps();

                $table->unique(['site_id', 'date'], 'live_chat_availability_site_date_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(Config::string('capell-live-chat.tables.availability_exceptions', 'live_chat_availability_exceptions'));
        Schema::dropIfExists(Config::string('capell-live-chat.tables.availability_windows', 'live_chat_availability_windows'));
    }
};
