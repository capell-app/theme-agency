<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(AppointmentRequest $appointmentRequest, ?CarbonImmutable $cancelledAt = null)
 */
class CalculateCancellationFeeAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest, ?CarbonImmutable $cancelledAt = null): int
    {
        $cancelledAt ??= CarbonImmutable::now();
        $freeWindowHoursConfig = config('capell-bookings.cancellation_free_window_hours', 24);
        $feePercentConfig = config('capell-bookings.late_cancellation_fee_percent', 50);
        $freeWindowHours = is_numeric($freeWindowHoursConfig) ? (int) $freeWindowHoursConfig : 24;
        $feePercent = is_numeric($feePercentConfig) ? (int) $feePercentConfig : 50;
        $feeBasePence = (int) ($appointmentRequest->payment_required_amount_pence ?? 0);

        if ($feeBasePence < 1 || $appointmentRequest->requested_starts_at->diffInHours($cancelledAt, absolute: false) <= -$freeWindowHours) {
            return 0;
        }

        return (int) round($feeBasePence * ($feePercent / 100));
    }
}
