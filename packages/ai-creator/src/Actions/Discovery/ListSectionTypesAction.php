<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions\Discovery;

use Capell\Core\Models\Blueprint;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Read-only catalogue of section blueprints (keys for CapellSiteSpecSectionData.type).
 * Section blueprints are contributed by the content-sections package as raw
 * type='section' rows; on an install without them this returns an empty list and
 * the agent falls back to the default `content` section key.
 *
 * @method static array<int, array<string, mixed>> run()
 */
final class ListSectionTypesAction
{
    use AsObject;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        return Blueprint::query()
            ->where('type', 'section')
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
