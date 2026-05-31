<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-contacts.tables.activities', 'contact_activities');
        $contactsTableName = config('capell-contacts.tables.contacts', 'contacts');
        $organisationsTableName = config('capell-contacts.tables.organisations', 'contact_organisations');
        $leadsTableName = config('capell-contacts.tables.leads', 'contact_leads');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($contactsTableName, $leadsTableName, $organisationsTableName): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained($contactsTableName)->nullOnDelete();
            $table->foreignId('organisation_id')->nullable()->constrained($organisationsTableName)->nullOnDelete();
            $table->foreignId('lead_id')->nullable()->constrained($leadsTableName)->nullOnDelete();
            $table->nullableMorphs('subject');
            $table->string('type')->index();
            $table->longText('summary')->nullable();
            $table->longText('payload')->nullable();
            $table->timestamp('occurred_at')->index();
            $table->timestamps();

            $table->index(['site_id', 'type', 'occurred_at'], 'contact_activities_site_type_time_idx');
            $table->index(['contact_id', 'occurred_at']);
            $table->index(['lead_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-contacts.tables.activities', 'contact_activities'));
    }
};
