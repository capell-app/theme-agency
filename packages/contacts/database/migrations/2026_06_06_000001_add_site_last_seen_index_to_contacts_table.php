<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const string INDEX_NAME = 'contacts_site_last_seen_index';

    public function up(): void
    {
        $tableName = $this->contactsTableName();

        if (
            ! Schema::hasTable($tableName)
            || ! Schema::hasColumn($tableName, 'site_id')
            || ! Schema::hasColumn($tableName, 'last_seen_at')
            || Schema::hasIndex($tableName, self::INDEX_NAME)
            || Schema::hasIndex($tableName, ['site_id', 'last_seen_at'])
        ) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->index(['site_id', 'last_seen_at'], self::INDEX_NAME);
        });
    }

    public function down(): void
    {
        $tableName = $this->contactsTableName();

        if (! Schema::hasTable($tableName) || ! Schema::hasIndex($tableName, self::INDEX_NAME)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropIndex(self::INDEX_NAME);
        });
    }

    private function contactsTableName(): string
    {
        $tableName = config('capell-contacts.tables.contacts', 'contacts');

        return is_string($tableName) && $tableName !== '' ? $tableName : 'contacts';
    }
};
