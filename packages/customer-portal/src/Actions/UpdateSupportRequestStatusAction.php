<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Events\PortalSupportRequestStatusChanged;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Capell\CustomerPortal\Notifications\SupportRequestStatusChangedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Notification;
use Lorisleiva\Actions\Concerns\AsAction;

final class UpdateSupportRequestStatusAction
{
    use AsAction;

    public function handle(PortalSupportRequest $supportRequest, SupportRequestStatus $status): PortalSupportRequest
    {
        $now = CarbonImmutable::now();
        $previousStatus = $supportRequest->status;

        $supportRequest->forceFill([
            'status' => $status,
            'resolved_at' => $status === SupportRequestStatus::Resolved ? ($supportRequest->resolved_at ?? $now) : null,
            'closed_at' => $status === SupportRequestStatus::Closed ? ($supportRequest->closed_at ?? $now) : null,
        ]);

        $supportRequest->save();

        $freshSupportRequest = $supportRequest->refresh();

        if ($previousStatus !== $freshSupportRequest->status) {
            event(new PortalSupportRequestStatusChanged($freshSupportRequest, $previousStatus, $freshSupportRequest->status));
            $this->notifyRequester($freshSupportRequest, $previousStatus);
        }

        return $freshSupportRequest;
    }

    private function notifyRequester(PortalSupportRequest $supportRequest, SupportRequestStatus $previousStatus): void
    {
        if (! is_string($supportRequest->requester_email) || trim($supportRequest->requester_email) === '') {
            return;
        }

        Notification::route('mail', $supportRequest->requester_email)
            ->notify(new SupportRequestStatusChangedNotification($supportRequest, $previousStatus));
    }
}
