<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private bool $addedDocumentPublicationIdColumn = false;

    private bool $addedDocumentHashColumn = false;

    private bool $addedDocumentPublicationIndex = false;

    private bool $addedAcceptorSubjectIndex = false;

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

        $needsDocumentPublicationIdColumn = ! Schema::hasColumn('legal_acceptances', 'document_publication_id');
        $needsDocumentHashColumn = ! Schema::hasColumn('legal_acceptances', 'document_hash');

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

        $this->addedDocumentPublicationIdColumn = $needsDocumentPublicationIdColumn;
        $this->addedDocumentHashColumn = $needsDocumentHashColumn;

        if (! Schema::hasIndex('legal_acceptances', ['document_key', 'document_publication_id'])) {
            Schema::table('legal_acceptances', function (Blueprint $table): void {
                $table->index(['document_key', 'document_publication_id'], 'legal_acceptances_doc_publication_lookup');
            });

            $this->addedDocumentPublicationIndex = true;
        }

        if (! Schema::hasIndex('legal_acceptances', ['acceptor_type', 'acceptor_id', 'subject_type', 'subject_id'])) {
            Schema::table('legal_acceptances', function (Blueprint $table): void {
                $table->index(['acceptor_type', 'acceptor_id', 'subject_type', 'subject_id'], 'legal_acceptances_acceptor_subject_lookup');
            });

            $this->addedAcceptorSubjectIndex = true;
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('legal_acceptances')) {
            return;
        }

        $hasDocumentPublicationForeignKey = $this->hasDocumentPublicationForeignKey();
        $shouldDropDocumentPublicationIdColumn = $this->addedDocumentPublicationIdColumn || $hasDocumentPublicationForeignKey;
        $shouldDropDocumentHashColumn = $this->addedDocumentHashColumn || $hasDocumentPublicationForeignKey;

        if (
            Schema::hasIndex('legal_acceptances', 'legal_acceptances_doc_publication_lookup')
            && ($this->addedDocumentPublicationIndex || $shouldDropDocumentPublicationIdColumn)
        ) {
            Schema::table('legal_acceptances', function (Blueprint $table): void {
                $table->dropIndex('legal_acceptances_doc_publication_lookup');
            });
        }

        if (
            Schema::hasIndex('legal_acceptances', 'legal_acceptances_acceptor_subject_lookup')
            && $this->addedAcceptorSubjectIndex
        ) {
            Schema::table('legal_acceptances', function (Blueprint $table): void {
                $table->dropIndex('legal_acceptances_acceptor_subject_lookup');
            });
        }

        if (
            $hasDocumentPublicationForeignKey
            || $shouldDropDocumentPublicationIdColumn
            || $shouldDropDocumentHashColumn
        ) {
            Schema::table('legal_acceptances', function (Blueprint $table) use (
                $hasDocumentPublicationForeignKey,
                $shouldDropDocumentPublicationIdColumn,
                $shouldDropDocumentHashColumn,
            ): void {
                if ($hasDocumentPublicationForeignKey) {
                    $table->dropForeign(['document_publication_id']);
                }

                if (
                    $shouldDropDocumentPublicationIdColumn
                    && Schema::hasColumn('legal_acceptances', 'document_publication_id')
                ) {
                    $table->dropColumn('document_publication_id');
                }

                if (
                    $shouldDropDocumentHashColumn
                    && Schema::hasColumn('legal_acceptances', 'document_hash')
                ) {
                    $table->dropColumn('document_hash');
                }
            });
        }
    }

    private function hasDocumentPublicationForeignKey(): bool
    {
        if (! Schema::hasColumn('legal_acceptances', 'document_publication_id')) {
            return false;
        }

        foreach (Schema::getForeignKeys('legal_acceptances') as $foreignKey) {
            /** @var array{columns?: list<string>, foreign_table?: string} $foreignKey */
            if (
                ($foreignKey['columns'] ?? []) === ['document_publication_id']
                && ($foreignKey['foreign_table'] ?? null) === 'document_lifecycle_publications'
            ) {
                return true;
            }
        }

        return false;
    }
};
