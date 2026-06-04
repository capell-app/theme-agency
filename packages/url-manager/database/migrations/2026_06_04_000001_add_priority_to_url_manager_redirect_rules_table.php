<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('url_manager_redirect_rules') || Schema::hasColumn('url_manager_redirect_rules', 'priority')) {
            return;
        }

        Schema::table('url_manager_redirect_rules', function (Blueprint $table): void {
            $table->integer('priority')->default(0)->after('status');
            $table->index(['status', 'match_type', 'priority'], 'url_manager_redirect_rules_status_match_priority');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('url_manager_redirect_rules') || ! Schema::hasColumn('url_manager_redirect_rules', 'priority')) {
            return;
        }

        Schema::table('url_manager_redirect_rules', function (Blueprint $table): void {
            $table->dropIndex('url_manager_redirect_rules_status_match_priority');
            $table->dropColumn('priority');
        });
    }
};
