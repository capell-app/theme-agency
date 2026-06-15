<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingMessageLog;
use Capell\Bookings\Models\BookingReviewRequest;
use Capell\Bookings\Models\LessonNote;
use Capell\Bookings\Models\MessagingConsent;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(int $siteId, PortalAccount $portalAccount)
 */
class ErasePortalBookingDataAction
{
    use AsAction;

    public function handle(int $siteId, PortalAccount $portalAccount): int
    {
        if ($portalAccount->site_id !== $siteId) {
            throw ValidationException::withMessages([
                'site_id' => __('capell-bookings::validation.portal_account_site_mismatch'),
            ]);
        }

        return DB::transaction(function () use ($siteId, $portalAccount): int {
            $portalAccountKey = $portalAccount->getKey();
            $portalAccountIdentifier = is_scalar($portalAccountKey) ? (string) $portalAccountKey : 'unknown';
            $appointmentIds = AppointmentRequest::query()
                ->where('site_id', $siteId)
                ->where('portal_account_id', $portalAccount->getKey())
                ->pluck('id');

            LessonNote::query()->whereIn('appointment_request_id', $appointmentIds)->delete();
            BookingMessageLog::query()->whereIn('appointment_request_id', $appointmentIds)->delete();
            BookingReviewRequest::query()->whereIn('appointment_request_id', $appointmentIds)->delete();
            MessagingConsent::query()
                ->where('site_id', $siteId)
                ->where('portal_account_id', $portalAccount->getKey())
                ->delete();

            return AppointmentRequest::query()
                ->whereIn('id', $appointmentIds)
                ->update([
                    'customer_email' => 'erased-' . $portalAccountIdentifier . '@example.invalid',
                    'customer_name' => __('capell-bookings::generic.erased_customer'),
                    'customer_phone' => null,
                    'notes' => null,
                    'payload' => null,
                    'portal_account_id' => null,
                ]);
        });
    }
}
