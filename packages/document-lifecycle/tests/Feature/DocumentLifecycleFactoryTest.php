<?php

declare(strict_types=1);

use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Capell\PublishingStudio\Enums\PublishingRevisionEventEnum;
use Capell\PublishingStudio\Models\PublishingRevision;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Support\Str;

require_once dirname(__DIR__) . '/DocumentLifecycleTestCase.php';

it('creates documents, publications, and acceptances through package factories', function (): void {
    $user = User::factory()->create();
    $document = Document::factory()
        ->active()
        ->metadata(['owner' => 'legal'])
        ->create();
    $publication = DocumentPublication::factory()
        ->document($document)
        ->metadata(['source' => 'factory'])
        ->create();
    $acceptance = DocumentAcceptance::factory()
        ->forPublication($publication)
        ->acceptor($user)
        ->subject($user)
        ->context('registration')
        ->metadata(['flow' => 'factory'])
        ->create();

    expect($document)->toBeInstanceOf(Document::class)
        ->and($document->status)->toBe(DocumentStatusEnum::Active)
        ->and($document->metadata)->toBe(['owner' => 'legal'])
        ->and($publication)->toBeInstanceOf(DocumentPublication::class)
        ->and($publication->document_id)->toBe($document->getKey())
        ->and($publication->content_hash)->toHaveLength(64)
        ->and($publication->metadata)->toBe(['source' => 'factory'])
        ->and($acceptance)->toBeInstanceOf(DocumentAcceptance::class)
        ->and($acceptance->document_key)->toBe($document->key)
        ->and($acceptance->document_version)->toBe($publication->version_label)
        ->and($acceptance->document_publication_id)->toBe($publication->getKey())
        ->and($acceptance->document_hash)->toBe($publication->content_hash)
        ->and($acceptance->acceptor_type)->toBe($user->getMorphClass())
        ->and($acceptance->acceptor_id)->toBe($user->getKey())
        ->and($acceptance->subject_type)->toBe($user->getMorphClass())
        ->and($acceptance->subject_id)->toBe($user->getKey())
        ->and($acceptance->context)->toBe('registration')
        ->and($acceptance->metadata)->toBe(['flow' => 'factory']);
});

it('creates publication factory state from a Publishing Studio revision', function (): void {
    $document = Document::factory()->active()->create();
    $revision = PublishingRevision::query()->create([
        'uuid' => (string) Str::uuid(),
        'revisionable_type' => Document::class,
        'revisionable_id' => $document->getKey(),
        'version' => 3,
        'event_type' => PublishingRevisionEventEnum::Published,
        'after_payload' => [
            'id' => $document->getKey(),
            'title' => $document->title,
        ],
    ]);

    $publication = DocumentPublication::factory()
        ->document($document)
        ->fromRevision($revision)
        ->create();

    expect($publication->published_revision_id)->toBe($revision->getKey())
        ->and($publication->version_label)->toBe('r3')
        ->and($publication->metadata['publishing_revision_uuid'] ?? null)->toBe($revision->uuid)
        ->and($publication->metadata['revisionable_type'] ?? null)->toBe(Document::class)
        ->and($publication->content_hash)->toHaveLength(64);
});

it('provides translated labels and badge colours for document statuses', function (): void {
    expect(DocumentStatusEnum::Draft->getLabel())->toBe(__('capell-document-lifecycle::navigation.status.draft'))
        ->and(DocumentStatusEnum::Draft->getColor())->toBe('gray')
        ->and(DocumentStatusEnum::Active->getLabel())->toBe(__('capell-document-lifecycle::navigation.status.active'))
        ->and(DocumentStatusEnum::Active->getColor())->toBe('success')
        ->and(DocumentStatusEnum::Archived->getLabel())->toBe(__('capell-document-lifecycle::navigation.status.archived'))
        ->and(DocumentStatusEnum::Archived->getColor())->toBe('warning');
});
