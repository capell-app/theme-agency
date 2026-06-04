<?php

declare(strict_types=1);

use Capell\DocumentLifecycle\Actions\ArchiveDocumentAction;
use Capell\DocumentLifecycle\Actions\RestoreDocumentAction;
use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentPublication;

it('archives controlled documents', function (): void {
    $document = Document::factory()->active()->create();

    $archivedDocument = ArchiveDocumentAction::run($document);

    expect($archivedDocument->status)->toBe(DocumentStatusEnum::Archived)
        ->and($document->refresh()->status)->toBe(DocumentStatusEnum::Archived);
});

it('restores archived documents to active when they have publications', function (): void {
    $document = Document::factory()->archived()->create();

    DocumentPublication::factory()->document($document)->create();

    $restoredDocument = RestoreDocumentAction::run($document);

    expect($restoredDocument->status)->toBe(DocumentStatusEnum::Active)
        ->and($document->refresh()->status)->toBe(DocumentStatusEnum::Active);
});

it('restores archived documents to draft when they have no publications', function (): void {
    $document = Document::factory()->archived()->create();

    $restoredDocument = RestoreDocumentAction::run($document);

    expect($restoredDocument->status)->toBe(DocumentStatusEnum::Draft)
        ->and($document->refresh()->status)->toBe(DocumentStatusEnum::Draft);
});
