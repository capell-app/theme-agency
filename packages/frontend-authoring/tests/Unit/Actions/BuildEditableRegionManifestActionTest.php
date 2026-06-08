<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\Core\Models\Translation;
use Capell\FrontendAuthoring\Actions\BuildEditableRegionManifestAction;
use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Capell\FrontendAuthoring\Enums\EditableRegionInputType;
use Capell\FrontendAuthoring\Enums\EditableRegionSurface;
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    Config::set('capell-frontend-authoring.selectors.page_title', '[data-edit-title]');
    Config::set('capell-frontend-authoring.selectors.page_content', '[data-edit-content]');
});

function editableRegionManifestModelKey(Model $model): int
{
    $key = $model->getKey();

    if (is_int($key)) {
        return $key;
    }

    return is_string($key) && ctype_digit($key) ? (int) $key : 0;
}

/**
 * @param  array<string, mixed>  $region
 */
function editableRegionManifestString(array $region, string $key): string
{
    $value = $region[$key] ?? null;

    return is_string($value) ? $value : '';
}

it('builds signed editable regions for a translated page url', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->create(['language_id' => $language->getKey()]);
    $siteDomain = SiteDomain::factory()
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
            'title' => 'Manifest title',
            'content' => '<p>Manifest content</p>',
            'meta' => ['seo' => ['description' => 'Manifest description']],
        ]);
    $pageUrl = PageUrl::factory()
        ->site($site)
        ->language($language)
        ->page($page)
        ->create(['url' => '/manifest-page']);

    $page->setRelation('translation', $translation);
    $pageUrl->setRelation('pageable', $page);
    $pageUrl->setRelation('siteDomain', $siteDomain);

    $manifest = BuildEditableRegionManifestAction::run($pageUrl);

    $regions = array_values($manifest);
    $regionFields = collect($regions)
        ->map(fn (array $region): string => editableRegionPayloadFromEditUrl(editableRegionManifestString($region, 'edit_url'))->field)
        ->all();
    $titleRegion = editableRegionByField($regions, 'title');
    $descriptionRegion = editableRegionByField($regions, 'meta.description');
    $contentRegion = editableRegionByField($regions, 'content');

    expect($regionFields)->toContain('title', 'meta.description', 'content')
        ->and($titleRegion)->toMatchArray([
            'type' => 'text',
            'selector' => '[data-edit-title]',
            'surface' => 'field',
        ])
        ->and($descriptionRegion)->toMatchArray([
            'type' => 'textarea',
            'selector' => '[data-edit-title]',
            'surface' => 'field',
        ])
        ->and($contentRegion)->toMatchArray([
            'type' => 'html',
            'selector' => '[data-edit-content]',
            'surface' => 'field',
        ]);
});

it('includes package supplied editable region extenders', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->create(['language_id' => $language->getKey()]);
    $siteDomain = SiteDomain::factory()
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
        ->create();
    $pageUrl = PageUrl::factory()
        ->site($site)
        ->language($language)
        ->page($page)
        ->create(['url' => '/extended-page']);

    $page->setRelation('translation', $translation);
    $pageUrl->setRelation('pageable', $page);
    $pageUrl->setRelation('siteDomain', $siteDomain);

    app()->bind('frontend-authoring-test.extra-region', fn (): callable => fn (PageUrl $resolvedPageUrl): array => [
        new EditableRegionPayloadData(
            model: PageUrl::class,
            recordKey: editableRegionManifestModelKey($resolvedPageUrl),
            field: 'meta.summary',
            label: 'Summary',
            type: EditableRegionInputType::Textarea,
            selector: '[data-edit-summary]',
            currentUrl: $resolvedPageUrl->full_url,
            pageUrlId: editableRegionManifestModelKey($resolvedPageUrl),
            siteId: $resolvedPageUrl->site_id,
            languageId: $resolvedPageUrl->language_id,
            regionKey: 'test.summary',
        ),
    ]);
    app()->tag('frontend-authoring-test.extra-region', 'capell-frontend-authoring:editable-regions');

    $manifest = BuildEditableRegionManifestAction::run($pageUrl);

    $region = collect(array_values($manifest))
        ->first(fn (array $editableRegion): bool => $editableRegion['label'] === 'Summary');

    expect($region)->toBeArray();
    assert(is_array($region));

    $payload = editableRegionPayloadFromEditUrl(editableRegionManifestString($region, 'edit_url'));

    expect($payload->field)->toBe('meta.summary')
        ->and($region)->toMatchArray([
            'label' => 'Summary',
            'type' => 'textarea',
            'selector' => '[data-edit-summary]',
            'surface' => 'field',
        ]);
});

