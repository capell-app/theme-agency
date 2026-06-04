<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

require_once dirname(__DIR__) . '/DocumentLifecycleTestCase.php';

function documentLifecycleExtendLegalAcceptancesMigration(): object
{
    return require dirname(__DIR__, 2)
        . '/database/migrations/2026_05_10_190868_03_extend_legal_acceptances_for_document_lifecycle.php';
}

function documentLifecycleRollbackLegalAcceptancesMigration(object $migration): void
{
    (new ReflectionMethod($migration, 'down'))->invoke($migration);
}

it('reverses only the columns it added when rolling back', function (): void {
    expect(Schema::hasColumn('legal_acceptances', 'document_publication_id'))->toBeTrue()
        ->and(Schema::hasColumn('legal_acceptances', 'document_hash'))->toBeTrue();

    documentLifecycleRollbackLegalAcceptancesMigration(documentLifecycleExtendLegalAcceptancesMigration());

    expect(Schema::hasColumn('legal_acceptances', 'document_publication_id'))->toBeFalse()
        ->and(Schema::hasColumn('legal_acceptances', 'document_hash'))->toBeFalse()
        ->and(Schema::hasIndex('legal_acceptances', ['document_key', 'document_publication_id']))->toBeFalse();
});

it('does not throw when rolling back twice (indexes already dropped)', function (): void {
    $migration = documentLifecycleExtendLegalAcceptancesMigration();

    documentLifecycleRollbackLegalAcceptancesMigration($migration);

    expect(fn (): null => documentLifecycleRollbackLegalAcceptancesMigration($migration))->not->toThrow(Throwable::class);
});

it('extends and rolls back a pre-existing legal acceptances table without requiring package-only indexes', function (): void {
    Schema::dropIfExists('legal_acceptances');

    Schema::create('legal_acceptances', function (Blueprint $table): void {
        $table->id();
        $table->nullableMorphs('acceptor');
        $table->nullableMorphs('subject');
        $table->string('document_key')->index();
        $table->string('document_version');
        $table->timestamp('accepted_at')->nullable();
        $table->timestamps();
    });

    $migration = documentLifecycleExtendLegalAcceptancesMigration();
    (new ReflectionMethod($migration, 'up'))->invoke($migration);

    expect(Schema::hasColumn('legal_acceptances', 'document_publication_id'))->toBeTrue()
        ->and(Schema::hasColumn('legal_acceptances', 'document_hash'))->toBeTrue()
        ->and(Schema::hasIndex('legal_acceptances', ['document_key', 'document_publication_id']))->toBeTrue();

    documentLifecycleRollbackLegalAcceptancesMigration($migration);

    expect(Schema::hasTable('legal_acceptances'))->toBeTrue()
        ->and(Schema::hasColumn('legal_acceptances', 'document_publication_id'))->toBeFalse()
        ->and(Schema::hasColumn('legal_acceptances', 'document_hash'))->toBeFalse()
        ->and(Schema::hasColumn('legal_acceptances', 'acceptor_type'))->toBeTrue()
        ->and(Schema::hasColumn('legal_acceptances', 'subject_type'))->toBeTrue();
});

it('preserves legacy-owned columns and indexes with matching names when rolling back', function (): void {
    Schema::dropIfExists('legal_acceptances');

    Schema::create('legal_acceptances', function (Blueprint $table): void {
        $table->id();
        $table->nullableMorphs('acceptor');
        $table->nullableMorphs('subject');
        $table->string('document_key')->index();
        $table->string('document_version');
        $table->unsignedBigInteger('document_publication_id')->nullable();
        $table->string('document_hash', 64)->nullable();
        $table->timestamp('accepted_at')->nullable();
        $table->timestamps();

        $table->index(['document_key', 'document_publication_id'], 'legal_acceptances_doc_publication_lookup');
        $table->index(['acceptor_type', 'acceptor_id', 'subject_type', 'subject_id'], 'legal_acceptances_acceptor_subject_lookup');
    });

    $migration = documentLifecycleExtendLegalAcceptancesMigration();
    (new ReflectionMethod($migration, 'up'))->invoke($migration);

    documentLifecycleRollbackLegalAcceptancesMigration($migration);

    expect(Schema::hasColumn('legal_acceptances', 'document_publication_id'))->toBeTrue()
        ->and(Schema::hasColumn('legal_acceptances', 'document_hash'))->toBeTrue()
        ->and(Schema::hasIndex('legal_acceptances', 'legal_acceptances_doc_publication_lookup'))->toBeTrue()
        ->and(Schema::hasIndex('legal_acceptances', 'legal_acceptances_acceptor_subject_lookup'))->toBeTrue();
});

it('does not drop package-named indexes when only differently named legacy indexes exist', function (): void {
    Schema::dropIfExists('legal_acceptances');

    Schema::create('legal_acceptances', function (Blueprint $table): void {
        $table->id();
        $table->nullableMorphs('acceptor');
        $table->nullableMorphs('subject');
        $table->string('document_key')->index();
        $table->string('document_version');
        $table->unsignedBigInteger('document_publication_id')->nullable();
        $table->string('document_hash', 64)->nullable();
        $table->timestamp('accepted_at')->nullable();
        $table->timestamps();

        $table->index(['document_key', 'document_publication_id'], 'legacy_legal_acceptances_doc_publication_lookup');
        $table->index(['acceptor_type', 'acceptor_id', 'subject_type', 'subject_id'], 'legacy_legal_acceptances_acceptor_subject_lookup');
    });

    $migration = documentLifecycleExtendLegalAcceptancesMigration();
    (new ReflectionMethod($migration, 'up'))->invoke($migration);

    expect(fn (): null => documentLifecycleRollbackLegalAcceptancesMigration($migration))->not->toThrow(Throwable::class);

    expect(Schema::hasIndex('legal_acceptances', 'legacy_legal_acceptances_doc_publication_lookup'))->toBeTrue()
        ->and(Schema::hasIndex('legal_acceptances', 'legacy_legal_acceptances_acceptor_subject_lookup'))->toBeTrue();
});
