<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\Core\Models\Translation;
use Capell\Frontend\Contracts\AdminAccessCheckerInterface;
use Capell\FrontendAuthoring\Actions\ClearAffectedCachedUrlsAction;
use Capell\FrontendAuthoring\Actions\CollectAffectedCachedUrlsAction;
use Capell\FrontendAuthoring\Actions\UpdateEditableRegionAction;
use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Capell\FrontendAuthoring\Http\Controllers\EditRegionController;
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Capell\HtmlCache\Models\CachedModelUrl;
use Capell\HtmlCache\Support\Cache\HtmlCachePathResolver;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\WorkspaceRegistry;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\getJson;

use Symfony\Component\HttpKernel\Exception\HttpException;

beforeEach(function (): void {
    Config::set('capell-frontend-authoring.enabled', true);
    Config::set('capell-frontend-authoring.workflow.require_approval', false);
    Config::set('capell-admin.auto_refresh_cache', false);
});

function bindEditableRegionAdminAccess(bool $isAdmin): void
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

function editableRegionCachePathFromUrl(string $url): string
{
    $path = parse_url($url, PHP_URL_PATH);

    if (! is_string($path) || $path === '') {
        return '/';
    }

    return '/' . ltrim($path, '/');
}

/**
 * @param  array<array-key, mixed>  $attributes
 */
function createEditableRegionTranslation(array $attributes = []): Translation
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
            'meta' => ['description' => 'Original description', 'seo' => ['description' => 'Original description']],
            ...$attributes,
        ]);
    PageUrl::factory()
        ->site($site)
        ->language($language)
        ->page($page)
        ->create(['url' => '/current']);

    return $translation;
}

function editableRegionPayload(Translation $translation, string $field = 'title'): EditableRegionPayloadData
{
    $page = $translation->translatable;
    assert($page instanceof Page);

    $pageUrl = PageUrl::query()
        ->where('pageable_type', $page->getMorphClass())
        ->where('pageable_id', $page->getKey())
        ->firstOrFail();

    $page->setRelation('translation', $translation);
    $pageUrl->setRelation('pageable', $page);

    return new EditableRegionPayloadData(
        model: Translation::class,
        recordKey: (int) $translation->getKey(),
        field: $field,
        label: $field === 'content' ? 'Page content' : ($field === 'title' ? 'Page title' : 'Page description'),
        type: $field === 'content' ? 'html' : ($field === 'title' ? 'text' : 'textarea'),
        selector: $field === 'content'
            ? config('capell-frontend-authoring.selectors.page_content', '#main .content-component:first-of-type')
            : config('capell-frontend-authoring.selectors.page_title', '#main h1:first-of-type'),
        currentUrl: $pageUrl->full_url,
        pageUrlId: (int) $pageUrl->getKey(),
        siteId: (int) $pageUrl->site_id,
        languageId: (int) $pageUrl->language_id,
        regionKey: match ($field) {
            'title' => 'page.title',
            'content' => 'page.content',
            'meta.description' => 'page.meta.description',
            default => 'test.' . $field,
        },
    );
}

function allowEditableRegionEdits(): void
{
    Gate::before(fn (Authenticatable $user, string $ability): ?bool => $ability === 'frontend-authoring.edit' ? true : null);
}

it('collects affected cached urls for the edited model record', function (): void {
    $translation = createEditableRegionTranslation();
    $otherTranslation = createEditableRegionTranslation();

    CachedModelUrl::query()->create([
        'url' => 'https://example.test/current',
        'url_hash' => CachedModelUrl::hashUrl('https://example.test/current'),
        'path' => '/current',
        'cacheable_type' => $translation->getMorphClass(),
        'cacheable_id' => $translation->getKey(),
    ]);
    CachedModelUrl::query()->create([
        'url' => 'https://example.test/duplicate',
        'url_hash' => CachedModelUrl::hashUrl('https://example.test/duplicate'),
        'path' => '/duplicate',
        'cacheable_type' => $translation->getMorphClass(),
        'cacheable_id' => $translation->getKey(),
    ]);
    CachedModelUrl::query()->create([
        'url' => 'https://example.test/other',
        'url_hash' => CachedModelUrl::hashUrl('https://example.test/other'),
        'path' => '/other',
        'cacheable_type' => $otherTranslation->getMorphClass(),
        'cacheable_id' => $otherTranslation->getKey(),
    ]);

    expect(CollectAffectedCachedUrlsAction::run($translation))->toBe([
        'https://example.test/current',
        'https://example.test/duplicate',
    ]);
});

