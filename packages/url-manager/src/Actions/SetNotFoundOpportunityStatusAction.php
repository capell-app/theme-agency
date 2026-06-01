<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Enums\NotFoundOpportunityStatus;
use Capell\UrlManager\Models\NotFoundOpportunity;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

final class SetNotFoundOpportunityStatusAction
{
    use AsAction;

    /**
     * @param  NotFoundOpportunity|Collection<int, NotFoundOpportunity>  $opportunities
     */
    public function handle(NotFoundOpportunity|Collection $opportunities, NotFoundOpportunityStatus $status): int
    {
        $collection = $opportunities instanceof NotFoundOpportunity ? collect([$opportunities]) : $opportunities;
        $updated = 0;

        $collection
            ->filter(fn (NotFoundOpportunity $opportunity): bool => $opportunity->status !== NotFoundOpportunityStatus::Converted)
            ->filter(fn (NotFoundOpportunity $opportunity): bool => $opportunity->status !== $status)
            ->each(function (NotFoundOpportunity $opportunity) use ($status, &$updated): void {
                $opportunity->forceFill(['status' => $status->value])->save();
                $updated++;
            });

        return $updated;
    }
}
