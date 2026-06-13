<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\AppointmentRequest;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static AppointmentRequest run(AppointmentRequest $appointmentRequest, PortalAccount $portalAccount, Authenticatable $user)
 */
class LinkBookingToPortalAccountAction
{
    use AsAction;

    public function handle(
        AppointmentRequest $appointmentRequest,
        PortalAccount $portalAccount,
        Authenticatable $user,
    ): AppointmentRequest {
        $verifiedEmail = $this->verifiedUserEmail($user);
        $normalizedEmail = PortalAccount::normalizeEmail($verifiedEmail);

        if ($normalizedEmail === null) {
            throw ValidationException::withMessages([
                'email' => __('capell-bookings::validation.portal_account_email_not_verified'),
            ]);
        }

        return DB::transaction(function () use ($appointmentRequest, $portalAccount, $normalizedEmail): AppointmentRequest {
            /** @var AppointmentRequest $lockedAppointmentRequest */
            $lockedAppointmentRequest = AppointmentRequest::query()
                ->whereKey($appointmentRequest->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertCanLink($lockedAppointmentRequest, $portalAccount, $normalizedEmail);

            if ((int) $lockedAppointmentRequest->portal_account_id === (int) $portalAccount->getKey()) {
                return $lockedAppointmentRequest;
            }

            $lockedAppointmentRequest->forceFill([
                'site_id' => $portalAccount->site_id,
                'portal_account_id' => $portalAccount->getKey(),
            ])->save();

            return $lockedAppointmentRequest->refresh();
        });
    }

    private function assertCanLink(
        AppointmentRequest $appointmentRequest,
        PortalAccount $portalAccount,
        string $normalizedEmail,
    ): void {
        if ($appointmentRequest->portal_account_id !== null && (int) $appointmentRequest->portal_account_id !== (int) $portalAccount->getKey()) {
            throw ValidationException::withMessages([
                'portal_account_id' => __('capell-bookings::validation.appointment_already_linked'),
            ]);
        }

        if ($appointmentRequest->site_id !== null && (int) $appointmentRequest->site_id !== (int) $portalAccount->site_id) {
            throw ValidationException::withMessages([
                'site_id' => __('capell-bookings::validation.portal_account_site_mismatch'),
            ]);
        }

        if (PortalAccount::normalizeEmail($appointmentRequest->customer_email) !== $normalizedEmail) {
            throw ValidationException::withMessages([
                'customer_email' => __('capell-bookings::validation.portal_account_email_mismatch'),
            ]);
        }

        if ($portalAccount->email_hash !== PortalAccount::emailHash($normalizedEmail)) {
            throw ValidationException::withMessages([
                'portal_account_id' => __('capell-bookings::validation.portal_account_email_mismatch'),
            ]);
        }
    }

    private function verifiedUserEmail(Authenticatable $user): ?string
    {
        if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
            return null;
        }

        if (! $user instanceof MustVerifyEmail && $this->userAttribute($user, 'email_verified_at') === null) {
            return null;
        }

        $email = $this->userAttribute($user, 'email');

        return is_string($email) ? $email : null;
    }

    private function userAttribute(Authenticatable $user, string $attribute): mixed
    {
        if (method_exists($user, 'getAttribute')) {
            return $user->getAttribute($attribute);
        }

        return null;
    }
}
