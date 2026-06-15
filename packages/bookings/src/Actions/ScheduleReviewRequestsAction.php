<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingReviewRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingReviewRequest;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(?CarbonImmutable $now = null)
 */
class ScheduleReviewRequestsAction
{
    use AsAction;

    public function handle(?CarbonImmutable $now = null): int
    {
        $now ??= CarbonImmutable::now();
        $reviewOffsetsDays = config('capell-bookings.review_offsets_days', [1, 2, 10]);
        $scheduled = 0;

        /** @var EloquentCollection<int, AppointmentRequest> $appointmentRequests */
        $appointmentRequests = AppointmentRequest::query()
            ->whereNotNull('completed_at')
            ->get();

        foreach ($appointmentRequests as $appointmentRequest) {
            if ($appointmentRequest->completed_at === null) {
                continue;
            }

            if (ShouldSuppressReviewRequestAction::run($appointmentRequest)) {
                continue;
            }

            $completedAt = CarbonImmutable::parse($appointmentRequest->completed_at);

            foreach (is_array($reviewOffsetsDays) ? $reviewOffsetsDays : [1, 2, 10] as $offsetDays) {
                $offsetDaysValue = is_numeric($offsetDays) ? (int) $offsetDays : 0;
                $scheduledFor = $completedAt->addDays($offsetDaysValue);

                if ($scheduledFor->greaterThan($now->addDays(30))) {
                    continue;
                }

                $reviewRequest = BookingReviewRequest::query()->firstOrCreate(
                    [
                        'appointment_request_id' => $appointmentRequest->getKey(),
                        'scheduled_for' => $scheduledFor,
                    ],
                    [
                        'site_id' => $appointmentRequest->site_id,
                        'portal_account_id' => $appointmentRequest->portal_account_id,
                        'status' => BookingReviewRequestStatusEnum::Scheduled,
                        'requested_at' => $now,
                    ],
                );

                CreateReviewLoopAction::run($reviewRequest);

                $scheduled++;
            }
        }

        return $scheduled;
    }
}
