<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingLessonBundleStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingLessonBundle;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingLessonBundle run(BookingLessonBundle $bundle, AppointmentRequest $appointmentRequest)
 */
class ApplyLessonBundleCreditAction
{
    use AsAction;

    public function handle(BookingLessonBundle $bundle, AppointmentRequest $appointmentRequest): BookingLessonBundle
    {
        if ($bundle->status !== BookingLessonBundleStatusEnum::Active) {
            throw ValidationException::withMessages([
                'bundle' => __('capell-bookings::validation.lesson_bundle_inactive'),
            ]);
        }

        if ($bundle->expires_at instanceof CarbonImmutable && $bundle->expires_at->isPast()) {
            $bundle->forceFill(['status' => BookingLessonBundleStatusEnum::Expired])->save();

            throw ValidationException::withMessages([
                'bundle' => __('capell-bookings::validation.lesson_bundle_expired'),
            ]);
        }

        if ($bundle->credits_remaining < 1) {
            $bundle->forceFill(['status' => BookingLessonBundleStatusEnum::Exhausted])->save();

            throw ValidationException::withMessages([
                'bundle' => __('capell-bookings::validation.lesson_bundle_exhausted'),
            ]);
        }

        $bundle->forceFill([
            'credits_remaining' => $bundle->credits_remaining - 1,
            'status' => $bundle->credits_remaining === 1 ? BookingLessonBundleStatusEnum::Exhausted : BookingLessonBundleStatusEnum::Active,
            'meta' => array_merge($bundle->meta ?? [], [
                'last_applied_appointment_request_id' => $appointmentRequest->getKey(),
            ]),
        ])->save();

        return $bundle->refresh();
    }
}
