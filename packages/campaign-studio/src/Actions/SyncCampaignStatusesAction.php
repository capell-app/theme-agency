<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Actions;

use Capell\CampaignStudio\Enums\CampaignStatus;
use Capell\CampaignStudio\Models\CampaignGroup;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncCampaignStatusesAction
{
    use AsAction;

    /**
     * @return array{scheduled_to_active: int, active_to_ended: int}
     */
    public function handle(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();

        $activated = CampaignGroup::query()
            ->where('status', CampaignStatus::Scheduled)
            ->whereNotNull('starts_at')
            ->where('starts_at', '<=', $now)
            ->where(function (Builder $query) use ($now): void {
                $query
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>', $now);
            })
            ->update([
                'status' => CampaignStatus::Active,
                'updated_at' => $now,
            ]);

        $ended = CampaignGroup::query()
            ->where('status', CampaignStatus::Active)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<=', $now)
            ->update([
                'status' => CampaignStatus::Ended,
                'updated_at' => $now,
            ]);

        return [
            'scheduled_to_active' => $activated,
            'active_to_ended' => $ended,
        ];
    }
}
