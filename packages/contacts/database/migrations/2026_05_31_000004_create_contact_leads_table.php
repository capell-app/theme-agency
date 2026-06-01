<?php

declare(strict_types=1);

use Capell\Contacts\Enums\LeadStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-contacts.tables.leads', 'contact_leads');
        $contactsTableName = config('capell-contacts.tables.contacts', 'contacts');
        $organisationsTableName = config('capell-contacts.tables.organisations', 'contact_organisations');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($contactsTableName, $organisationsTableName): void {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained($contactsTableName)->nullOnDelete();
            $table->foreignId('organisation_id')->nullable()->constrained($organisationsTableName)->nullOnDelete();
            $table->nullableMorphs('source');
            $table->string('title')->nullable();
            $table->string('status')->default(LeadStatus::New->value)->index();
            $table->decimal('value_amount', 12, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->longText('context')->nullable();
            $table->timestamp('captured_at')->nullable()->index();
            $table->timestamp('qualified_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->index(['site_id', 'status']);
            $table->index(['contact_id', 'status']);
            $table->index(['organisation_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-contacts.tables.leads', 'contact_leads'));
    }
};
