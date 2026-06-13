<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingReviewRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<string, mixed> run(int $siteId, PortalAccount $portalAccount)
 */
class ExportPortalBookingDataAction
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(int $siteId, PortalAccount $portalAccount): array
    {
        if ($portalAccount->site_id !== $siteId) {
            throw ValidationException::withMessages([
                'site_id' => __('capell-bookings::validation.portal_account_site_mismatch'),
            ]);
        }

        $appointmentRequests = AppointmentRequest::query()
            ->with(['lessonNotes', 'messageLogs', 'reviewRequests'])
            ->where('site_id', $siteId)
            ->where('portal_account_id', $portalAccount->getKey())
            ->orderBy('requested_starts_at')
            ->get();

        return [
            'portal_account_id' => $portalAccount->getKey(),
            'site_id' => $siteId,
            'appointments' => $appointmentRequests->map(static fn (AppointmentRequest $appointmentRequest): array => [
                'id' => $appointmentRequest->getKey(),
                'starts_at' => $appointmentRequest->requested_starts_at->toIso8601String(),
                'ends_at' => $appointmentRequest->requested_ends_at->toIso8601String(),
                'status' => $appointmentRequest->status->value,
                'customer_email' => $appointmentRequest->customer_email,
                'notes' => $appointmentRequest->lessonNotes->pluck('summary')->all(),
                'messages' => $appointmentRequest->messageLogs->pluck('type')->all(),
                'reviews' => $appointmentRequest->reviewRequests
                    ->pluck('status')
                    ->filter(static fn (mixed $status): bool => $status instanceof BookingReviewRequestStatusEnum)
                    ->map(static fn (BookingReviewRequestStatusEnum $status): string => $status->value)
                    ->values()
                    ->all(),
            ])->values()->all(),
        ];
    }
}
