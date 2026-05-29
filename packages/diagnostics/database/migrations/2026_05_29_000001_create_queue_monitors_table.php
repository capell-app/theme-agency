<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('queue_monitors')) {
            return;
        }

        Schema::create('queue_monitors', function (Blueprint $table): void {
            $table->id();
            $table->string('job_id')->index();
            $table->string('name')->nullable()->index();
            $table->string('queue')->nullable()->index();
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('finished_at')->nullable()->index();
            $table->boolean('failed')->default(false)->index();
            $table->integer('attempt')->default(0);
            $table->integer('progress')->nullable();
            $table->text('exception_message')->nullable();
            $table->string('tenant_id')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Diagnostics may be installed after the upstream package already created this table.
    }
};
