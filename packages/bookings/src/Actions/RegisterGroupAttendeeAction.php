<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentAuditEventEnum;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingGroupSession;
use Capell\Bookings\Models\BookingLocation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static AppointmentRequest run(BookingGroupSession $groupSession, string $customerName, string $customerEmail, ?int $portalAccountId = null, ?string $customerPhone = null)
 */
class RegisterGroupAttendeeAction
{
    use AsAction;

    public function handle(
        BookingGroupSession $groupSession,
        string $customerName,
        string $customerEmail,
        ?int $portalAccountId = null,
        ?string $customerPhone = null,
    ): AppointmentRequest {
        return DB::transaction(function () use ($groupSession, $customerName, $customerEmail, $portalAccountId, $customerPhone): AppointmentRequest {
            /** @var BookingGroupSession $lockedGroupSession */
            $lockedGroupSession = BookingGroupSession::query()
                ->whereKey($groupSession->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedGroupSession->status->acceptsRegistrations()) {
                throw ValidationException::withMessages([
                    'status' => __('capell-bookings::validation.group_session_closed'),
                ]);
            }

            $registeredCount = $lockedGroupSession->appointmentRequests()
                ->whereIn('status', [
                    AppointmentRequestStatusEnum::Provisional->value,
                    AppointmentRequestStatusEnum::Requested->value,
                    AppointmentRequestStatusEnum::Confirmed->value,
                ])
                ->count();

            if ($registeredCount >= $lockedGroupSession->capacity) {
                throw ValidationException::withMessages([
                    'capacity' => __('capell-bookings::validation.group_session_full'),
                ]);
            }
            $locationTimezone = $lockedGroupSession->location instanceof BookingLocation
                ? $lockedGroupSession->location->timezone
                : null;
            $defaultTimezone = config('app.timezone', 'UTC');

            /** @var AppointmentRequest $appointmentRequest */
            $appointmentRequest = AppointmentRequest::query()->create([
                'calendar_uid' => (string) Str::uuid(),
                'customer_email' => $customerEmail,
                'customer_name' => $customerName,
                'customer_phone' => $customerPhone,
                'group_session_id' => $lockedGroupSession->getKey(),
                'location_id' => $lockedGroupSession->location_id,
                'offered_window_ends_at' => $lockedGroupSession->offered_window_ends_at,
                'offered_window_starts_at' => $lockedGroupSession->offered_window_starts_at,
                'portal_account_id' => $portalAccountId,
                'requested_at' => CarbonImmutable::now(),
                'requested_ends_at' => $lockedGroupSession->ends_at,
                'requested_starts_at' => $lockedGroupSession->starts_at,
                'service_id' => $lockedGroupSession->service_id,
                'site_id' => $lockedGroupSession->site_id,
                'staff_member_id' => $lockedGroupSession->staff_member_id,
                'status' => AppointmentRequestStatusEnum::Requested,
                'timezone' => is_string($locationTimezone) ? $locationTimezone : (is_string($defaultTimezone) ? $defaultTimezone : 'UTC'),
            ]);

            RecordAppointmentAuditLogAction::run(
                appointmentRequest: $appointmentRequest,
                event: AppointmentAuditEventEnum::Created,
                statusTo: AppointmentRequestStatusEnum::Requested,
                payload: ['group_session_id' => $lockedGroupSession->getKey()],
            );

            return $appointmentRequest;
        });
    }
}
