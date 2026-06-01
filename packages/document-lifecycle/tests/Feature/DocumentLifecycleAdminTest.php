<?php

declare(strict_types=1);

use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Filament\Resources\Documents\DocumentResource;
use Capell\DocumentLifecycle\Filament\Resources\Documents\Pages\EditDocument;
use Capell\DocumentLifecycle\Filament\Resources\Documents\RelationManagers\AcceptancesRelationManager;
use Capell\DocumentLifecycle\Filament\Resources\Documents\RelationManagers\PublicationsRelationManager;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Permission;

uses(CreatesAdminUser::class);

it('exposes controlled documents in the admin surface', function (): void {
    test()->actingAsAdmin();

    $document = Document::query()->create([
        'key' => 'terms',
        'title' => 'Terms of Service',
        'status' => DocumentStatusEnum::Active,
        'metadata' => ['source' => 'test'],
    ]);

    get(DocumentResource::getUrl())
        ->assertOk()
        ->assertSee('Terms of Service');

    get(DocumentResource::getUrl('edit', ['record' => $document]))
        ->assertOk()
        ->assertSee('terms');
});

it('shows controlled document publication and acceptance audit trails', function (): void {
    test()->actingAsAdmin();

    $document = Document::query()->create([
        'key' => 'terms',
        'title' => 'Terms of Service',
        'status' => DocumentStatusEnum::Active,
    ]);

    $publication = DocumentPublication::query()->create([
        'document_id' => $document->getKey(),
        'version_label' => '2026-05-14',
        'content_hash' => str_repeat('a', 64),
        'published_at' => Date::parse('2026-05-14 10:00:00'),
    ]);

    $acceptance = DocumentAcceptance::query()->create([
        'document_key' => 'terms',
        'document_version' => '2026-05-14',
        'document_publication_id' => $publication->getKey(),
        'document_hash' => str_repeat('a', 64),
        'accepted_at' => Date::parse('2026-05-14 11:00:00'),
        'context' => 'registration',
    ]);

    livewire(PublicationsRelationManager::class, [
        'ownerRecord' => $document,
        'pageClass' => EditDocument::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$publication])
        ->assertTableColumnStateSet('version_label', '2026-05-14', $publication);

    livewire(AcceptancesRelationManager::class, [
        'ownerRecord' => $document,
        'pageClass' => EditDocument::class,
    ])
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$acceptance])
        ->assertTableColumnStateSet('context', 'registration', $acceptance);
});

it('denies controlled document resources to panel users without document permissions', function (): void {
    test()->actingAsUser();

    $document = Document::query()->create([
        'key' => 'terms',
        'title' => 'Terms of Service',
        'status' => DocumentStatusEnum::Active,
    ]);

    get(DocumentResource::getUrl())->assertForbidden();
    get(DocumentResource::getUrl('edit', ['record' => $document]))->assertForbidden();
});

it('allows permitted users to list and edit controlled documents', function (): void {
    Permission::findOrCreate('ViewAny:Document', 'web');
    Permission::findOrCreate('View:Document', 'web');
    Permission::findOrCreate('Update:Document', 'web');

    test()->actingAs(test()->createUserWithPermission([
        'ViewAny:Document',
        'View:Document',
        'Update:Document',
    ]));

    $document = Document::query()->create([
        'key' => 'terms',
        'title' => 'Terms of Service',
        'status' => DocumentStatusEnum::Active,
    ]);

    expect(DocumentResource::canAccess())->toBeTrue()
        ->and(DocumentResource::canViewAny())->toBeTrue()
        ->and(DocumentResource::canEdit($document))->toBeTrue();
});
