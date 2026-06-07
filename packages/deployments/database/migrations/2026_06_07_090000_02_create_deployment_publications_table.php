<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_publications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deployment_connection_id')->constrained()->cascadeOnDelete();
            $table->string('provider')->index();
            $table->string('repo_owner');
            $table->string('repo_name');
            $table->string('composer_package');
            $table->string('constraint')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('commit_sha')->nullable()->index();
            $table->unsignedBigInteger('pull_request_id')->nullable();
            $table->string('pull_request_url')->nullable();
            $table->string('status')->default('pending')->index();
            $table->boolean('dry_run')->default(false);
            $table->timestamp('status_checked_at')->nullable();
            $table->timestamps();

            $table->index(['deployment_connection_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_publications');
    }
};
