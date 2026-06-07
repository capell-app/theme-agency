<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\Core\Models\Translation;
use Capell\Frontend\Contracts\AdminAccessCheckerInterface;
use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Capell\FrontendAuthoring\Health\FrontendAuthoringHealthCheck;
use Capell\FrontendAuthoring\Http\Middleware\PassThroughActivityMiddleware;
use Capell\FrontendAuthoring\Http\Requests\BeaconRequest;
use Capell\FrontendAuthoring\Livewire\EditRegionField;
use Capell\FrontendAuthoring\Support\EditableRegionRegistry;
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;

beforeEach(function (): void {
    Livewire::component('edit-region-field', EditRegionField::class);
});

it('declares beacon validation and passes activity middleware through unchanged', function (): void {
    $request = new BeaconRequest;
    $middleware = new PassThroughActivityMiddleware;
    $httpRequest = Request::create('/beacon', Symfony\Component\HttpFoundation\Request::METHOD_POST, ['url' => 'https://example.test/page']);

    $response = $middleware->handle(
        $httpRequest,
        fn (Request $nextRequest): string => 'handled:' . $nextRequest->input('url'),
    );

    expect($request->authorize())->toBeTrue()
        ->and($request->rules())->toBe([
            'url' => ['required', 'url', 'max:2048'],
        ])
        ->and($response)->toBe('handled:https://example.test/page')
        ->and(FrontendAuthoringHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});

it('loads current editable values and saves text updates from the livewire editor', function (): void {
    bindEditRegionFieldAdminAccess(true);
    allowEditRegionFieldFrontendAuthoringEdits();

    $user = User::factory()->create();
    actingAs($user);

    $translation = createEditRegionFieldTranslation([
        'title' => 'Original title',
        'content' => '<p>Original content</p>',
        'meta' => ['seo' => ['description' => 'Original description']],
    ]);
    $payload = editableRegionFieldPayload($translation, 'title');

    Livewire::test('edit-region-field', ['payload' => $payload])
        ->assertSet('label', 'Page title')
        ->assertSet('type', 'text')
        ->assertSet('data.value', 'Original title')
        ->set('data.value', 'Updated title')
        ->call('save')
        ->assertSet('savedStatus', 'published')
        ->assertDispatched('capell-authoring-saved');

    expect($translation->fresh()?->title)->toBe('Updated title');
});

it('loads meta values and uses textarea controls for non-text editable regions', function (): void {
    bindEditRegionFieldAdminAccess(true);
    allowEditRegionFieldFrontendAuthoringEdits();

    $user = User::factory()->create();
    actingAs($user);

    $translation = createEditRegionFieldTranslation([
        'meta' => ['description' => 'Original description'],
    ]);
    $payload = editableRegionFieldPayload($translation, 'meta.description', type: 'textarea');

    Livewire::test('edit-region-field', ['payload' => $payload])
        ->assertSet('type', 'textarea')
        ->assertSet('data.value', 'Original description')
        ->set('data.value', 'Updated description')
        ->call('save')
        ->assertSet('savedStatus', 'published');

    expect($translation->fresh()?->meta)->toHaveKey('description', 'Updated description');
});

it('rejects admin users without frontend authoring edit permission', function (): void {
    bindEditRegionFieldAdminAccess(true);

    $user = User::factory()->create();
    actingAs($user);

    $translation = createEditRegionFieldTranslation();
    $payload = editableRegionFieldPayload($translation, 'title');

    Livewire::test('edit-region-field', ['payload' => $payload])
        ->assertForbidden();
});

it('revalidates signed payloads against the current editable manifest before saving', function (): void {
    bindEditRegionFieldAdminAccess(true);
    allowEditRegionFieldFrontendAuthoringEdits();

    $user = User::factory()->create();
    actingAs($user);

    $translation = createEditRegionFieldTranslation();
    $payload = editableRegionFieldPayload($translation, 'title');

    $component = Livewire::test('edit-region-field', ['payload' => $payload])
        ->assertSet('data.value', 'Original title');

    config(['capell-frontend-authoring.selectors.page_title' => '[data-now-different]']);

    $component
        ->set('data.value', 'Should not save')
        ->call('save')
        ->assertForbidden();

    expect($translation->fresh()?->title)->toBe('Original title');
});

it('rejects non-admin users in the livewire editor', function (): void {
    bindEditRegionFieldAdminAccess(false);
    allowEditRegionFieldFrontendAuthoringEdits();

    $user = User::factory()->create();
    actingAs($user);

    $translation = createEditRegionFieldTranslation();
    $payload = editableRegionFieldPayload($translation, 'content', type: 'html');

    Livewire::test('edit-region-field', ['payload' => $payload])
        ->assertForbidden();
});

function bindEditRegionFieldAdminAccess(bool $isAdmin): void
{
    app()->instance(AdminAccessCheckerInterface::class, new readonly class($isAdmin) implements AdminAccessCheckerInterface
    {
        public function __construct(private bool $isAdmin) {}

        public function isAdmin(Authenticatable $user): bool
        {
            return $this->isAdmin;
        }
    });
}

function allowEditRegionFieldFrontendAuthoringEdits(): void
{
    Gate::before(fn (Authenticatable $user, string $ability): ?bool => $ability === 'frontend-authoring.edit' ? true : null);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function createEditRegionFieldTranslation(array $attributes = []): Translation
{
    $language = Language::factory()->create();
    $site = Site::factory()->create(['language_id' => $language->getKey()]);
    SiteDomain::factory()
        ->for($site)
        ->for($language)
        ->create([
            'scheme' => 'https',
            'domain' => 'example.test',
            'path' => '/',
            'status' => true,
        ]);
    $page = Page::factory()->site($site)->create();

    $translation = Translation::factory()
        ->translatable($page)
        ->language($language)
        ->create([
            'title' => 'Original title',
            'content' => '<p>Original content</p>',
            'meta' => ['seo' => ['description' => 'Original description']],
            ...$attributes,
        ]);
    PageUrl::factory()
        ->site($site)
        ->language($language)
        ->page($page)
        ->create(['url' => '/current']);

    return $translation;
}

function editableRegionFieldPayload(Translation $translation, string $field, string $type = 'text'): string
{
    $page = $translation->translatable;
    assert($page instanceof Page);

    $pageUrl = PageUrl::query()
        ->where('pageable_type', $page->getMorphClass())
        ->where('pageable_id', $page->getKey())
        ->firstOrFail();

    $page->setRelation('translation', $translation);
    $pageUrl->setRelation('pageable', $page);

    $region = collect(resolve(EditableRegionRegistry::class)->regionsFor($pageUrl))
        ->first(fn (EditableRegionPayloadData $region): bool => $region->field === $field);

    assert($region instanceof EditableRegionPayloadData);
    assert($region->type->value === $type);

    return resolve(EditableRegionSigner::class)->encode($region);
}
