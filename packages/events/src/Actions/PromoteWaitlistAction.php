<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Enums\EventNotificationTypeEnum;
use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static ?EventRegistration run(EventOccurrence $occurrence)
 */
class PromoteWaitlistAction
{
    use AsAction;

    public function handle(EventOccurrence $occurrence): ?EventRegistration
    {
        $remainingCapacity = $occurrence->remainingCapacity();

        if ($remainingCapacity !== null && $remainingCapacity < 1) {
            return null;
        }

        /** @var EventRegistration|null $registration */
        $registration = $occurrence->registrations()
            ->where('status', EventRegistrationStatusEnum::Waitlisted)
            ->orderBy('waitlist_position')
            ->first();

        if (! $registration instanceof EventRegistration) {
            return null;
        }

        if ($occurrence->isFullForQuantity($registration->quantity)) {
            return null;
        }

        $registration->forceFill([
            'status' => EventRegistrationStatusEnum::Pending,
            'waitlist_position' => null,
        ])->save();

        $occurrence->forceFill([
            'registration_count' => $occurrence->confirmedRegistrationQuantity(),
        ])->save();

        SendEventNotificationAction::run($registration, EventNotificationTypeEnum::WaitlistPromotion);

        return $registration->refresh();
    }
}
