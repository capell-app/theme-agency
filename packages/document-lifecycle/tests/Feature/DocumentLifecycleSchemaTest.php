<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Capell\DocumentLifecycle\Providers\DocumentLifecycleServiceProvider;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once dirname(__DIR__) . '/DocumentLifecycleTestCase.php';

it('loads controlled document and compatibility acceptance tables', function (): void {
    expect(Schema::hasTable('document_lifecycle_documents'))->toBeTrue()
        ->and(Schema::hasTable('document_lifecycle_publications'))->toBeTrue()
        ->and(Schema::hasTable('legal_acceptances'))->toBeTrue()
        ->and(Schema::hasColumns('legal_acceptances', [
            'document_key',
            'document_version',
            'document_publication_id',
            'document_hash',
        ]))->toBeTrue();
});

it('keeps document keys unique and stable', function (): void {
    Document::query()->create([
        'key' => 'terms',
        'title' => 'Terms',
        'status' => DocumentStatusEnum::Active,
    ]);

    expect(fn (): Document => Document::query()->create([
        'key' => 'terms',
        'title' => 'Replacement terms',
        'status' => DocumentStatusEnum::Active,
    ]))->toThrow(QueryException::class);
});

it('adopts existing legal acceptance rows through the package model', function (): void {
    DB::table('legal_acceptances')->insert([
        'document_key' => 'terms',
        'document_version' => '2026-05-07',
        'accepted_at' => now(),
        'context' => 'legacy',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $acceptance = DocumentAcceptance::query()->firstOrFail();

    expect($acceptance->document_key)->toBe('terms')
        ->and($acceptance->document_version)->toBe('2026-05-07')
        ->and($acceptance->document_publication_id)->toBeNull()
        ->and($acceptance->document_hash)->toBeNull();
});

it('extends a pre-existing legal acceptances table without replacing it', function (): void {
    Schema::dropIfExists('legal_acceptances');

    Schema::create('legal_acceptances', function (Blueprint $table): void {
        $table->id();
        $table->nullableMorphs('acceptor');
        $table->nullableMorphs('subject');
        $table->string('document_key')->index();
        $table->string('document_version');
        $table->timestamp('accepted_at')->index();
        $table->timestamps();
    });

    DB::table('legal_acceptances')->insert([
        'document_key' => 'privacy',
        'document_version' => 'legacy-v1',
        'accepted_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $migration = include dirname(__DIR__, 2) . '/database/migrations/2026_05_10_190868_03_extend_legal_acceptances_for_document_lifecycle.php';
    $migration->up();
    $migration->up();

    expect(Schema::hasColumns('legal_acceptances', [
        'document_publication_id',
        'document_hash',
    ]))->toBeTrue()
        ->and(DocumentAcceptance::query()->firstOrFail()->document_version)->toBe('legacy-v1');
});

it('registers package models and protected tables when installed', function (): void {
    CapellCore::forcePackageInstalled(DocumentLifecycleServiceProvider::$packageName);
    CapellCore::registerModels([
        Document::class,
        DocumentAcceptance::class,
        DocumentPublication::class,
    ]);

    foreach (['document_lifecycle_documents', 'document_lifecycle_publications', 'legal_acceptances'] as $table) {
        CapellCore::registerProtectedTable($table);
    }

    expect(CapellCore::getModels())->toContain(Document::class)
        ->and(CapellCore::getModels())->toContain(DocumentPublication::class)
        ->and(CapellCore::getModels())->toContain(DocumentAcceptance::class)
        ->and(CapellCore::getProtectedTables())->toContain('document_lifecycle_documents')
        ->and(CapellCore::getProtectedTables())->toContain('document_lifecycle_publications')
        ->and(CapellCore::getProtectedTables())->toContain('legal_acceptances');
});
