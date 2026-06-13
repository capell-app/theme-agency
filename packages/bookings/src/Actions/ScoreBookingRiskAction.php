<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array{score: int, level: string, signals: list<string>} run(AppointmentRequest $appointmentRequest)
 */
class ScoreBookingRiskAction
{
    use AsAction;

    /**
     * @return array{score: int, level: string, signals: list<string>}
     */
    public function handle(AppointmentRequest $appointmentRequest): array
    {
        $score = 0;
        $signals = [];

        $recentIssues = AppointmentRequest::query()
            ->where('customer_email', $appointmentRequest->customer_email)
            ->where('id', '!=', $appointmentRequest->getKey())
            ->where('requested_starts_at', '>=', CarbonImmutable::now()->subMonths(6))
            ->whereIn('status', [AppointmentRequestStatusEnum::Cancelled, AppointmentRequestStatusEnum::NoShow])
            ->count();

        if ($recentIssues >= 2) {
            $score += 40;
            $signals[] = 'recent_cancellations_or_no_shows';
        }

        if ($appointmentRequest->hold_expires_at !== null && $appointmentRequest->hold_expires_at->isPast()) {
            $score += 20;
            $signals[] = 'expired_hold';
        }

        if ($appointmentRequest->payment_required_amount_pence !== null && $appointmentRequest->payment_confirmed_at === null) {
            $score += 20;
            $signals[] = 'payment_required_unpaid';
        }

        if ($appointmentRequest->customer_phone === null || trim($appointmentRequest->customer_phone) === '') {
            $score += 10;
            $signals[] = 'missing_phone';
        }

        return [
            'level' => $score >= 60 ? 'high' : ($score >= 30 ? 'medium' : 'low'),
            'score' => min($score, 100),
            'signals' => $signals,
        ];
    }
}
