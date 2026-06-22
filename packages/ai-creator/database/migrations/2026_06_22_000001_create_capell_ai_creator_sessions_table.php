<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('capell_ai_creator_sessions')) {
            return;
        }

        Schema::create('capell_ai_creator_sessions', function (Blueprint $table): void {
            $table->id();
            $table->text('intent');
            $table->json('questions')->nullable();
            $table->json('answers')->nullable();
            $table->json('package_recommendations')->nullable();
            $table->json('package_requirements')->nullable();
            $table->json('content_plan')->nullable();
            $table->json('page_plan')->nullable();
            $table->json('layout_plan')->nullable();
            $table->json('theme_plan')->nullable();
            $table->json('preview_output')->nullable();
            $table->string('status')->default('draft')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('capell_ai_creator_sessions');
    }
};
