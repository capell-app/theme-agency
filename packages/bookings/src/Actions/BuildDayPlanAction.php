<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Data\DayPlanData;
use Capell\Bookings\Data\DayPlanStopData;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static DayPlanData run(int $staffMemberId, CarbonImmutable $date)
 */
class BuildDayPlanAction
{
    use AsAction;

    public function handle(int $staffMemberId, CarbonImmutable $date): DayPlanData
    {
        /** @var EloquentCollection<int, AppointmentRequest> $appointmentRequests */
        $appointmentRequests = AppointmentRequest::query()
            ->with('location')
            ->where('staff_member_id', $staffMemberId)
            ->whereIn('status', [
                AppointmentRequestStatusEnum::Provisional->value,
                AppointmentRequestStatusEnum::Requested->value,
                AppointmentRequestStatusEnum::Confirmed->value,
            ])
            ->whereDate('requested_starts_at', $date->toDateString())
            ->orderBy('requested_starts_at')
            ->get();

        $previousAppointmentRequest = null;
        $totalTravelMinutes = 0;
        $totalTravelMiles = 0.0;

        /** @var Collection<int, DayPlanStopData> $stops */
        $stops = $appointmentRequests->map(function (AppointmentRequest $appointmentRequest) use (&$previousAppointmentRequest, &$totalTravelMinutes, &$totalTravelMiles): DayPlanStopData {
            $estimate = EstimateTravelTimeAction::run(
                origin: $previousAppointmentRequest?->location,
                destination: $appointmentRequest->location,
                departAt: $previousAppointmentRequest?->requested_ends_at,
            );
            $warnings = [];

            if ($previousAppointmentRequest instanceof AppointmentRequest && $previousAppointmentRequest->requested_ends_at->addMinutes($estimate->durationMinutes)->greaterThan($appointmentRequest->requested_starts_at)) {
                $warnings[] = __('capell-bookings::validation.day_plan_travel_overlap');
            }

            $previousAppointmentRequest = $appointmentRequest;
            $totalTravelMinutes += $estimate->durationMinutes;
            $totalTravelMiles += $estimate->distanceMiles;

            return new DayPlanStopData(
                appointmentRequestId: (int) $appointmentRequest->id,
                locationId: $appointmentRequest->location_id,
                startsAt: $appointmentRequest->requested_starts_at,
                endsAt: $appointmentRequest->requested_ends_at,
                timePinned: $appointmentRequest->is_time_pinned,
                travelBeforeMinutes: $estimate->durationMinutes,
                travelBeforeMiles: $estimate->distanceMiles,
                warnings: $warnings,
            );
        });

        return new DayPlanData(
            staffMemberId: $staffMemberId,
            date: $date,
            stops: $stops,
            totalTravelMinutes: $totalTravelMinutes,
            totalTravelMiles: round($totalTravelMiles, 2),
            warnings: array_values($stops->flatMap(static fn (DayPlanStopData $stop): array => $stop->warnings)->all()),
        );
    }
}
