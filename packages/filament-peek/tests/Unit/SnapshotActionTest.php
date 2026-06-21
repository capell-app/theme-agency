<?php

declare(strict_types=1);

use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\FilamentPeek\Actions\CreatePagePreviewSnapshotAction;
use Capell\FilamentPeek\Actions\FindPagePreviewSnapshotAction;
use Capell\FilamentPeek\Actions\StoreLayoutBuilderPreviewStateAction;
use Capell\FilamentPeek\Contracts\StoresLayoutBuilderPreviewState;
use Capell\FilamentPeek\Data\LayoutBuilderPreviewStateData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

it('exposes a typed layout builder preview state store contract', function (): void {
    expect(resolve(StoreLayoutBuilderPreviewStateAction::class))->toBeInstanceOf(StoresLayoutBuilderPreviewState::class);
});

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

it('rejects oversized page preview snapshots before caching them', function (): void {
    config()->set('capell-filament-peek.preview.max_payload_kb', 1);
    $logger = Log::spy();

    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);

    $page = Page::factory()->create();

    expect(fn (): array => CreatePagePreviewSnapshotAction::run($page, [
        'body' => str_repeat('x', 2048),
    ]))->toThrow(RuntimeException::class, __('capell-filament-peek::errors.payload_too_large'));

    $logger->shouldHaveReceived('warning')->withArgs(fn (string $message, array $context): bool => $message === 'Filament Peek preview payload exceeded the configured cache limit.'
        && ($context['payload_type'] ?? null) === 'page_preview_snapshot'
        && ($context['max_bytes'] ?? null) === 1024);

    Cache::store('array')->flush();
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
    $mainContainer = $state->containers['main'] ?? null;

    throw_unless(is_array($mainContainer), RuntimeException::class, 'Expected main preview container array.');
    $widgets = $mainContainer['widgets'] ?? null;

    throw_unless(is_array($widgets), RuntimeException::class, 'Expected main preview widgets array.');
    $firstWidget = $widgets[0] ?? null;

    throw_unless(is_array($firstWidget), RuntimeException::class, 'Expected first preview widget array.');

    expect($state)->not->toBeNull()
        ->and($state->layoutId)->toBe($layout->id)
        ->and($firstWidget['widget_key'] ?? null)->toBe('hero')
        ->and($state->signature)->toBeString();

    Cache::store('array')->flush();
});

it('rejects oversized layout builder preview state before caching it', function (): void {
    config()->set('capell-filament-peek.preview.max_payload_kb', 1);
    $logger = Log::spy();

    $user = $this->createUserWithRole('super_admin');
    $this->actingAs($user);

    $layout = Layout::factory()->create();
    $page = Page::factory()->create(['layout_id' => $layout->id]);

    expect(fn (): mixed => StoreLayoutBuilderPreviewStateAction::run(
        page: $page,
        layout: $layout,
        containers: [
            'main' => ['widgets' => [['payload' => str_repeat('x', 2048)]]],
        ],
    ))->toThrow(RuntimeException::class, __('capell-filament-peek::errors.payload_too_large'));

    $logger->shouldHaveReceived('warning')->withArgs(fn (string $message, array $context): bool => $message === 'Filament Peek preview payload exceeded the configured cache limit.'
        && ($context['payload_type'] ?? null) === 'layout_builder_preview_state'
        && ($context['max_bytes'] ?? null) === 1024);

    expect(resolve(StoreLayoutBuilderPreviewStateAction::class)->resolve($page, $user))->toBeNull();
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