it('clears affected cached urls and removes the edited model from the cache index', function (): void {
    Storage::fake('page_cache');

    $translation = createEditableRegionTranslation();
    $language = Language::factory()->create();
    $site = Site::factory()->hasSiteDomains()->create();
    $siteDomain = SiteDomain::factory()
        ->for($site)
        ->for($language)
        ->create([
            'scheme' => 'https',
            'domain' => 'example.test',
            'path' => '/',
            'status' => true,
        ]);
    $url = 'https://example.test/edited';
    $cachePath = resolve(HtmlCachePathResolver::class)->pathForUrl('/edited', $siteDomain);

    Storage::disk('page_cache')->put($cachePath, 'cached html');
    CachedModelUrl::query()->create([
        'url' => $url,
        'url_hash' => CachedModelUrl::hashUrl($url),
        'path' => '/edited',
        'site_domain_id' => $siteDomain->getKey(),
        'cacheable_type' => $translation->getMorphClass(),
        'cacheable_id' => $translation->getKey(),
    ]);
    CachedModelUrl::query()->create([
        'url' => $url,
        'url_hash' => CachedModelUrl::hashUrl($url),
        'path' => '/edited',
        'site_domain_id' => $siteDomain->getKey(),
        'cacheable_type' => Page::factory()->create()->getMorphClass(),
        'cacheable_id' => 123,
    ]);
    CachedModelUrl::query()->create([
        'url' => 'https://missing.test/edited',
        'url_hash' => CachedModelUrl::hashUrl('https://missing.test/edited'),
        'path' => '/edited',
        'cacheable_type' => $translation->getMorphClass(),
        'cacheable_id' => $translation->getKey(),
    ]);

    $cleared = ClearAffectedCachedUrlsAction::run(
        $translation,
        [$url, 'https://missing.test/edited'],
        'https://example.test/other',
    );

    expect($cleared)->toBe(1)
        ->and(Storage::disk('page_cache')->exists($cachePath))->toBeFalse()
        ->and(CachedModelUrl::query()->where('url', $url)->exists())->toBeFalse()
        ->and(CachedModelUrl::query()->where('url', 'https://missing.test/edited')->exists())->toBeFalse();
});

it('updates allowed editable region fields and rejects unknown fields', function (): void {
    $user = User::factory()->create();
    actingAs($user);
    allowEditableRegionEdits();

    $translation = createEditableRegionTranslation();

    $titleResult = UpdateEditableRegionAction::run(editableRegionPayload($translation, 'title'), 'Updated title', $user);
    $metaResult = UpdateEditableRegionAction::run(editableRegionPayload($translation, 'meta.description'), 'Updated description', $user);

    $translation->refresh();

    expect($titleResult)->toMatchArray(['cleared' => 0, 'urls' => [], 'status' => 'published', 'redirect_url' => null])
        ->and($metaResult)->toMatchArray(['cleared' => 0, 'urls' => [], 'status' => 'published', 'redirect_url' => null])
        ->and($translation->title)->toBe('Updated title')
        ->and($translation->meta)->toHaveKey('description', 'Updated description');

    expect(fn (): array => UpdateEditableRegionAction::run(editableRegionPayload($translation, 'admin.hidden'), 'Nope', $user))
        ->toThrow(HttpException::class);
});

