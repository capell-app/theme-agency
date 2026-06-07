<?php

declare(strict_types=1);

namespace Capell\AccessGate\Actions;

use Capell\AccessGate\Enums\GrantStatus;
use Capell\AccessGate\Models\Grant;
use Capell\AccessGate\Models\Registration;
use Capell\AccessGate\Support\AccessGateDatabase;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class ResendAccessGateClaimTokenAction
{
    use AsAction;

    public function __construct(
        private readonly SendAccessGateApprovedNotificationAction $sendApprovedNotification,
    ) {}

    public function handle(Registration $registration): ?Grant
    {
        return AccessGateDatabase::transaction(function () use ($registration): ?Grant {
            $grant = $registration->grants()
                ->where('status', GrantStatus::Active->value)
                ->whereNull('revoked_at')
                ->where(function (Builder $query): void {
                    $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
                })
                ->where(function (Builder $query): void {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->latest('id')
                ->first();

            if (! $grant instanceof Grant) {
                return null;
            }

            $this->sendApprovedNotification->handle($registration, $grant);

            return $grant;
        });
    }
}
