<?php

declare(strict_types=1);

use Capell\Contacts\Enums\OrganisationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-contacts.tables.organisations', 'contact_organisations');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->string('name');
            $table->string('name_key');
            $table->string('domain')->nullable();
            $table->string('website')->nullable();
            $table->longText('profile')->nullable();
            $table->string('status')->default(OrganisationStatus::Active->value)->index();
            $table->timestamps();

            $table->unique(['site_id', 'name_key']);
            $table->index(['site_id', 'domain']);
            $table->index(['site_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-contacts.tables.organisations', 'contact_organisations'));
    }
};
