<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Enums\PrivacyRequestStatus;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Carbon\CarbonInterface;
use Lorisleiva\Actions\Concerns\AsAction;

final class RejectPrivacyRequestAction
{
    use AsAction;

    public function handle(
        PrivacyRequest $privacyRequest,
        string $reason,
        ?CarbonInterface $rejectedAt = null,
    ): PrivacyRequest {
        $privacyRequest->forceFill([
            'status' => PrivacyRequestStatus::Rejected,
            'rejected_at' => $rejectedAt ?? now(),
            'rejection_reason' => $reason,
        ])->save();

        return $privacyRequest->refresh();
    }
}
