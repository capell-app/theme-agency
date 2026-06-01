<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Enums\PrivacyRequestStatus;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Carbon\CarbonInterface;
use Lorisleiva\Actions\Concerns\AsAction;

final class MarkPrivacyRequestFulfilledAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $workflowPayload
     */
    public function handle(
        PrivacyRequest $privacyRequest,
        array $workflowPayload = [],
        ?CarbonInterface $fulfilledAt = null,
    ): PrivacyRequest {
        $privacyRequest->forceFill([
            'status' => PrivacyRequestStatus::Fulfilled,
            'fulfilled_at' => $fulfilledAt ?? now(),
            'workflow_payload' => $workflowPayload === [] ? $privacyRequest->workflow_payload : $workflowPayload,
        ])->save();

        return $privacyRequest->refresh();
    }
}
