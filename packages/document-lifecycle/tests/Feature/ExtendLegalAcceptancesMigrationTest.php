<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

require_once dirname(__DIR__) . '/DocumentLifecycleTestCase.php';

function documentLifecycleExtendLegalAcceptancesMigration(): Migration
{
    return require dirname(__DIR__, 2)
        . '/database/migrations/2026_05_10_190868_03_extend_legal_acceptances_for_document_lifecycle.php';
}

it('reverses only the columns it added when rolling back', function (): void {
    expect(Schema::hasColumn('legal_acceptances', 'document_publication_id'))->toBeTrue()
        ->and(Schema::hasColumn('legal_acceptances', 'document_hash'))->toBeTrue();

    documentLifecycleExtendLegalAcceptancesMigration()->down();

    expect(Schema::hasColumn('legal_acceptances', 'document_publication_id'))->toBeFalse()
        ->and(Schema::hasColumn('legal_acceptances', 'document_hash'))->toBeFalse()
        ->and(Schema::hasIndex('legal_acceptances', ['document_key', 'document_publication_id']))->toBeFalse();
});

it('does not throw when rolling back twice (indexes already dropped)', function (): void {
    $migration = documentLifecycleExtendLegalAcceptancesMigration();

    $migration->down();

    expect(fn (): mixed => $migration->down())->not->toThrow(Throwable::class);
});
