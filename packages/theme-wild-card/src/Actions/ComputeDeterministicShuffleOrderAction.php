<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\WildCard\Actions;

use Lorisleiva\Actions\Concerns\AsObject;

/**
 * §0.1 determinism guardrail: html-cache serves identical HTML to every
 * visitor, so the "card-shuffle-grid" signature widget can never call
 * `Math.random()` (or shuffle in PHP with an unseeded RNG) at render time.
 * Instead every card's visual position is derived deterministically from a
 * stable per-item seed (its identifier, or its title when no identifier is
 * supplied) combined with a page-level seed (the page slug), so the same
 * page always renders the same "shuffled" order for every visitor and every
 * cache hit, while two different pages (or the same page re-curated with a
 * different seed) still look shuffled relative to each other.
 */
final class ComputeDeterministicShuffleOrderAction
{
    use AsObject;

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array{item: array<string, mixed>, position: int}>
     */
    public function handle(array $items, string $pageSeed): array
    {
        $itemCount = count($items);

        if ($itemCount === 0) {
            return [];
        }

        $seeded = [];

        foreach (array_values($items) as $index => $item) {
            $rawItemSeed = data_get($item, 'id') ?? data_get($item, 'title') ?? data_get($item, 'name') ?? $index;
            $itemSeed = is_scalar($rawItemSeed) ? (string) $rawItemSeed : (string) $index;
            $hash = crc32($pageSeed . '::' . $itemSeed . '::' . $index);

            $seeded[] = [
                'item' => $item,
                'hash' => $hash,
                'originalIndex' => $index,
            ];
        }

        usort(
            $seeded,
            static fn (array $left, array $right): int => $left['hash'] <=> $right['hash'] ?: $left['originalIndex'] <=> $right['originalIndex'],
        );

        $withPositions = [];

        foreach ($seeded as $position => $entry) {
            $withPositions[] = [
                'item' => $entry['item'],
                'position' => $position,
            ];
        }

        return $withPositions;
    }
}
