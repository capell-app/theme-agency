<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Core\Models\Site;
use Capell\Events\Models\EventOccurrence;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, EventOccurrence> run(Site $site, CarbonImmutable $startsAt, CarbonImmutable $endsAt, ?int $limit = null)
 */
class QueryPublicEventOccurrencesAction
{
    use AsAction;

    /**
     * @return Collection<int, EventOccurrence>
     */
    public function handle(Site $site, CarbonImmutable $startsAt, CarbonImmutable $endsAt, ?int $limit = null): Collection
    {
        $query = EventOccurrence::query()
            ->with(['event.pageUrl', 'event.translation', 'event.translations', 'venue'])
            ->whereHas('event', function (Builder $query) use ($site): void {
                $query
                    ->where('site_id', $site->getKey())
                    ->where('visibility', 'public')
                    ->publishedDate();
            })
            ->public()
            ->inRange($startsAt, $endsAt)
            ->ordered();

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }
}
