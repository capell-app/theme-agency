<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions\Discovery;

use Capell\Core\Models\Layout;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Read-only catalogue of layouts. The builder maps the first (order-0) page to
 * the `home` layout and the rest to `default`; this surfaces what is installed
 * so the agent knows those keys resolve.
 *
 * @method static array<int, array<string, mixed>> run()
 */
final class ListLayoutsAction
{
    use AsObject;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        return Layout::query()
            ->orderBy('name')
            ->get(['id', 'key', 'name'])
            ->map(static fn (Layout $layout): array => [
                'key' => $layout->key,
                'name' => $layout->name,
            ])
            ->all();
    }
}
