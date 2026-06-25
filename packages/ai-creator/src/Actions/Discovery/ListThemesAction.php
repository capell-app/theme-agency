<?php

declare(strict_types=1);

namespace Capell\AiCreator\Actions\Discovery;

use Capell\Core\Models\Theme;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * Read-only catalogue of installed themes for the spec's theme.key. Returns the
 * branding-relevant subset of Theme.meta so the agent can pick a theme and know
 * which colours/fonts it may override.
 *
 * @method static array<int, array<string, mixed>> run()
 */
final class ListThemesAction
{
    use AsObject;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function handle(): array
    {
        return Theme::query()
            ->orderBy('name')
            ->get(['id', 'key', 'name', 'meta'])
            ->map(static function (Theme $theme): array {
                $meta = $theme->meta ?? [];

                return [
                    'key' => $theme->key,
                    'name' => $theme->name,
                    'colors' => $meta['colors'] ?? [],
                    'font_family' => $meta['font_family'] ?? null,
                    'container' => $meta['container'] ?? null,
                ];
            })
            ->all();
    }
}
