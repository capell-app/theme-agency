<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Events\EventRegistrationCancelled;
use Capell\Events\Models\EventRegistration;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EventRegistration run(EventRegistration $registration, EventRegistrationStatusEnum $status)
 */
class UpdateRegistrationStatusAction
{
    use AsAction;

    public function handle(EventRegistration $registration, EventRegistrationStatusEnum $status): EventRegistration
    {
        $registration->forceFill([
            'status' => $status,
            'cancelled_at' => $status === EventRegistrationStatusEnum::Cancelled ? now() : $registration->cancelled_at,
        ])->save();

        $occurrence = $registration->occurrence;
        $occurrence->forceFill([
            'registration_count' => $occurrence->confirmedRegistrationQuantity(),
        ])->save();

        if ($status === EventRegistrationStatusEnum::Cancelled) {
            event(new EventRegistrationCancelled($registration->refresh()));
            $occurrence->refresh();
        }

        return $registration->refresh();
    }
}
