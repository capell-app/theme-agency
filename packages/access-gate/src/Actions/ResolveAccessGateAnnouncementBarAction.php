<?php

declare(strict_types=1);

namespace Capell\AccessGate\Actions;

use Capell\AccessGate\Data\AnnouncementBarData;
use Capell\AccessGate\Models\Area;
use Capell\AccessGate\Support\AccessGateSchema;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class ResolveAccessGateAnnouncementBarAction
{
    use AsAction;

    public function handle(Request $request, ?string $areaKey = null): ?AnnouncementBarData
    {
        if (! $this->schemaIsReady()) {
            return null;
        }

        $area = Area::query()
            ->where('key', $areaKey ?? $this->defaultAreaKey())
            ->where('announcement_enabled', true)
            ->first();

        if (! $area instanceof Area || ! $this->matchesRequestPath($request, $area)) {
            return null;
        }

        $message = trim((string) $area->announcement_message);
        $shortMessage = trim((string) ($area->announcement_short_message ?: $message));

        if ($message === '' || $shortMessage === '') {
            return null;
        }

        $linkLabel = trim((string) $area->announcement_link_label);
        $linkShortLabel = trim((string) ($area->announcement_link_short_label ?: $linkLabel));
        $linkUrl = trim((string) $area->announcement_link_url);

        return new AnnouncementBarData(
            message: $message,
            shortMessage: $shortMessage,
            linkLabel: $linkLabel !== '' ? $linkLabel : null,
            linkShortLabel: $linkShortLabel !== '' ? $linkShortLabel : null,
            linkUrl: $linkUrl !== '' ? $linkUrl : null,
        );
    }

    private function schemaIsReady(): bool
    {
        $builder = AccessGateSchema::builder();

        return $builder->hasTable('access_gate_areas')
            && $builder->hasColumns('access_gate_areas', [
                'announcement_enabled',
                'announcement_message',
                'announcement_short_message',
                'announcement_link_label',
                'announcement_link_short_label',
                'announcement_link_url',
                'announcement_path_patterns',
            ]);
    }

    private function matchesRequestPath(Request $request, Area $area): bool
    {
        $patterns = collect($area->announcement_path_patterns ?? [])
            ->filter(fn (mixed $pattern): bool => is_string($pattern) && trim($pattern) !== '')
            ->map(fn (string $pattern): string => trim($pattern, " \t\n\r\0\x0B/"))
            ->filter()
            ->values()
            ->all();

        if ($patterns === []) {
            return false;
        }

        $path = trim($request->path(), '/');

        return collect($patterns)->contains(fn (string $pattern): bool => Str::is($pattern, $path));
    }

    private function defaultAreaKey(): string
    {
        $key = config('access-gate.install.default_area.key', 'capell-preview');

        return is_string($key) && trim($key) !== '' ? trim($key) : 'capell-preview';
    }
}
