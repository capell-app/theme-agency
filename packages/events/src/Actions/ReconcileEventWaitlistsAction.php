<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Models\EventOccurrence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run()
 */
final class ReconcileEventWaitlistsAction
{
    use AsAction;

    public function handle(): int
    {
        $promotedRegistrations = 0;

        EventOccurrence::query()
            ->where('waitlist_enabled', true)
            ->whereHas('registrations', function (Builder $query): void {
                $query->where('status', EventRegistrationStatusEnum::Waitlisted);
            })
            ->lazyById()
            ->each(function (EventOccurrence $occurrence) use (&$promotedRegistrations): void {
                $promotedRegistrations += $this->reconcileOccurrence($occurrence);
            });

        return $promotedRegistrations;
    }

    private function reconcileOccurrence(EventOccurrence $occurrence): int
    {
        return DB::transaction(function () use ($occurrence): int {
            /** @var EventOccurrence $lockedOccurrence */
            $lockedOccurrence = EventOccurrence::query()
                ->whereKey($occurrence->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $promotedRegistrations = 0;

            while (PromoteWaitlistAction::run($lockedOccurrence) !== null) {
                $promotedRegistrations++;
                $lockedOccurrence->refresh();
            }

            return $promotedRegistrations;
        });
    }
}
