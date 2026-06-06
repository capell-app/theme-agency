<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('diagnostics_health_snapshots')) {
            return;
        }

        Schema::create('diagnostics_health_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('overall_status')->index();
            $table->unsignedTinyInteger('health_score')->index();
            $table->string('worst_severity')->nullable()->index();
            $table->unsignedInteger('declared_count');
            $table->unsignedInteger('implemented_count');
            $table->unsignedInteger('stub_count');
            $table->unsignedInteger('broken_count');
            $table->unsignedInteger('executed_count');
            $table->unsignedInteger('passed_count');
            $table->unsignedInteger('failed_count');
            $table->json('checks')->nullable();
            $table->timestamp('recorded_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostics_health_snapshots');
    }
};
