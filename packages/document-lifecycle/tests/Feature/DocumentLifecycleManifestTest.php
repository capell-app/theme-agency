<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\RegistersExtensionAdminResource;
use Capell\Core\Contracts\Extensions\RunsExtensionMigration;
use Capell\Core\Contracts\Extensions\RunsScheduledExtensionJob;
use Capell\DocumentLifecycle\Filament\Resources\Documents\DocumentResource;
use Capell\DocumentLifecycle\Manifest\DocumentLifecycleMigrationsContribution;
use Capell\DocumentLifecycle\Manifest\DocumentLifecycleModelsContribution;
use Capell\DocumentLifecycle\Manifest\DocumentLifecycleRetentionScheduleContribution;
use Capell\DocumentLifecycle\Manifest\DocumentResourceContribution;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;

/**
 * @return array<string, mixed>
 */
function documentLifecycleManifest(): array
{
    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($manifest), RuntimeException::class, 'Expected document lifecycle manifest array.');

    return $manifest;
}

/**
 * @return array<string, mixed>
 */
function documentLifecycleComposerManifest(): array
{
    $composerManifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/composer.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($composerManifest), RuntimeException::class, 'Expected document lifecycle composer manifest array.');

    return $composerManifest;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return list<array<string, mixed>>
 */
function documentLifecycleManifestList(array $manifest, string $key): array
{
    $items = $manifest[$key] ?? [];

    throw_unless(is_array($items), RuntimeException::class, 'Expected document lifecycle manifest list.');

    $list = [];

    foreach ($items as $item) {
        if (is_array($item)) {
            $list[] = $item;
        }
    }

    return $list;
}

