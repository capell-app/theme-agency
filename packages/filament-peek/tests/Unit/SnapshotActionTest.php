<?php

declare(strict_types=1);

use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\FilamentPeek\Actions\CreatePagePreviewSnapshotAction;
use Capell\FilamentPeek\Actions\FindPagePreviewSnapshotAction;
use Capell\FilamentPeek\Actions\StoreLayoutBuilderPreviewStateAction;
use Capell\FilamentPeek\Data\LayoutBuilderPreviewStateData;
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
        ->and(FindPagePreviewSnapshotAction::run($result['snapshot']->token))->not->toBeNull();
});

it('returns null for missing preview snapshot tokens', function (): void {
    expect(FindPagePreviewSnapshotAction::run('missing-token'))->toBeNull();
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
            'main' => ['widgets' => [['widget_key' => 'hero', 'occurrence' => 1]]],
        ],
    );

    $state = resolve(StoreLayoutBuilderPreviewStateAction::class)->resolve($page, $user);

    throw_unless($state instanceof LayoutBuilderPreviewStateData, RuntimeException::class, 'Expected layout builder preview state to resolve.');

    expect($state)->not->toBeNull()
        ->and($state->layoutId)->toBe($layout->id)
        ->and($state->containers['main']['widgets'][0]['widget_key'])->toBe('hero')
        ->and($state->signature)->toBeString();

    Cache::store('array')->flush();
});

it('clears stale layout builder preview state after saved layout changes reset the editor', function (): void {
    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);

    $layout = Layout::factory()->create();
    $page = Page::factory()->create(['layout_id' => $layout->id]);

    StoreLayoutBuilderPreviewStateAction::run(
        page: $page,
        layout: $layout,
        containers: [
            'main' => ['widgets' => [['widget_key' => 'hero', 'occurrence' => 1]]],
        ],
        assets: [
            'main' => [
                [
                    ['asset_type' => 'page', 'asset_id' => 1],
                ],
            ],
        ],
    );

    $action = resolve(StoreLayoutBuilderPreviewStateAction::class);

    expect($action->resolve($page, $user))->not->toBeNull();

    $action->clear($page, $user);

    expect($action->resolve($page, $user))->toBeNull();
});
