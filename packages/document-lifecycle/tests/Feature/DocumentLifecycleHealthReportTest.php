<?php

declare(strict_types=1);

use Capell\DocumentLifecycle\Actions\BuildDocumentLifecycleHealthReportAction;
use Capell\DocumentLifecycle\Health\DocumentLifecycleHealthCheck;
use Illuminate\Support\Facades\Schema;

require_once dirname(__DIR__) . '/DocumentLifecycleTestCase.php';

it('passes when tables, columns, protected tables, morph map, and listener are all wired', function (): void {
    $report = DocumentLifecycleHealthCheck::report();

    expect(DocumentLifecycleHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and($report->status)->toBe('passed')
        ->and($report->tablesPresent)->toBeTrue()
        ->and($report->acceptanceColumnsPresent)->toBeTrue()
        ->and($report->protectedTablesRegistered)->toBeTrue()
        ->and($report->morphMapRegistered)->toBeTrue()
        ->and($report->publishingRevisionListenerRegistered)->toBeTrue()
        ->and($report->missingTables)->toBe([])
        ->and($report->missingAcceptanceColumns)->toBe([])
        ->and($report->unprotectedTables)->toBe([])
        ->and($report->unregisteredMorphAliases)->toBe([])
        ->and($report->issues)->toBe([]);
});

it('fails and reports the missing table when a required table is absent', function (): void {
    Schema::dropIfExists('document_lifecycle_publications');

    $report = BuildDocumentLifecycleHealthReportAction::run();

    expect($report->status)->toBe('failed')
        ->and($report->tablesPresent)->toBeFalse()
        ->and($report->missingTables)->toContain('document_lifecycle_publications')
        ->and($report->issues)->toContain('Required table(s) not migrated: document_lifecycle_publications.');
});
