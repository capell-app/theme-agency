<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\LessonNoteVisibilityEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\LessonNote;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static LessonNote run(AppointmentRequest $appointmentRequest, string $summary, ?string $body = null, LessonNoteVisibilityEnum $visibility = LessonNoteVisibilityEnum::Private, ?PortalAccount $portalAccount = null, ?CarbonImmutable $occurredAt = null)
 */
class RecordLessonNoteAction
{
    use AsAction;

    public function handle(
        AppointmentRequest $appointmentRequest,
        string $summary,
        ?string $body = null,
        LessonNoteVisibilityEnum $visibility = LessonNoteVisibilityEnum::Private,
        ?PortalAccount $portalAccount = null,
        ?CarbonImmutable $occurredAt = null,
    ): LessonNote {
        if ($portalAccount instanceof PortalAccount) {
            $this->assertPortalAccountOwnsAppointment($appointmentRequest, $portalAccount);
        }

        /** @var LessonNote $lessonNote */
        $lessonNote = LessonNote::query()->create([
            'appointment_request_id' => $appointmentRequest->getKey(),
            'site_id' => $appointmentRequest->site_id,
            'portal_account_id' => $appointmentRequest->portal_account_id,
            'visibility' => $visibility,
            'summary' => $summary,
            'body' => $body,
            'photo_media_ids' => [],
            'occurred_at' => $occurredAt ?? CarbonImmutable::now(),
        ]);

        return $lessonNote;
    }

    private function assertPortalAccountOwnsAppointment(AppointmentRequest $appointmentRequest, PortalAccount $portalAccount): void
    {
        if ($appointmentRequest->site_id !== $portalAccount->site_id || $appointmentRequest->portal_account_id !== $portalAccount->getKey()) {
            throw ValidationException::withMessages([
                'portal_account_id' => __('capell-bookings::validation.portal_lesson_scope_mismatch'),
            ]);
        }
    }
}
