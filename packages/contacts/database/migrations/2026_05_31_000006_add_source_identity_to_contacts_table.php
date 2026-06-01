<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-contacts.tables.contacts', 'contacts');

        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (! Schema::hasColumn($tableName, 'source_key')) {
                $table->string('source_key')->nullable()->after('source_id');
            }

            if (! Schema::hasColumn($tableName, 'source_identifier')) {
                $table->longText('source_identifier')->nullable()->after('source_key');
            }

            if (! Schema::hasColumn($tableName, 'source_identifier_hash')) {
                $table->string('source_identifier_hash', 64)->nullable()->after('source_identifier');
            }
        });

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (Schema::hasColumn($tableName, 'source_key') && Schema::hasColumn($tableName, 'source_identifier_hash')) {
                $table->unique(['site_id', 'source_key', 'source_identifier_hash'], 'contacts_site_source_identifier_unique');
            }
        });
    }

    public function down(): void
    {
        $tableName = config('capell-contacts.tables.contacts', 'contacts');

        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (Schema::hasColumn($tableName, 'source_key') && Schema::hasColumn($tableName, 'source_identifier_hash')) {
                $table->dropUnique('contacts_site_source_identifier_unique');
            }

            foreach (['source_identifier_hash', 'source_identifier', 'source_key'] as $column) {
                if (Schema::hasColumn($tableName, $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
