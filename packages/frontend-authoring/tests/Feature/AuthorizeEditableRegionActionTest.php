<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\FrontendAuthoring\Actions\AuthorizeEditableRegionAction;
use Capell\FrontendAuthoring\Data\EditableRegionPayloadData;
use Capell\FrontendAuthoring\Enums\EditableRegionInputType;
use Capell\Tests\Fixtures\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

function frontendAuthoringAuthorizationPayload(?PageUrl $pageUrl = null): EditableRegionPayloadData
{
    return new EditableRegionPayloadData(
        model: Page::class,
        recordKey: $pageUrl instanceof PageUrl ? (int) $pageUrl->pageable_id : 1,
        field: 'title',
        label: 'Page title',
        type: EditableRegionInputType::Text,
        selector: '#main h1:first-of-type',
        currentUrl: $pageUrl instanceof PageUrl ? $pageUrl->full_url : 'https://example.test/current',
        pageUrlId: $pageUrl instanceof PageUrl ? (int) $pageUrl->getKey() : 1,
        siteId: $pageUrl instanceof PageUrl ? (int) $pageUrl->site_id : 1,
        languageId: $pageUrl instanceof PageUrl ? (int) $pageUrl->language_id : 1,
        regionKey: 'page.title',
    );
}

function frontendAuthoringAuthorizationPageUrl(): PageUrl
{
    $site = Site::factory()->create();
    $language = Language::factory()->create();
    SiteDomain::factory()->for($site)->for($language)->create();
    $page = Page::factory()->site($site)->create();

    return PageUrl::factory()
        ->for($site)
        ->for($language)
        ->page($page)
        ->create();
}

it('registers the advertised frontend authoring edit gate as deny by default', function (): void {
    $user = User::factory()->create();
    $payload = frontendAuthoringAuthorizationPayload();

    expect(Gate::has('frontend-authoring.edit'))->toBeTrue()
        ->and(Gate::forUser($user)->allows('frontend-authoring.edit', [null, $payload]))->toBeFalse()
        ->and(AuthorizeEditableRegionAction::run($user, $payload))->toBeFalse();
});

it('allows host applications to override the frontend authoring edit gate', function (): void {
    $user = User::factory()->create();
    $payload = frontendAuthoringAuthorizationPayload();

    Gate::define(
        'frontend-authoring.edit',
        static fn (Authenticatable $actor, ?PageUrl $pageUrl, EditableRegionPayloadData $region): bool => $region->regionKey === 'page.title',
    );

    expect(AuthorizeEditableRegionAction::run($user, $payload))->toBeTrue();
});

it('keeps pageable policy fallbacks active when the package gate denies by default', function (): void {
    $user = User::factory()->create();
    $pageUrl = frontendAuthoringAuthorizationPageUrl();
    $payload = frontendAuthoringAuthorizationPayload($pageUrl);

    Gate::define(
        'editContent',
        static fn (Authenticatable $actor, Page $page): bool => (int) $page->getKey() === (int) $pageUrl->pageable_id,
    );

    expect(Gate::forUser($user)->allows('frontend-authoring.edit', [$pageUrl, $payload]))->toBeFalse()
        ->and(AuthorizeEditableRegionAction::run($user, $payload, $pageUrl))->toBeTrue();
});
