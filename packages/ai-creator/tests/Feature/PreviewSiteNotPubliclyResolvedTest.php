<?php

declare(strict_types=1);

use Capell\Core\Actions\CreateDefaultLanguagesAction;
use Capell\Core\Actions\LoadSiteDomainFromUrlAction;
use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;

beforeEach(function (): void {
    CreateDefaultLanguagesAction::run(['en']);
});

it('resolves a normal site publicly but excludes a preview-flagged site', function (): void {
    $publicSite = Site::factory()->create();
    SiteDomain::factory()->site($publicSite)->create([
        'domain' => 'public.test',
        'scheme' => 'https',
        'path' => null,
        'status' => true,
    ]);

    $previewSite = Site::factory()->create(['meta' => ['is_preview' => true]]);
    SiteDomain::factory()->site($previewSite)->create([
        'domain' => 'preview.test',
        'scheme' => 'https',
        'path' => null,
        'status' => true,
    ]);

    $resolvedPublic = LoadSiteDomainFromUrlAction::run('https://public.test/');
    $resolvedPreview = LoadSiteDomainFromUrlAction::run('https://preview.test/');

    $resolvedDomain = $resolvedPublic[0] ?? null;

    expect($resolvedPublic)->not->toBeNull()
        ->and($resolvedDomain?->site_id)->toBe($publicSite->id)
        ->and($resolvedPreview)->toBeNull();
});

it('keeps preview-flagged sites out of the excludingPreview scope while retaining normal sites', function (): void {
    $publicSite = Site::factory()->create();
    $previewSite = Site::factory()->create(['meta' => ['is_preview' => true]]);

    $ids = Site::excludingPreview()->pluck('id');

    expect($ids)->toContain($publicSite->id)
        ->and($ids)->not->toContain($previewSite->id);
});