it('saves text rich html and meta edits while clearing every affected cached page', function (string $field, string $value, Closure $assertSaved): void {
    Storage::fake('page_cache');

    $user = User::factory()->create();
    actingAs($user);
    allowEditableRegionEdits();

    $translation = createEditableRegionTranslation();
    $page = $translation->translatable;
    assert($page instanceof Page);

    $pageUrl = PageUrl::query()
        ->where('pageable_type', $page->getMorphClass())
        ->where('pageable_id', $page->getKey())
        ->firstOrFail();
    $siteDomain = SiteDomain::query()
        ->where('site_id', $pageUrl->site_id)
        ->where('language_id', $pageUrl->language_id)
        ->firstOrFail();
    $pathResolver = resolve(HtmlCachePathResolver::class);

    $touchedUrls = [
        $pageUrl->full_url,
        'https://example.test/also-uses-this-copy',
    ];

    foreach ($touchedUrls as $url) {
        $path = editableRegionCachePathFromUrl($url);
        $cachePath = $pathResolver->pathForUrl($path, $siteDomain);

        Storage::disk('page_cache')->put($cachePath, 'stale cached html');

        CachedModelUrl::query()->create([
            'url' => $url,
            'url_hash' => CachedModelUrl::hashUrl($url),
            'path' => $path,
            'site_id' => $siteDomain->site_id,
            'site_domain_id' => $siteDomain->getKey(),
            'language_id' => $siteDomain->language_id,
            'cacheable_type' => $translation->getMorphClass(),
            'cacheable_id' => $translation->getKey(),
        ]);
    }

    $result = UpdateEditableRegionAction::run(editableRegionPayload($translation, $field), $value, $user);

    $translation->refresh();
    $assertSaved($translation);

    expect($result)->toMatchArray([
        'cleared' => 2,
        'urls' => $touchedUrls,
        'status' => 'published',
        'redirect_url' => null,
    ]);

    foreach ($touchedUrls as $url) {
        $path = editableRegionCachePathFromUrl($url);
        $cachePath = $pathResolver->pathForUrl($path, $siteDomain);

        expect(Storage::disk('page_cache')->exists($cachePath))->toBeFalse()
            ->and(CachedModelUrl::query()->where('url', $url)->exists())->toBeFalse();
    }
})->with([
    'plain text title' => [
        'title',
        'User tested title',
        function (Translation $translation): void {
            expect($translation->title)->toBe('User tested title');
        },
    ],
    'rich html content' => [
        'content',
        '<h2>User tested heading</h2><p><strong>Rich</strong> body copy.</p>',
        function (Translation $translation): void {
            expect($translation->content)->toBe('<h2>User tested heading</h2><p><strong>Rich</strong> body copy.</p>');
        },
    ],
    'meta description' => [
        'meta.description',
        'User tested SEO description',
        function (Translation $translation): void {
            expect($translation->meta)->toHaveKey('description', 'User tested SEO description');
        },
    ],
]);

it('saves inline edits into an approval workspace and returns a preview redirect when approval is required', function (): void {
    Config::set('capell-frontend-authoring.workflow.require_approval', true);
    ensureEditableRegionWorkflowTables();
    WorkspaceRegistry::register(Translation::class);

    $user = User::factory()->create();
    actingAs($user);
    Gate::before(fn (Authenticatable $user, string $ability): ?bool => in_array($ability, ['frontend-authoring.edit', 'preview', 'view'], true) ? true : null);
    Route::get('/workflow-preview-stub', fn (): string => 'preview')->name('capell-frontend.home');

    $translation = createEditableRegionTranslation();

    $result = UpdateEditableRegionAction::run(editableRegionPayload($translation, 'title'), 'Draft title', $user);

    $translation->refresh();
    $workspace = Workspace::query()->firstOrFail();
    $draftTranslation = Translation::query()
        ->withoutGlobalScopes()
        ->where('workspace_id', $workspace->getKey())
        ->firstOrFail();

    expect($result['status'])->toBe('pending_approval')
        ->and($result['cleared'])->toBe(0)
        ->and($result['redirect_url'])->toContain('__workspace=')
        ->and($workspace->status)->toBe(WorkspaceStatusEnum::InReview)
        ->and($translation->title)->toBe('Original title')
        ->and($draftTranslation->title)->toBe('Draft title');
});

