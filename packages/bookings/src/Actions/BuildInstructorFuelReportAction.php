<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array{staff_member_id: int|null, from: string, until: string, appointment_count: int, travel_distance_miles: float, fuel_allowance_pence: int, unacknowledged_fuel_allowance_pence: int} run(?int $staffMemberId = null, ?CarbonImmutable $from = null, ?CarbonImmutable $until = null)
 */
class BuildInstructorFuelReportAction
{
    use AsAction;

    /**
     * @return array{staff_member_id: int|null, from: string, until: string, appointment_count: int, travel_distance_miles: float, fuel_allowance_pence: int, unacknowledged_fuel_allowance_pence: int}
     */
    public function handle(?int $staffMemberId = null, ?CarbonImmutable $from = null, ?CarbonImmutable $until = null): array
    {
        $from ??= CarbonImmutable::now()->startOfMonth();
        $until ??= CarbonImmutable::now()->endOfMonth();

        $query = AppointmentRequest::query()
            ->whereBetween('requested_starts_at', [$from, $until])
            ->when($staffMemberId !== null, static fn (Builder $query): Builder => $query->where('staff_member_id', $staffMemberId));

        /** @var list<AppointmentRequest> $appointments */
        $appointments = $query->get()->all();

        $distanceMiles = array_reduce(
            $appointments,
            static fn (float $carry, AppointmentRequest $appointmentRequest): float => $carry + (float) ($appointmentRequest->travel_distance_miles ?? 0),
            0.0,
        );

        $fuelAllowancePence = array_reduce(
            $appointments,
            static fn (int $carry, AppointmentRequest $appointmentRequest): int => $carry + (int) ($appointmentRequest->fuel_allowance_pence ?? 0),
            0,
        );

        $unacknowledgedFuelAllowancePence = array_reduce(
            $appointments,
            static fn (int $carry, AppointmentRequest $appointmentRequest): int => $carry + ($appointmentRequest->fuel_acknowledged_at === null ? (int) ($appointmentRequest->fuel_allowance_pence ?? 0) : 0),
            0,
        );

        return [
            'appointment_count' => count($appointments),
            'from' => $from->toDateTimeString(),
            'fuel_allowance_pence' => $fuelAllowancePence,
            'staff_member_id' => $staffMemberId,
            'travel_distance_miles' => round($distanceMiles, 2),
            'unacknowledged_fuel_allowance_pence' => $unacknowledgedFuelAllowancePence,
            'until' => $until->toDateTimeString(),
        ];
    }
}
