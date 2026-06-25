<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions\Discovery;

use Capell\Core\Models\Blueprint;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Read-only catalogue of page blueprints (keys for CapellSiteSpecPageData.pageType).
 * Reads key/name/meta directly — the `type` column is cast to PageTypeData, so it
 * is filtered on the raw column and never read back as an attribute here.
 *
 * @method static array<int, array<string, mixed>> run()
 */
final class ListPageTypesAction
{
    use AsObject;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        return Blueprint::query()
            ->where('type', 'page')
            ->orderBy('name')
            ->get(['id', 'key', 'name', 'meta'])
            ->map(static function (Blueprint $blueprint): array {
                $meta = $blueprint->meta ?? [];

                return [
                    'key' => $blueprint->key,
                    'name' => $blueprint->name,
                    'content_structure' => $meta['content_structure'] ?? null,
                ];
            })
            ->all();
    }
}
