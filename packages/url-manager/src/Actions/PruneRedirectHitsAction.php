<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Models\RedirectHit;
use Lorisleiva\Actions\Concerns\AsAction;

final class PruneRedirectHitsAction
{
    use AsAction;

    public function handle(?int $olderThanDays = null): int
    {
        $days = max(1, $olderThanDays ?? (int) config('capell-url-manager.hit_recording.retention_days', 365));
        $cutoff = now()->subDays($days);

        return RedirectHit::query()
            ->where('hit_at', '<', $cutoff)
            ->delete();
    }
}
