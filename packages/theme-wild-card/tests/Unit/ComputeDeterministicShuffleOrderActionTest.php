<?php

declare(strict_types=1);

use Capell\ThemeStudio\WildCard\Actions\ComputeDeterministicShuffleOrderAction;

/*
 * §0.1 determinism guardrail: this action is the reference implementation
 * for the card-shuffle-grid seeded shuffle algorithm. The Blade views
 * themselves inline the same crc32-based hash (the `@php` block static-call
 * policy bans calling Action::run() from a view — see
 * card-shuffle-grid.blade.php's comment), so this test is the coverage that
 * actually exercises Capell\ThemeStudio\WildCard\Actions\ComputeDeterministicShuffleOrderAction
 * as a unit, keeping the two implementations honest against each other.
 */
it('produces the same order for the same page seed and item set', function (): void {
    $items = [
        ['id' => 'a', 'title' => 'Alpha'],
        ['id' => 'b', 'title' => 'Bravo'],
        ['id' => 'c', 'title' => 'Charlie'],
    ];

    $first = ComputeDeterministicShuffleOrderAction::run($items, 'theme-wild-card');
    $second = ComputeDeterministicShuffleOrderAction::run($items, 'theme-wild-card');

    expect($first)->toBe($second);
});

it('produces a different order for a different page seed', function (): void {
    $items = [
        ['id' => 'a', 'title' => 'Alpha'],
        ['id' => 'b', 'title' => 'Bravo'],
        ['id' => 'c', 'title' => 'Charlie'],
    ];

    $home = ComputeDeterministicShuffleOrderAction::run($items, 'theme-wild-card');
    $detail = ComputeDeterministicShuffleOrderAction::run($items, 'theme-wild-card-detail');

    expect($home)->not->toBe($detail);
});

it('assigns a contiguous zero-based position to every item and preserves every item', function (): void {
    $items = [
        ['id' => 'a', 'title' => 'Alpha'],
        ['id' => 'b', 'title' => 'Bravo'],
        ['id' => 'c', 'title' => 'Charlie'],
    ];

    /** @var list<array{item: array<string, mixed>, position: int}> $shuffled */
    $shuffled = ComputeDeterministicShuffleOrderAction::run($items, 'theme-wild-card');

    expect($shuffled)->toHaveCount(3);

    $positions = array_column($shuffled, 'position');
    sort($positions);
    expect($positions)->toBe([0, 1, 2]);

    $shuffledIds = array_map(static fn (array $entry): mixed => $entry['item']['id'], $shuffled);
    sort($shuffledIds);
    expect($shuffledIds)->toBe(['a', 'b', 'c']);
});

it('returns an empty list for an empty item set', function (): void {
    expect(ComputeDeterministicShuffleOrderAction::run([], 'theme-wild-card'))->toBe([]);
});
