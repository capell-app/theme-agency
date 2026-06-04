<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Data\SupportRequestData;
use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Events\PortalSupportRequestSubmitted;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Capell\CustomerPortal\Notifications\SupportRequestSubmittedNotification;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PortalSupportRequest run(PortalAccount $portalAccount, SupportRequestData $supportRequestData)
 */
class SubmitSupportRequestAction
{
    use AsAction;

    public function handle(PortalAccount $portalAccount, SupportRequestData $supportRequestData): PortalSupportRequest
    {
        $subject = trim($supportRequestData->subject);
        $message = trim($supportRequestData->message);

        if ($subject === '') {
            throw ValidationException::withMessages([
                'subject' => __('capell-customer-portal::validation.support_subject_required'),
            ]);
        }

        if ($message === '') {
            throw ValidationException::withMessages([
                'message' => __('capell-customer-portal::validation.support_message_required'),
            ]);
        }

        $supportRequest = DB::transaction(function () use ($portalAccount, $supportRequestData, $subject, $message): PortalSupportRequest {
            /** @var PortalSupportRequest $supportRequest */
            $supportRequest = PortalSupportRequest::query()->create([
                'site_id' => $portalAccount->site_id,
                'portal_account_id' => $portalAccount->getKey(),
                'status' => SupportRequestStatus::Open,
                'priority' => $supportRequestData->priority,
                'subject' => $subject,
                'message' => $message,
                'requester_email' => $supportRequestData->requesterEmail ?? $portalAccount->email,
                'source' => $supportRequestData->source,
                'external_reference' => $supportRequestData->externalReference,
                'context' => $supportRequestData->context,
                'submitted_at' => CarbonImmutable::now(),
            ]);

            return $supportRequest;
        });

        event(new PortalSupportRequestSubmitted($supportRequest));
        $this->notifyRequester($supportRequest);

        return $supportRequest;
    }

    private function notifyRequester(PortalSupportRequest $supportRequest): void
    {
        if (! is_string($supportRequest->requester_email) || trim($supportRequest->requester_email) === '') {
            return;
        }

        Notification::route('mail', $supportRequest->requester_email)
            ->notify(new SupportRequestSubmittedNotification($supportRequest));
    }
}