function ensureEditableRegionWorkflowTables(): void
{
    Relation::morphMap([
        'workspace' => Workspace::class,
        'user' => User::class,
        'translation' => Translation::class,
    ]);

    if (! Schema::hasColumn('translations', 'workspace_id')) {
        DB::statement('DROP INDEX IF EXISTS translations_key_unique');
        DB::statement('DROP INDEX IF EXISTS translations_language_id_translatable_type_translatable_id_unique');

        Schema::table('translations', function (Blueprint $table): void {
            $table->unsignedBigInteger('workspace_id')->default(0)->index();
            $table->unsignedBigInteger('shadowed_by_workspace_id')->default(0)->index();
            $table->unique(['language_id', 'translatable_type', 'translatable_id', 'workspace_id'], 'translations_identity_workspace_unique');
        });
    }

    if (! Schema::hasTable('workspaces')) {
        Schema::create('workspaces', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->string('status')->default('open');
            $table->string('kind')->default('manual');
            $table->unsignedBigInteger('base_version_id')->nullable();
            $table->unsignedBigInteger('cloned_from_id')->nullable();
            $table->json('settings')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('unpublish_at')->nullable();
            $table->timestamp('embargo_until')->nullable();
            $table->timestamp('review_reminder_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    if (! Schema::hasTable('versions')) {
        Schema::create('versions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->nullable();
            $table->unsignedInteger('number')->default(1);
            $table->string('name')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_live')->default(false);
            $table->json('manifest')->nullable();
            $table->unsignedBigInteger('source_workspace_id')->nullable();
            $table->unsignedBigInteger('rollback_of_version_id')->nullable();
            $table->nullableMorphs('published_by');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('workspace_approvals')) {
        Schema::create('workspace_approvals', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->nullableMorphs('actionable');
            $table->unsignedInteger('level')->default(1);
            $table->string('action');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    if (! Schema::hasTable('preview_links')) {
        Schema::create('preview_links', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('workspace_id');
            $table->string('token')->unique();
            $table->nullableMorphs('issued_by');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_accessed_at')->nullable();
            $table->unsignedInteger('access_count')->default(0);
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }
}

it('encodes signed region payloads and rejects tampered payloads', function (): void {
    $translation = createEditableRegionTranslation();
    $signer = resolve(EditableRegionSigner::class);
    $payload = editableRegionPayload($translation, 'content');
    $encodedPayload = $signer->encode($payload);

    expect($signer->decode($encodedPayload)->toArray())->toBe($payload->toArray());

    $decodedJson = base64_decode(strtr($encodedPayload, '-_', '+/'), true);
    expect($decodedJson)->toBeString();

    $decodedPayload = json_decode((string) $decodedJson, associative: true, flags: JSON_THROW_ON_ERROR);
    $decodedPayload['data']['field'] = 'title';
    $tamperedPayload = rtrim(strtr(base64_encode(json_encode($decodedPayload, JSON_THROW_ON_ERROR)), '+/', '-_'), '=');

    expect(fn (): EditableRegionPayloadData => $signer->decode($tamperedPayload))
        ->toThrow(HttpException::class);
});

it('protects the edit region route with authentication admin access and signed urls', function (): void {
    $translation = createEditableRegionTranslation();
    $signer = resolve(EditableRegionSigner::class);
    $signedUrl = $signer->signedEditUrl(editableRegionPayload($translation));

    getJson($signedUrl)->assertUnauthorized();

    $user = User::factory()->create();
    actingAs($user);

    bindEditableRegionAdminAccess(false);
    get($signedUrl)->assertForbidden();

    bindEditableRegionAdminAccess(true);
    allowEditableRegionEdits();

    get($signedUrl)->assertOk()
        ->assertSee('<html lang="en" class="fi">', false)
        ->assertSee('capell-authoring-editor-shell')
        ->assertSee("[x-cloak='']", false)
        ->assertSee('capell-authoring:editor-loaded')
        ->assertSee('wire:snapshot', false)
        ->assertSee('Page title');

    $encodedPayload = $signer->encode(editableRegionPayload($translation));
    $request = Request::create('/authoring/regions/' . $encodedPayload);
    $request->setUserResolver(fn (): User => $user);

    $view = resolve(EditRegionController::class)->__invoke($request, $encodedPayload);

    expect($view->name())->toBe('capell::editor.region')
        ->and($view->getData())->toHaveKey('payload', $encodedPayload);

    $tamperedUrl = str_replace('signature=', 'signature=invalid', $signedUrl);
    getJson($tamperedUrl)->assertForbidden();
});
