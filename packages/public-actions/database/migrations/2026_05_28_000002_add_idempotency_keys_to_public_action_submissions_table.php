<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-public-actions.tables.submissions', 'public_action_submissions');

        if (! Schema::hasTable($tableName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (! Schema::hasColumn($tableName, 'idempotency_key')) {
                $table->string('idempotency_key', 128)->nullable()->after('source_id');
            }
        });

        Schema::table($tableName, function (Blueprint $table) use ($tableName): void {
            if (Schema::hasColumn($tableName, 'idempotency_key')) {
                $table->unique(['public_action_id', 'idempotency_key'], 'public_action_submissions_idempotency_unique');
            }
        });
    }

    public function down(): void
    {
        $tableName = config('capell-public-actions.tables.submissions', 'public_action_submissions');

        if (! Schema::hasTable($tableName) || ! Schema::hasColumn($tableName, 'idempotency_key')) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table): void {
            $table->dropUnique('public_action_submissions_idempotency_unique');
            $table->dropColumn('idempotency_key');
        });
    }
};
