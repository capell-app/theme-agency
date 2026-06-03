<?php

declare(strict_types=1);

namespace Capell\DocumentLifecycle\Actions;

use Capell\Core\Facades\CapellCore;
use Capell\DocumentLifecycle\Data\DocumentLifecycleHealthReportData;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Capell\PublishingStudio\Models\PublishingRevision;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildDocumentLifecycleHealthReportAction
{
    use AsAction;

    /**
     * @var list<string>
     */
    private const array REQUIRED_TABLES = [
        'document_lifecycle_documents',
        'document_lifecycle_publications',
        'legal_acceptances',
    ];

    /**
     * @var list<string>
     */
    private const array REQUIRED_ACCEPTANCE_COLUMNS = [
        'document_key',
        'document_version',
        'document_publication_id',
        'document_hash',
    ];

    /**
     * @var array<string, class-string>
     */
    private const array REQUIRED_MORPH_ALIASES = [
        'document_lifecycle_document' => Document::class,
        'document_lifecycle_publication' => DocumentPublication::class,
        'document_acceptance' => DocumentAcceptance::class,
    ];

    public function handle(): DocumentLifecycleHealthReportData
    {
        $issues = [];

        $missingTables = $this->missingTables();
        $tablesPresent = $missingTables === [];

        if (! $tablesPresent) {
            $issues[] = sprintf('Required table(s) not migrated: %s.', implode(', ', $missingTables));
        }

        $missingAcceptanceColumns = $this->missingAcceptanceColumns();
        $acceptanceColumnsPresent = $missingAcceptanceColumns === [];

        if (Schema::hasTable('legal_acceptances') && ! $acceptanceColumnsPresent) {
            $issues[] = sprintf(
                'legal_acceptances is missing Document Lifecycle column(s): %s.',
                implode(', ', $missingAcceptanceColumns),
            );
        }

        $unprotectedTables = $this->unprotectedTables();
        $protectedTablesRegistered = $unprotectedTables === [];

        if (! $protectedTablesRegistered) {
            $issues[] = sprintf('Audit table(s) not registered as protected: %s.', implode(', ', $unprotectedTables));
        }

        $unregisteredMorphAliases = $this->unregisteredMorphAliases();
        $morphMapRegistered = $unregisteredMorphAliases === [];

        if (! $morphMapRegistered) {
            $issues[] = sprintf('Morph alias(es) not registered: %s.', implode(', ', $unregisteredMorphAliases));
        }

        $publishingRevisionListenerRegistered = $this->publishingRevisionListenerRegistered();

        if (! $publishingRevisionListenerRegistered) {
            $issues[] = 'The Publishing Studio revision auto-publish listener is not registered.';
        }

        return new DocumentLifecycleHealthReportData(
            status: $issues === [] ? 'passed' : 'failed',
            tablesPresent: $tablesPresent,
            acceptanceColumnsPresent: $acceptanceColumnsPresent,
            protectedTablesRegistered: $protectedTablesRegistered,
            morphMapRegistered: $morphMapRegistered,
            publishingRevisionListenerRegistered: $publishingRevisionListenerRegistered,
            missingTables: $missingTables,
            missingAcceptanceColumns: $missingAcceptanceColumns,
            unprotectedTables: $unprotectedTables,
            unregisteredMorphAliases: $unregisteredMorphAliases,
            issues: $issues,
        );
    }

    /**
     * @return list<string>
     */
    private function missingTables(): array
    {
        return array_values(array_filter(
            self::REQUIRED_TABLES,
            static fn (string $table): bool => ! Schema::hasTable($table),
        ));
    }

    /**
     * @return list<string>
     */
    private function missingAcceptanceColumns(): array
    {
        if (! Schema::hasTable('legal_acceptances')) {
            return self::REQUIRED_ACCEPTANCE_COLUMNS;
        }

        return array_values(array_filter(
            self::REQUIRED_ACCEPTANCE_COLUMNS,
            static fn (string $column): bool => ! Schema::hasColumn('legal_acceptances', $column),
        ));
    }

    /**
     * @return list<string>
     */
    private function unprotectedTables(): array
    {
        $protectedTables = CapellCore::getProtectedTables();

        return array_values(array_filter(
            self::REQUIRED_TABLES,
            static fn (string $table): bool => ! in_array($table, $protectedTables, true),
        ));
    }

    /**
     * @return list<string>
     */
    private function unregisteredMorphAliases(): array
    {
        $morphMap = Relation::morphMap();

        $unregistered = [];

        foreach (self::REQUIRED_MORPH_ALIASES as $alias => $modelClass) {
            if (($morphMap[$alias] ?? null) !== $modelClass) {
                $unregistered[] = $alias;
            }
        }

        return $unregistered;
    }

    private function publishingRevisionListenerRegistered(): bool
    {
        return Event::hasListeners('eloquent.created: ' . PublishingRevision::class);
    }
}