it('includes package supplied media editor regions without changing field defaults', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->create(['language_id' => $language->getKey()]);
    $siteDomain = SiteDomain::factory()
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
        ->create();
    $pageUrl = PageUrl::factory()
        ->site($site)
        ->language($language)
        ->page($page)
        ->create(['url' => '/media-editor-page']);

    $page->setRelation('translation', $translation);
    $pageUrl->setRelation('pageable', $page);
    $pageUrl->setRelation('siteDomain', $siteDomain);

    app()->bind('frontend-authoring-test.media-region', fn (): callable => fn (PageUrl $resolvedPageUrl): array => [
        new EditableRegionPayloadData(
            model: PageUrl::class,
            recordKey: editableRegionManifestModelKey($resolvedPageUrl),
            field: 'meta.hero_image',
            label: 'Hero image',
            type: EditableRegionInputType::Text,
            selector: '[data-edit-hero-image]',
            currentUrl: $resolvedPageUrl->full_url,
            pageUrlId: editableRegionManifestModelKey($resolvedPageUrl),
            siteId: $resolvedPageUrl->site_id,
            languageId: $resolvedPageUrl->language_id,
            regionKey: 'test.hero-image',
            surface: EditableRegionSurface::Media,
            target: 'hero-image',
            description: 'Replace the hero image.',
            context: ['collection' => 'hero'],
        ),
    ]);
    app()->tag('frontend-authoring-test.media-region', 'capell-frontend-authoring:editable-regions');

    $manifest = BuildEditableRegionManifestAction::run($pageUrl);

    $defaultTitleRegion = editableRegionByField(array_values($manifest), 'title');
    $mediaRegion = collect(array_values($manifest))
        ->first(fn (array $editableRegion): bool => $editableRegion['label'] === 'Hero image');

    expect($mediaRegion)->toBeArray();
    assert(is_array($mediaRegion));

    $payload = editableRegionPayloadFromEditUrl(editableRegionManifestString($mediaRegion, 'edit_url'));

    expect($defaultTitleRegion)->toMatchArray([
        'surface' => 'field',
    ])
        ->and($mediaRegion)->toMatchArray([
            'label' => 'Hero image',
            'type' => 'text',
            'selector' => '[data-edit-hero-image]',
            'surface' => 'media',
            'target' => 'hero-image',
            'description' => 'Replace the hero image.',
            'context' => ['collection' => 'hero'],
        ])
        ->and($payload->surface)->toBe(EditableRegionSurface::Media);
});

it('filters package supplied editable regions by region permissions', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->create(['language_id' => $language->getKey()]);
    $siteDomain = SiteDomain::factory()
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
        ->create();
    $pageUrl = PageUrl::factory()
        ->site($site)
        ->language($language)
        ->page($page)
        ->create(['url' => '/permissioned-page']);
    $user = User::factory()->create();

    $page->setRelation('translation', $translation);
    $pageUrl->setRelation('pageable', $page);
    $pageUrl->setRelation('siteDomain', $siteDomain);

    app()->bind('frontend-authoring-test.restricted-region', fn (): callable => fn (PageUrl $resolvedPageUrl): array => [
        new EditableRegionPayloadData(
            model: PageUrl::class,
            recordKey: editableRegionManifestModelKey($resolvedPageUrl),
            field: 'meta.editor_note',
            label: 'Editor note',
            type: EditableRegionInputType::Textarea,
            selector: '[data-edit-editor-note]',
            currentUrl: $resolvedPageUrl->full_url,
            pageUrlId: editableRegionManifestModelKey($resolvedPageUrl),
            siteId: $resolvedPageUrl->site_id,
            languageId: $resolvedPageUrl->language_id,
            regionKey: 'test.editor-note',
            permissions: ['frontend-authoring.edit-editor-note'],
        ),
    ]);
    app()->tag('frontend-authoring-test.restricted-region', 'capell-frontend-authoring:editable-regions');

    Gate::define(
        'frontend-authoring.edit',
        static fn (Authenticatable $actor, ?PageUrl $resolvedPageUrl, EditableRegionPayloadData $region): bool => true,
    );
    $permissionState = new class
    {
        public bool $allowEditorNote = false;
    };

    Gate::define(
        'frontend-authoring.edit-editor-note',
        static fn (Authenticatable $actor, ?PageUrl $resolvedPageUrl, EditableRegionPayloadData $region): bool => $permissionState->allowEditorNote && $region->regionKey === 'test.editor-note',
    );

    $deniedManifest = BuildEditableRegionManifestAction::run($pageUrl, $user);

    $permissionState->allowEditorNote = true;

    $allowedManifest = BuildEditableRegionManifestAction::run($pageUrl, $user);
    $allowedRegion = collect(array_values($allowedManifest))
        ->first(fn (array $editableRegion): bool => $editableRegion['label'] === 'Editor note');

    expect(collect(array_values($deniedManifest))->pluck('label'))->not->toContain('Editor note')
        ->and($allowedRegion)->toBeArray();
    assert(is_array($allowedRegion));

    expect($allowedRegion)->toMatchArray([
        'label' => 'Editor note',
        'permissions' => ['frontend-authoring.edit-editor-note'],
    ]);
});

function editableRegionPayloadFromEditUrl(string $editUrl): EditableRegionPayloadData
{
    $path = (string) parse_url($editUrl, PHP_URL_PATH);
    $payload = basename($path);

    return resolve(EditableRegionSigner::class)->decode($payload);
}

/**
 * @param  list<array<string, mixed>>  $regions
 * @return array<string, mixed>
 */
function editableRegionByField(array $regions, string $field): array
{
    $region = collect($regions)
        ->first(fn (array $region): bool => editableRegionPayloadFromEditUrl(editableRegionManifestString($region, 'edit_url'))->field === $field);

    expect($region)->toBeArray();
    assert(is_array($region));

    return $region;
}
