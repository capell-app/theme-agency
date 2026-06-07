<?php

declare(strict_types=1);

use Capell\DocumentLifecycle\Actions\ComputeDocumentContentHashAction;
use Capell\DocumentLifecycle\Enums\DocumentStatusEnum;
use Capell\DocumentLifecycle\Filament\Resources\Documents\DocumentResource;
use Capell\DocumentLifecycle\Filament\Resources\Documents\Pages\CreateDocument;
use Capell\DocumentLifecycle\Filament\Resources\Documents\Pages\EditDocument;
use Capell\DocumentLifecycle\Filament\Resources\Documents\Pages\ListDocuments;
use Capell\DocumentLifecycle\Filament\Resources\Documents\RelationManagers\AcceptancesRelationManager;
use Capell\DocumentLifecycle\Filament\Resources\Documents\RelationManagers\PublicationsRelationManager;
use Capell\DocumentLifecycle\Models\Document;
use Capell\DocumentLifecycle\Models\DocumentAcceptance;
use Capell\DocumentLifecycle\Models\DocumentPublication;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Livewire\livewire;

use Spatie\Permission\Models\Permission;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('admin'));
});

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

it('keeps the controlled document index within the manifest query budget', function (): void {
    test()->actingAsAdmin();

    Document::factory()
        ->count(5)
        ->active()
        ->create()
        ->each(static function (Document $document): void {
            DocumentPublication::factory()->document($document)->create();
        });

    $queries = 0;

    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        if (str_starts_with(strtolower($query->sql), 'select')) {
            $queries++;
        }
    });

    livewire(ListDocuments::class)->assertSuccessful();

    $manifest = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2) . '/capell.json'),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($queries)->toBeLessThanOrEqual((int) data_get($manifest, 'performance.adminQueryBudget', 40));
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

it('registers controlled documents from the admin create page', function (): void {
    test()->actingAsAdmin();

    get(DocumentResource::getUrl('create'))->assertOk();

    livewire(CreateDocument::class)
        ->assertSuccessful()
        ->fillForm([
            'key' => 'Terms of Service',
            'title' => 'Terms of Service',
            'status' => DocumentStatusEnum::Draft->value,
            'metadata' => ['owner' => 'legal'],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas('document_lifecycle_documents', [
        'key' => 'terms_of_service',
        'title' => 'Terms of Service',
        'status' => DocumentStatusEnum::Draft->value,
    ]);
});

it('archives and restores controlled documents from table actions', function (): void {
    test()->actingAsAdmin();

    $document = Document::factory()->active()->create([
        'key' => 'terms',
        'title' => 'Terms of Service',
    ]);

    DocumentPublication::factory()->document($document)->create([
        'version_label' => '2026-06-04',
    ]);

    livewire(ListDocuments::class)
        ->assertSuccessful()
        ->assertActionVisible(TestAction::make('archive')->table($document))
        ->assertActionHidden(TestAction::make('restore')->table($document))
        ->callAction(TestAction::make('archive')->table($document))
        ->assertHasNoActionErrors()
        ->assertNotified(__('capell-document-lifecycle::navigation.messages.document_archived'));

    expect($document->refresh()->status)->toBe(DocumentStatusEnum::Archived);

    livewire(ListDocuments::class)
        ->assertSuccessful()
        ->assertActionHidden(TestAction::make('archive')->table($document))
        ->assertActionVisible(TestAction::make('restore')->table($document))
        ->callAction(TestAction::make('restore')->table($document))
        ->assertHasNoActionErrors()
        ->assertNotified(__('capell-document-lifecycle::navigation.messages.document_restored'));

    expect($document->refresh()->status)->toBe(DocumentStatusEnum::Active);
});

it('publishes controlled document versions from table actions', function (): void {
    test()->actingAsAdmin();

    $admin = auth()->user();

    throw_unless($admin instanceof Model, RuntimeException::class, 'Expected an authenticated admin model.');

    $document = Document::factory()->draft()->create([
        'key' => 'terms',
        'title' => 'Terms of Service',
    ]);

    livewire(ListDocuments::class)
        ->assertSuccessful()
        ->assertActionVisible(TestAction::make('publish_version')->table($document))
        ->callAction(TestAction::make('publish_version')->table($document), data: [
            'version_label' => '2026-06-04',
            'content' => 'Terms updated for June 2026.',
            'metadata_note' => 'Approved by legal.',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified(__('capell-document-lifecycle::navigation.messages.version_published'));

    $publication = $document->refresh()->publications()->firstOrFail();

    expect($document->status)->toBe(DocumentStatusEnum::Active)
        ->and($publication->version_label)->toBe('2026-06-04')
        ->and($publication->content_hash)->toBe(ComputeDocumentContentHashAction::run('Terms updated for June 2026.'))
        ->and($publication->published_actor_type)->toBe($admin->getMorphClass())
        ->and($publication->published_actor_id)->toBe($admin->getKey())
        ->and($publication->metadata['admin_note'] ?? null)->toBe('Approved by legal.')
        ->and($publication->metadata['content_snapshot'] ?? null)->toBe('Terms updated for June 2026.')
        ->and($publication->metadata['content_snapshot_format'] ?? null)->toBe('text');
});

it('records admin acceptances from table actions', function (): void {
    test()->actingAsAdmin();

    $admin = auth()->user();

    throw_unless($admin instanceof Model, RuntimeException::class, 'Expected an authenticated admin model.');

    $document = Document::factory()->active()->create([
        'key' => 'terms',
        'title' => 'Terms of Service',
    ]);
    $emptyDocument = Document::factory()->active()->create([
        'key' => 'privacy',
        'title' => 'Privacy Policy',
    ]);
    $publication = DocumentPublication::factory()->document($document)->create([
        'version_label' => '2026-06-04',
        'content_hash' => str_repeat('b', 64),
    ]);

    livewire(ListDocuments::class)
        ->assertSuccessful()
        ->assertActionVisible(TestAction::make('record_acceptance')->table($document))
        ->assertActionHidden(TestAction::make('record_acceptance')->table($emptyDocument))
        ->callAction(TestAction::make('record_acceptance')->table($document), data: [
            'context' => 'admin-review',
            'metadata_note' => 'Accepted while reviewing controlled document setup.',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified(__('capell-document-lifecycle::navigation.messages.acceptance_recorded'));

    $acceptance = DocumentAcceptance::query()
        ->where('document_key', 'terms')
        ->firstOrFail();

    expect($acceptance->document_version)->toBe('2026-06-04')
        ->and($acceptance->document_publication_id)->toBe($publication->getKey())
        ->and($acceptance->document_hash)->toBe(str_repeat('b', 64))
        ->and($acceptance->acceptor_type)->toBe($admin->getMorphClass())
        ->and($acceptance->acceptor_id)->toBe($admin->getKey())
        ->and($acceptance->context)->toBe('admin-review')
        ->and($acceptance->metadata)->toBe(['admin_note' => 'Accepted while reviewing controlled document setup.']);
});

it('restores archived documents without publications to draft', function (): void {
    test()->actingAsAdmin();

    $document = Document::factory()->archived()->create();

    livewire(ListDocuments::class)
        ->assertSuccessful()
        ->callAction(TestAction::make('restore')->table($document))
        ->assertHasNoActionErrors();

    expect($document->refresh()->status)->toBe(DocumentStatusEnum::Draft);
});
