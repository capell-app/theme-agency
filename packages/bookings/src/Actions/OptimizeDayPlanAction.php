<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Data\DayPlanData;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static DayPlanData run(int $staffMemberId, CarbonImmutable $date)
 */
class OptimizeDayPlanAction
{
    use AsAction;

    public function handle(int $staffMemberId, CarbonImmutable $date): DayPlanData
    {
        /** @var EloquentCollection<int, AppointmentRequest> $unpinnedAppointmentRequests */
        $unpinnedAppointmentRequests = AppointmentRequest::query()
            ->where('staff_member_id', $staffMemberId)
            ->where('is_time_pinned', false)
            ->whereIn('status', [
                AppointmentRequestStatusEnum::Provisional->value,
                AppointmentRequestStatusEnum::Requested->value,
                AppointmentRequestStatusEnum::Confirmed->value,
            ])
            ->whereDate('requested_starts_at', $date->toDateString())
            ->orderBy('location_id')
            ->orderBy('requested_starts_at')
            ->get();

        $cursor = $date->copy()->setTime(9, 0);

        foreach ($unpinnedAppointmentRequests as $appointmentRequest) {
            $durationMinutes = (int) $appointmentRequest->requested_starts_at->diffInMinutes($appointmentRequest->requested_ends_at);
            $appointmentRequest->forceFill([
                'requested_starts_at' => $cursor,
                'requested_ends_at' => $cursor->addMinutes($durationMinutes),
            ])->save();

            $cursor = $cursor->addMinutes($durationMinutes + 15);
        }

        return BuildDayPlanAction::run($staffMemberId, $date);
    }
}
