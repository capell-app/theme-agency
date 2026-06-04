<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('comment_authors') || Schema::hasColumn('comment_authors', 'reply_notifications_disabled_at')) {
            return;
        }

        Schema::table('comment_authors', function (Blueprint $table): void {
            $table->timestamp('reply_notifications_disabled_at')->nullable()->index()->after('blocked_at');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('comment_authors') || ! Schema::hasColumn('comment_authors', 'reply_notifications_disabled_at')) {
            return;
        }

        Schema::table('comment_authors', function (Blueprint $table): void {
            $table->dropColumn('reply_notifications_disabled_at');
        });
    }
};
