<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Data\AppointmentRequestData;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\LessonSeries;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, AppointmentRequest> run(LessonSeries $lessonSeries, CarbonImmutable $through)
 */
class MaterialiseLessonSeriesAction
{
    use AsAction;

    /**
     * @return Collection<int, AppointmentRequest>
     */
    public function handle(LessonSeries $lessonSeries, CarbonImmutable $through): Collection
    {
        return DB::transaction(function () use ($lessonSeries, $through): Collection {
            /** @var LessonSeries $lockedLessonSeries */
            $lockedLessonSeries = LessonSeries::query()
                ->whereKey($lessonSeries->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedLessonSeries->active) {
                return collect();
            }

            $endDate = $this->materialisationEndDate($lockedLessonSeries, $through);
            $currentDate = $this->firstCandidateDate($lockedLessonSeries);

            if ($currentDate->greaterThan($endDate)) {
                return collect();
            }

            $createdAppointments = collect();

            while ($currentDate->lessThanOrEqualTo($endDate)) {
                if ($this->isSeriesOccurrence($lockedLessonSeries, $currentDate)) {
                    $appointmentRequest = $this->materialiseOccurrence($lockedLessonSeries, $currentDate);

                    if ($appointmentRequest instanceof AppointmentRequest) {
                        $createdAppointments->push($appointmentRequest);
                    }
                }

                $currentDate = $currentDate->addDay();
            }

            $lockedLessonSeries->forceFill([
                'materialized_until' => $endDate,
            ])->save();

            return $createdAppointments;
        });
    }

    private function materialisationEndDate(LessonSeries $lessonSeries, CarbonImmutable $through): CarbonImmutable
    {
        $endDate = $through->setTimezone($lessonSeries->timezone)->startOfDay();

        if ($lessonSeries->active_until !== null && $lessonSeries->active_until->lessThan($endDate)) {
            return $lessonSeries->active_until->startOfDay();
        }

        return $endDate;
    }

    private function firstCandidateDate(LessonSeries $lessonSeries): CarbonImmutable
    {
        $today = CarbonImmutable::now($lessonSeries->timezone)->startOfDay();
        $candidateDate = $lessonSeries->active_from->startOfDay()->greaterThan($today)
            ? $lessonSeries->active_from->startOfDay()
            : $today;

        if ($lessonSeries->materialized_until !== null && $lessonSeries->materialized_until->addDay()->greaterThan($candidateDate)) {
            return $lessonSeries->materialized_until->addDay()->startOfDay();
        }

        return $candidateDate;
    }

    private function isSeriesOccurrence(LessonSeries $lessonSeries, CarbonImmutable $candidateDate): bool
    {
        if ($candidateDate->dayOfWeek !== $lessonSeries->day_of_week) {
            return false;
        }

        $weeksFromStart = intdiv((int) $lessonSeries->active_from->startOfDay()->diffInDays($candidateDate), 7);

        return $weeksFromStart % max(1, $lessonSeries->cadence_weeks) === 0;
    }

    private function materialiseOccurrence(LessonSeries $lessonSeries, CarbonImmutable $occurrenceDate): ?AppointmentRequest
    {
        $existingAppointment = AppointmentRequest::query()
            ->where('lesson_series_id', $lessonSeries->getKey())
            ->whereDate('series_occurrence_date', $occurrenceDate->toDateString())
            ->first();

        if ($existingAppointment instanceof AppointmentRequest) {
            return null;
        }

        $startsAt = CarbonImmutable::parse($occurrenceDate->toDateString() . ' ' . $lessonSeries->starts_at, $lessonSeries->timezone);
        $requestedEndsAt = $lessonSeries->duration_minutes === null ? null : $startsAt->addMinutes($lessonSeries->duration_minutes);

        $appointmentRequest = CreateAppointmentRequestAction::run(new AppointmentRequestData(
            serviceId: $lessonSeries->service_id,
            requestedStartsAt: $startsAt,
            timezone: $lessonSeries->timezone,
            customerName: $lessonSeries->customer_name,
            customerEmail: $lessonSeries->customer_email,
            requestedEndsAt: $requestedEndsAt,
            siteId: $lessonSeries->site_id,
            portalAccountId: $lessonSeries->portal_account_id,
            lessonSeriesId: $lessonSeries->id,
            seriesOccurrenceDate: $occurrenceDate,
            staffMemberId: $lessonSeries->staff_member_id,
            locationId: $lessonSeries->location_id,
            customerPhone: $lessonSeries->customer_phone,
            source: 'lesson-series',
            payload: array_replace($lessonSeries->payload ?? [], [
                'lesson_series_id' => $lessonSeries->getKey(),
            ]),
            reminderPreferences: $lessonSeries->reminder_preferences ?? [],
        ));

        return $lessonSeries->auto_confirm_instances
            ? ConfirmAppointmentRequestAction::run($appointmentRequest)
            : $appointmentRequest;
    }
}
