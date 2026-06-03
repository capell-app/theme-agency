<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Data;

use Spatie\LaravelData\Data;

final class DocumentLifecycleHealthReportData extends Data
{
    /**
     * @param  list<string>  $missingTables
     * @param  list<string>  $missingAcceptanceColumns
     * @param  list<string>  $unprotectedTables
     * @param  list<string>  $unregisteredMorphAliases
     * @param  list<string>  $issues
     */
    public function __construct(
        public readonly string $status,
        public readonly bool $tablesPresent,
        public readonly bool $acceptanceColumnsPresent,
        public readonly bool $protectedTablesRegistered,
        public readonly bool $morphMapRegistered,
        public readonly bool $publishingRevisionListenerRegistered,
        public readonly array $missingTables,
        public readonly array $missingAcceptanceColumns,
        public readonly array $unprotectedTables,
        public readonly array $unregisteredMorphAliases,
        public readonly array $issues,
    ) {}
}
