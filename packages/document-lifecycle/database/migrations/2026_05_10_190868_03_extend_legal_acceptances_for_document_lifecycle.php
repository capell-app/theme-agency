<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('legal_acceptances')) {
            Schema::create('legal_acceptances', function (Blueprint $table): void {
                $table->id();
                $table->nullableMorphs('acceptor');
                $table->nullableMorphs('subject');
                $table->string('document_key')->index();
                $table->string('document_version');
                $table->foreignId('document_publication_id')->nullable()->constrained('document_lifecycle_publications')->nullOnDelete();
                $table->string('document_hash', 64)->nullable();
                $table->string('legal_bundle_version')->nullable();
                $table->string('legal_bundle_hash', 64)->nullable();
                $table->json('legal_document_versions')->nullable();
                $table->timestamp('accepted_at')->index();
                $table->string('context')->nullable()->index();
                $table->string('ip_hash', 64)->nullable();
                $table->string('user_agent_hash', 64)->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['document_key', 'document_version']);
                $table->index(['document_key', 'document_publication_id'], 'legal_acceptances_doc_publication_lookup');
                $table->index(['acceptor_type', 'acceptor_id', 'subject_type', 'subject_id'], 'legal_acceptances_acceptor_subject_lookup');
            });

            return;
        }

        Schema::table('legal_acceptances', function (Blueprint $table): void {
            if (! Schema::hasColumn('legal_acceptances', 'document_publication_id')) {
                $table->foreignId('document_publication_id')
                    ->nullable()
                    ->after('document_version')
                    ->constrained('document_lifecycle_publications')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('legal_acceptances', 'document_hash')) {
                $table->string('document_hash', 64)->nullable()->after('document_publication_id');
            }
        });

        if (! Schema::hasIndex('legal_acceptances', ['document_key', 'document_publication_id'])) {
            Schema::table('legal_acceptances', function (Blueprint $table): void {
                $table->index(['document_key', 'document_publication_id'], 'legal_acceptances_doc_publication_lookup');
            });
        }

        if (! Schema::hasIndex('legal_acceptances', ['acceptor_type', 'acceptor_id', 'subject_type', 'subject_id'])) {
            Schema::table('legal_acceptances', function (Blueprint $table): void {
                $table->index(['acceptor_type', 'acceptor_id', 'subject_type', 'subject_id'], 'legal_acceptances_acceptor_subject_lookup');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('legal_acceptances')) {
            return;
        }

        if (Schema::hasIndex('legal_acceptances', ['document_key', 'document_publication_id'])) {
            Schema::table('legal_acceptances', function (Blueprint $table): void {
                $table->dropIndex('legal_acceptances_doc_publication_lookup');
            });
        }

        if (Schema::hasIndex('legal_acceptances', ['acceptor_type', 'acceptor_id', 'subject_type', 'subject_id'])) {
            Schema::table('legal_acceptances', function (Blueprint $table): void {
                $table->dropIndex('legal_acceptances_acceptor_subject_lookup');
            });
        }

        Schema::table('legal_acceptances', function (Blueprint $table): void {
            if (Schema::hasColumn('legal_acceptances', 'document_publication_id')) {
                $table->dropConstrainedForeignId('document_publication_id');
            }

            if (Schema::hasColumn('legal_acceptances', 'document_hash')) {
                $table->dropColumn('document_hash');
            }
        });
    }
};
