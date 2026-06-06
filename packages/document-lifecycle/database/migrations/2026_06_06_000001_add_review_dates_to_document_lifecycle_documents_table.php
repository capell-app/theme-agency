<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('document_lifecycle_documents')) {
            return;
        }

        Schema::table('document_lifecycle_documents', function (Blueprint $table): void {
            if (! Schema::hasColumn('document_lifecycle_documents', 'review_due_at')) {
                $table->timestamp('review_due_at')->nullable()->after('metadata')->index();
            }

            if (! Schema::hasColumn('document_lifecycle_documents', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('review_due_at')->index();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('document_lifecycle_documents')) {
            return;
        }

        Schema::table('document_lifecycle_documents', function (Blueprint $table): void {
            if (Schema::hasIndex('document_lifecycle_documents', ['expires_at'])) {
                $table->dropIndex(['expires_at']);
            }

            if (Schema::hasIndex('document_lifecycle_documents', ['review_due_at'])) {
                $table->dropIndex(['review_due_at']);
            }

            if (Schema::hasColumn('document_lifecycle_documents', 'expires_at')) {
                $table->dropColumn('expires_at');
            }

            if (Schema::hasColumn('document_lifecycle_documents', 'review_due_at')) {
                $table->dropColumn('review_due_at');
            }
        });
    }
};
