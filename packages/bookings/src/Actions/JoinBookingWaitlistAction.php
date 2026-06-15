<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingWaitlistStatusEnum;
use Capell\Bookings\Models\BookingWaitlistEntry;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingWaitlistEntry run(string $customerName, string $customerEmail, ?int $siteId = null, ?int $serviceId = null, ?int $staffMemberId = null, ?int $locationId = null, ?int $portalAccountId = null, ?string $customerPhone = null, ?CarbonImmutable $preferredStartsAt = null, ?CarbonImmutable $preferredEndsAt = null, array<string, mixed> $preferences = [])
 */
class JoinBookingWaitlistAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $preferences
     */
    public function handle(
        string $customerName,
        string $customerEmail,
        ?int $siteId = null,
        ?int $serviceId = null,
        ?int $staffMemberId = null,
        ?int $locationId = null,
        ?int $portalAccountId = null,
        ?string $customerPhone = null,
        ?CarbonImmutable $preferredStartsAt = null,
        ?CarbonImmutable $preferredEndsAt = null,
        array $preferences = [],
    ): BookingWaitlistEntry {
        /** @var BookingWaitlistEntry $entry */
        $entry = BookingWaitlistEntry::query()->create([
            'customer_email' => strtolower($customerEmail),
            'customer_name' => $customerName,
            'customer_phone' => $customerPhone,
            'location_id' => $locationId,
            'portal_account_id' => $portalAccountId,
            'preferred_ends_at' => $preferredEndsAt,
            'preferred_starts_at' => $preferredStartsAt,
            'preferences' => $preferences,
            'service_id' => $serviceId,
            'site_id' => $siteId,
            'staff_member_id' => $staffMemberId,
            'status' => BookingWaitlistStatusEnum::Waiting,
        ]);

        return $entry;
    }
}
