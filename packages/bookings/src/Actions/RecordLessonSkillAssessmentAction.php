<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingLessonSkillStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingLessonSkillAssessment;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingLessonSkillAssessment run(AppointmentRequest $appointmentRequest, string $skill, BookingLessonSkillStatusEnum $status, ?int $confidence = null, ?string $notes = null, array<string, mixed> $meta = [])
 */
class RecordLessonSkillAssessmentAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $meta
     */
    public function handle(
        AppointmentRequest $appointmentRequest,
        string $skill,
        BookingLessonSkillStatusEnum $status,
        ?int $confidence = null,
        ?string $notes = null,
        array $meta = [],
    ): BookingLessonSkillAssessment {
        if ($confidence !== null && ($confidence < 1 || $confidence > 5)) {
            throw ValidationException::withMessages([
                'confidence' => __('capell-bookings::validation.lesson_skill_confidence_range'),
            ]);
        }

        /** @var BookingLessonSkillAssessment $assessment */
        $assessment = BookingLessonSkillAssessment::query()->updateOrCreate(
            [
                'appointment_request_id' => $appointmentRequest->getKey(),
                'skill' => $skill,
            ],
            [
                'confidence' => $confidence,
                'meta' => $meta,
                'notes' => $notes,
                'portal_account_id' => $appointmentRequest->portal_account_id,
                'site_id' => $appointmentRequest->site_id,
                'status' => $status,
            ],
        );

        return $assessment;
    }
}
