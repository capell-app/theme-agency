<?php

declare(strict_types=1);

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
