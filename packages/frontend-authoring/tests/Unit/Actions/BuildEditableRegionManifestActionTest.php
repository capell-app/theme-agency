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
use Capell\FrontendAuthoring\Support\EditableRegionSigner;
use Illuminate\Support\Facades\Config;

beforeEach(function (): void {
    Config::set('capell-frontend-authoring.selectors.page_title', '[data-edit-title]');
    Config::set('capell-frontend-authoring.selectors.page_content', '[data-edit-content]');
});

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
        ->map(fn (array $region): string => editableRegionPayloadFromEditUrl((string) $region['edit_url'])->field)
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
            recordKey: (int) $resolvedPageUrl->getKey(),
            field: 'meta.summary',
            label: 'Summary',
            type: 'textarea',
            selector: '[data-edit-summary]',
            currentUrl: $resolvedPageUrl->full_url,
            pageUrlId: (int) $resolvedPageUrl->getKey(),
            siteId: (int) $resolvedPageUrl->site_id,
            languageId: (int) $resolvedPageUrl->language_id,
            regionKey: 'test.summary',
        ),
    ]);
    app()->tag('frontend-authoring-test.extra-region', 'capell-frontend-authoring:editable-regions');

    $manifest = BuildEditableRegionManifestAction::run($pageUrl);

    $region = collect(array_values($manifest))
        ->first(fn (array $editableRegion): bool => $editableRegion['label'] === 'Summary');

    expect($region)->toBeArray();
    assert(is_array($region));

    $payload = editableRegionPayloadFromEditUrl((string) $region['edit_url']);

    expect($payload->field)->toBe('meta.summary')
        ->and($region)->toMatchArray([
            'label' => 'Summary',
            'type' => 'textarea',
            'selector' => '[data-edit-summary]',
            'surface' => 'field',
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
        ->first(fn (array $region): bool => editableRegionPayloadFromEditUrl((string) $region['edit_url'])->field === $field);

    expect($region)->toBeArray();
    assert(is_array($region));

    return $region;
}
