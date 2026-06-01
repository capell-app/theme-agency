<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Data\SupportRequestData;
use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
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

        return DB::transaction(function () use ($portalAccount, $supportRequestData, $subject, $message): PortalSupportRequest {
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
    }
}
