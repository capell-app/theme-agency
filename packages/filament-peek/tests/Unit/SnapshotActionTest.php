<?php

declare(strict_types=1);

use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\FilamentPeek\Actions\CreatePagePreviewSnapshotAction;
use Capell\FilamentPeek\Actions\StoreLayoutBuilderPreviewStateAction;
use Illuminate\Support\Facades\Cache;

it('stores private expiring page preview snapshots for the current user', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);

    $page = Page::factory()->create();

    $result = CreatePagePreviewSnapshotAction::run($page, [
        'name' => 'Unsaved page name',
    ]);

    expect($result['url'])->toContain('/capell-filament-peek/preview/')
        ->and($result['snapshot']->userId)->toBe($user->getAuthIdentifier())
        ->and($result['snapshot']->formState['name'])->toBe('Unsaved page name')
        ->and(resolve(CreatePagePreviewSnapshotAction::class)->find($result['snapshot']->token))->not->toBeNull();
});

it('returns null for missing preview snapshot tokens', function (): void {
    expect(resolve(CreatePagePreviewSnapshotAction::class)->find('missing-token'))->toBeNull();
});

it('caches the latest layout builder preview state per user and page', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);

    $layout = Layout::factory()->create();
    $page = Page::factory()->create(['layout_id' => $layout->id]);

    StoreLayoutBuilderPreviewStateAction::run(
        page: $page,
        layout: $layout,
        containers: [
            'main' => ['blocks' => [['block_key' => 'hero', 'occurrence' => 1]]],
        ],
    );

    $state = resolve(StoreLayoutBuilderPreviewStateAction::class)->resolve($page, $user);

    expect($state)->not->toBeNull()
        ->and($state->layoutId)->toBe($layout->id)
        ->and($state->containers['main']['blocks'][0]['block_key'])->toBe('hero');

    Cache::store('array')->flush();
});
