<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Data\EventRegistrationData;
use Capell\Events\Enums\EventRegistrationStatusEnum;
use Capell\Events\Events\EventRegistrationCreated;
use Capell\Events\Models\EventOccurrence;
use Capell\Events\Models\EventRegistration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EventRegistration run(EventOccurrence $occurrence, EventRegistrationData $registrationData)
 */
class RegisterForEventOccurrenceAction
{
    use AsAction;

    public function handle(EventOccurrence $occurrence, EventRegistrationData $registrationData): EventRegistration
    {
        return DB::transaction(function () use ($occurrence, $registrationData): EventRegistration {
            /** @var EventOccurrence $lockedOccurrence */
            $lockedOccurrence = EventOccurrence::query()
                ->whereKey($occurrence->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedOccurrence->booking_mode->allowsNativeRsvp()) {
                throw ValidationException::withMessages([
                    'event' => __('capell-events::validation.rsvp_disabled'),
                ]);
            }

            if (! $lockedOccurrence->status->isPubliclyBookable()) {
                throw ValidationException::withMessages([
                    'event' => __('capell-events::validation.event_not_bookable'),
                ]);
            }

            $status = $this->registrationStatus($lockedOccurrence, $registrationData->quantity);

            /** @var EventRegistration $registration */
            $registration = EventRegistration::query()->create([
                'event_occurrence_id' => $lockedOccurrence->getKey(),
                'form_submission_id' => $registrationData->formSubmissionId,
                'status' => $status,
                'name' => $registrationData->name,
                'email' => $registrationData->email,
                'phone' => $registrationData->phone,
                'quantity' => $registrationData->quantity,
                'waitlist_position' => $status === EventRegistrationStatusEnum::Waitlisted ? $this->nextWaitlistPosition($lockedOccurrence) : null,
                'payload' => $registrationData->payload,
                'registered_at' => now(),
            ]);

            $this->refreshRegistrationCount($lockedOccurrence);

            event(new EventRegistrationCreated($registration->refresh()));

            DB::afterCommit(static function () use ($registration): void {
                ScheduleEventNotificationsAction::run($registration->refresh());
            });

            return $registration;
        });
    }

    private function registrationStatus(EventOccurrence $occurrence, int $quantity): EventRegistrationStatusEnum
    {
        if (! $occurrence->isFullForQuantity($quantity)) {
            return EventRegistrationStatusEnum::Pending;
        }

        if ($occurrence->waitlist_enabled) {
            return EventRegistrationStatusEnum::Waitlisted;
        }

        throw ValidationException::withMessages([
            'quantity' => __('capell-events::validation.event_full'),
        ]);
    }

    private function nextWaitlistPosition(EventOccurrence $occurrence): int
    {
        return ((int) $occurrence->registrations()
            ->where('status', EventRegistrationStatusEnum::Waitlisted)
            ->max('waitlist_position')) + 1;
    }

    private function refreshRegistrationCount(EventOccurrence $occurrence): void
    {
        $occurrence->forceFill([
            'registration_count' => $occurrence->confirmedRegistrationQuantity(),
        ])->save();
    }
}
