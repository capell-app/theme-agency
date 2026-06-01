<?php

declare(strict_types=1);

use Capell\DocumentLifecycle\Actions\ComputeDocumentContentHashAction;
use Capell\DocumentLifecycle\Actions\PublishDocumentAction;
use Capell\DocumentLifecycle\Actions\RegisterDocumentAction;
use Capell\DocumentLifecycle\Actions\ResolveLatestDocumentPublicationAction;
use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Capell\PublishingStudio\Enums\PublishingRevisionEventEnum;
use Capell\PublishingStudio\Models\PublishingRevision;
use Capell\PublishingStudio\Tests\Integration\Fixtures\WorkspaceDraftableFixture;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

require_once dirname(__DIR__) . '/DocumentLifecycleTestCase.php';

it('publishes controlled documents with stable content hashes', function (): void {
    $document = RegisterDocumentAction::run('terms', 'Terms of Service');

    $first = PublishDocumentAction::run(
        document: $document,
        content: ['body' => 'Version one', 'title' => 'Terms'],
        versionLabel: '2026-05-07',
        metadata: ['source' => 'cms'],
    );
    $second = PublishDocumentAction::run(
        document: $document->refresh(),
        content: ['title' => 'Terms', 'body' => 'Version two'],
        versionLabel: '2026-05-08',
    );

    expect($first)->toBeInstanceOf(DocumentPublication::class)
        ->and($first->content_hash)->toHaveLength(64)
        ->and($second->content_hash)->toHaveLength(64)
        ->and($second->content_hash)->not->toBe($first->content_hash)
        ->and($document->refresh()->status)->toBe(DocumentStatusEnum::Active)
        ->and(ResolveLatestDocumentPublicationAction::run('terms')->is($second))->toBeTrue();
});

it('normalises associative payload order before hashing', function (): void {
    $first = ComputeDocumentContentHashAction::run([
        'title' => 'Terms',
        'body' => [
            'b' => 'Second',
            'a' => 'First',
        ],
    ]);

    $second = ComputeDocumentContentHashAction::run([
        'body' => [
            'a' => 'First',
            'b' => 'Second',
        ],
        'title' => 'Terms',
    ]);

    expect($first)->toBe($second);
});

it('keeps document publication versions immutable', function (): void {
    $document = RegisterDocumentAction::run('terms', 'Terms of Service');

    $first = PublishDocumentAction::run(
        document: $document,
        content: ['body' => 'Version one'],
        versionLabel: '2026-05-07',
    );

    $again = PublishDocumentAction::run(
        document: $document->refresh(),
        content: ['body' => 'Version one'],
        versionLabel: '2026-05-07',
    );

    expect($again->is($first))->toBeTrue()
        ->and(DocumentPublication::query()->count())->toBe(1);

    PublishDocumentAction::run(
        document: $document->refresh(),
        content: ['body' => 'Changed content'],
        versionLabel: '2026-05-07',
    );
})->throws(LogicException::class);

it('does not mutate an existing publication when a version hash conflicts', function (): void {
    $document = RegisterDocumentAction::run('terms', 'Terms of Service');
    $publication = PublishDocumentAction::run(
        document: $document,
        content: ['body' => 'Version one'],
        versionLabel: '2026-05-07',
    );
    $originalHash = $publication->content_hash;

    expect(function () use ($document): void {
        PublishDocumentAction::run(
            document: $document->refresh(),
            content: ['body' => 'Changed content'],
            versionLabel: '2026-05-07',
        );
    })->toThrow(LogicException::class);

    expect($publication->refresh()->content_hash)->toBe($originalHash)
        ->and(DocumentPublication::query()->count())->toBe(1);
});

it('creates document publications from publishing revision rows for registered documents', function (): void {
    createDocumentLifecycleDraftableFixtureTable();

    $record = WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => 0,
        'uuid' => (string) Str::uuid(),
        'name' => 'Published terms',
    ]);

    $document = RegisterDocumentAction::run('terms', 'Terms of Service', $record);

    $revision = PublishingRevision::query()->create([
        'uuid' => (string) Str::uuid(),
        'revisionable_type' => WorkspaceDraftableFixture::class,
        'revisionable_id' => $record->getKey(),
        'revisionable_uuid' => $record->uuid,
        'version' => 1,
        'event_type' => PublishingRevisionEventEnum::Published,
        'after_payload' => [
            'id' => $record->getKey(),
            'uuid' => $record->uuid,
            'name' => 'Published terms',
        ],
    ]);

    $publication = DocumentPublication::query()->firstOrFail();

    expect($publication->document_id)->toBe($document->getKey())
        ->and($publication->published_revision_id)->toBe($revision->getKey())
        ->and($publication->version_label)->toBe('r1')
        ->and($publication->content_hash)->toBe(ComputeDocumentContentHashAction::run($revision->after_payload))
        ->and(($publication->metadata ?? [])['publishing_revision_uuid'] ?? null)->toBe($revision->uuid);
});

it('moves a documentable pointer when a published revision replaces a uuid-matched live row', function (): void {
    createDocumentLifecycleDraftableFixtureTable();

    $sharedUuid = (string) Str::uuid();
    $oldLive = WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => 0,
        'uuid' => $sharedUuid,
        'name' => 'Old terms',
    ]);
    $newLive = WorkspaceDraftableFixture::query()->withoutGlobalScopes()->create([
        'workspace_id' => 0,
        'uuid' => $sharedUuid,
        'name' => 'New terms',
    ]);

    $document = RegisterDocumentAction::run('terms', 'Terms of Service', $oldLive);

    PublishingRevision::query()->create([
        'uuid' => (string) Str::uuid(),
        'revisionable_type' => WorkspaceDraftableFixture::class,
        'revisionable_id' => $newLive->getKey(),
        'revisionable_uuid' => $sharedUuid,
        'version' => 1,
        'event_type' => PublishingRevisionEventEnum::Published,
        'before_payload' => [
            'id' => $oldLive->getKey(),
            'uuid' => $sharedUuid,
            'name' => 'Old terms',
        ],
        'after_payload' => [
            'id' => $newLive->getKey(),
            'uuid' => $sharedUuid,
            'name' => 'New terms',
        ],
    ]);

    expect($document->refresh()->documentable_id)->toBe($newLive->getKey())
        ->and(DocumentPublication::query()->whereBelongsTo($document)->exists())->toBeTrue();
});

function createDocumentLifecycleDraftableFixtureTable(): void
{
    Schema::dropIfExists('workspace_draftable_fixtures');

    Schema::create('workspace_draftable_fixtures', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('workspace_id')->default(0)->index();
        $table->unsignedBigInteger('shadowed_by_workspace_id')->default(0)->index();
        $table->uuid('uuid');
        $table->string('name');
        $table->timestamps();
    });
}