it('declares the document admin resource as a manifest contribution', function (): void {
    $manifest = documentLifecycleManifest();

    $contribution = null;

    foreach (documentLifecycleManifestList($manifest, 'contributes') as $manifestContribution) {
        if (($manifestContribution['type'] ?? null) === 'admin-resource') {
            $contribution = $manifestContribution;

            break;
        }
    }

    expect($contribution)
        ->toBeArray()
        ->and($contribution['class'] ?? null)->toBe(DocumentResourceContribution::class)
        ->and($contribution['resourceClass'] ?? null)->toBe(DocumentResource::class)
        ->and($contribution['group'] ?? null)->toBe('Document')
        ->and(DocumentResourceContribution::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(class_implements(DocumentResourceContribution::class))->toContain(RegistersExtensionAdminResource::class);
});

it('declares only real package surfaces in the manifest', function (): void {
    $manifest = documentLifecycleManifest();

    expect($manifest['surfaces'] ?? null)->toBe(['admin', 'console'])
        ->and($manifest['providers']['frontend'] ?? null)->toBe([]);
});

it('declares the retention command as a scheduled job contribution', function (): void {
    $manifest = documentLifecycleManifest();

    $contribution = null;

    foreach (documentLifecycleManifestList($manifest, 'contributes') as $manifestContribution) {
        if (($manifestContribution['type'] ?? null) === 'scheduled-job') {
            $contribution = $manifestContribution;

            break;
        }
    }

    expect($contribution)
        ->toBeArray()
        ->and($contribution['class'] ?? null)->toBe(DocumentLifecycleRetentionScheduleContribution::class)
        ->and($contribution['command'] ?? null)->toBe('capell:document-lifecycle:archive-expired')
        ->and($contribution['frequency'] ?? null)->toBe('daily')
        ->and(DocumentLifecycleRetentionScheduleContribution::compatibleCapellApiVersion())->toBe('^4.0')
        ->and(class_implements(DocumentLifecycleRetentionScheduleContribution::class))->toContain(RunsScheduledExtensionJob::class);
});

it('declares model and migration contributions for install diagnostics', function (): void {
    $manifest = documentLifecycleManifest();
    $contributions = collect(documentLifecycleManifestList($manifest, 'contributes'));

    $models = $contributions->firstWhere('class', DocumentLifecycleModelsContribution::class);
    $migrations = $contributions->firstWhere('class', DocumentLifecycleMigrationsContribution::class);

    expect($models)->toBeArray()
        ->and($models['type'])->toBe('model')
        ->and($models['modelClasses'])->toBe([
            Document::class,
            DocumentPublication::class,
            DocumentAcceptance::class,
        ])
        ->and($models['morphAliases'])->toBe([
            'document_lifecycle_document' => Document::class,
            'document_lifecycle_publication' => DocumentPublication::class,
            'document_acceptance' => DocumentAcceptance::class,
        ])
        ->and($migrations)->toBeArray()
        ->and($migrations['type'])->toBe('migration')
        ->and($migrations['migrationFiles'])->toBe([
            '2026_05_10_190868_01_create_document_lifecycle_documents_table',
            '2026_05_10_190868_02_create_document_lifecycle_publications_table',
            '2026_05_10_190868_03_extend_legal_acceptances_for_document_lifecycle',
            '2026_06_06_000001_add_review_dates_to_document_lifecycle_documents_table',
        ])
        ->and($migrations['requiredTables'])->toBe($manifest['database']['requiredTables'])
        ->and($manifest['contributionTraceability']['deferredContributions'])->toBe([])
        ->and(class_implements(DocumentLifecycleMigrationsContribution::class))->toContain(RunsExtensionMigration::class);
});

it('keeps manifest and composer package copy aligned with shipped capabilities', function (): void {
    $manifest = documentLifecycleManifest();
    $composerManifest = documentLifecycleComposerManifest();

    $summary = 'Version-pinned acceptance evidence for controlled documents in Capell: register documents, publish hashed versions from admin or Publishing Studio, and record who accepted which version and when.';

    expect($manifest['marketplace']['summary'] ?? null)->toBe($summary)
        ->and($composerManifest['description'] ?? null)->toBe($summary)
        ->and($manifest['description'] ?? null)->toContain('admin-managed records')
        ->and($manifest['description'] ?? null)->toContain('Publishing Studio revisions')
        ->and($manifest['description'] ?? null)->toContain('authenticated Customer Portal feed')
        ->and($summary)->not->toContain('court-ready')
        ->and($summary)->not->toContain('immutable');
});

it('promotes committed screenshot contract captures into marketplace media', function (): void {
    $manifest = documentLifecycleManifest();
    $screenshotContract = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/docs/screenshots.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    throw_unless(is_array($screenshotContract), RuntimeException::class, 'Expected screenshot contract array.');

    $contractEntries = $screenshotContract['entries'] ?? [];
    $marketplace = $manifest['marketplace'] ?? [];

    throw_unless(is_array($contractEntries), RuntimeException::class, 'Expected screenshot contract entries array.');
    throw_unless(is_array($marketplace), RuntimeException::class, 'Expected marketplace manifest array.');

    $requiredScreenshotPaths = [];

    foreach ($contractEntries as $contractEntry) {
        if (! is_array($contractEntry)) {
            continue;
        }

        if (($contractEntry['required'] ?? false) !== true) {
            continue;
        }

        $screenshotPath = $contractEntry['screenshotPath'] ?? null;

        if (is_string($screenshotPath)) {
            $requiredScreenshotPaths[] = str_replace('packages/document-lifecycle/', '', $screenshotPath);
        }
    }

    $manifestScreenshotEntries = $marketplace['screenshots'] ?? [];

    throw_unless(is_array($manifestScreenshotEntries), RuntimeException::class, 'Expected marketplace screenshot entries array.');

    $manifestScreenshotPaths = [];

    foreach ($manifestScreenshotEntries as $manifestScreenshotEntry) {
        if (! is_array($manifestScreenshotEntry)) {
            continue;
        }

        $screenshotPath = $manifestScreenshotEntry['path'] ?? null;

        if (is_string($screenshotPath)) {
            $manifestScreenshotPaths[] = $screenshotPath;
        }
    }

    expect($manifestScreenshotPaths)->toContain('docs/assets/marketplace/extension-card.jpg');

    foreach ($manifestScreenshotPaths as $manifestScreenshotPath) {
        expect(file_exists(dirname(__DIR__, 2) . '/' . $manifestScreenshotPath))->toBeTrue();
    }

    foreach ($requiredScreenshotPaths as $requiredScreenshotPath) {
        expect($manifestScreenshotPaths)->toContain($requiredScreenshotPath);
    }
});
