<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Data\DayPlanData;
use Capell\Bookings\Data\DayPlanStopData;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static list<string> run(DayPlanData $dayPlan)
 */
class CheckDayPlanFeasibilityAction
{
    use AsAction;

    /**
     * @return list<string>
     */
    public function handle(DayPlanData $dayPlan): array
    {
        return array_values($dayPlan->stops
            ->flatMap(static fn (DayPlanStopData $stop): array => $stop->warnings)
            ->values()
            ->all());
    }
}
