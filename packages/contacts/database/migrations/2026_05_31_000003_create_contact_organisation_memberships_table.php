<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-contacts.tables.organisation_memberships', 'contact_organisation_memberships');
        $contactsTableName = config('capell-contacts.tables.contacts', 'contacts');
        $organisationsTableName = config('capell-contacts.tables.organisations', 'contact_organisations');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($contactsTableName, $organisationsTableName): void {
            $table->id();
            $table->foreignId('contact_id')->constrained($contactsTableName)->cascadeOnDelete();
            $table->foreignId('organisation_id')->constrained($organisationsTableName)->cascadeOnDelete();
            $table->string('role')->nullable();
            $table->boolean('is_primary')->default(false)->index();
            $table->timestamps();

            $table->unique(['contact_id', 'organisation_id'], 'contact_organisation_member_unique');
            $table->index(['organisation_id', 'is_primary'], 'contact_organisation_primary_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-contacts.tables.organisation_memberships', 'contact_organisation_memberships'));
    }
};
