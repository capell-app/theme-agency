<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('capell-customer-portal.tables.support_request_replies', 'portal_support_request_replies');
        $supportRequestsTableName = config('capell-customer-portal.tables.support_requests', 'portal_support_requests');

        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($supportRequestsTableName): void {
            $table->id();
            $table->foreignId('portal_support_request_id')->constrained($supportRequestsTableName)->cascadeOnDelete();
            $table->string('sender_type')->index();
            $table->nullableMorphs('author', 'portal_support_request_replies_author_idx');
            $table->longText('message');
            $table->longText('attachments')->nullable();
            $table->timestamp('submitted_at')->index();
            $table->timestamps();

            $table->index(['portal_support_request_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('capell-customer-portal.tables.support_request_replies', 'portal_support_request_replies'));
    }
};
