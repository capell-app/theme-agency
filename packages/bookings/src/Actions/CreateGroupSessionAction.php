<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingGroupSessionStatusEnum;
use Capell\Bookings\Models\BookingGroupSession;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingGroupSession run(int $serviceId, string $title, CarbonImmutable $startsAt, CarbonImmutable $endsAt, ?int $siteId = null, ?int $staffMemberId = null, ?int $locationId = null, ?int $capacity = null, ?int $feePence = null, ?string $description = null)
 */
class CreateGroupSessionAction
{
    use AsAction;

    public function handle(
        int $serviceId,
        string $title,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        ?int $siteId = null,
        ?int $staffMemberId = null,
        ?int $locationId = null,
        ?int $capacity = null,
        ?int $feePence = null,
        ?string $description = null,
    ): BookingGroupSession {
        $defaultCapacityConfig = config('capell-bookings.default_group_capacity', 8);
        $defaultCapacity = is_numeric($defaultCapacityConfig) ? (int) $defaultCapacityConfig : 8;

        /** @var BookingGroupSession $groupSession */
        $groupSession = BookingGroupSession::query()->create([
            'site_id' => $siteId,
            'service_id' => $serviceId,
            'staff_member_id' => $staffMemberId,
            'location_id' => $locationId,
            'title' => $title,
            'description' => $description,
            'status' => BookingGroupSessionStatusEnum::Draft,
            'capacity' => $capacity ?? $defaultCapacity,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'offered_window_starts_at' => $startsAt,
            'offered_window_ends_at' => $endsAt,
            'fee_pence' => $feePence,
        ]);

        return $groupSession;
    }
}
