<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(?CarbonImmutable $now = null)
 */
class ExpireStaleHoldsAction
{
    use AsAction;

    public function handle(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();

        return AppointmentRequest::query()
            ->where('status', AppointmentRequestStatusEnum::Provisional->value)
            ->whereNotNull('hold_expires_at')
            ->where('hold_expires_at', '<=', $now)
            ->update([
                'status' => AppointmentRequestStatusEnum::Cancelled->value,
                'cancelled_at' => $now,
            ]);
    }
}
