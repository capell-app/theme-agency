<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Enums\PrivacyRequestStatus;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Carbon\CarbonInterface;
use Lorisleiva\Actions\Concerns\AsAction;

final class MarkPrivacyRequestVerifiedAction
{
    use AsAction;

    public function handle(
        PrivacyRequest $privacyRequest,
        ?CarbonInterface $verifiedAt = null,
    ): PrivacyRequest {
        $privacyRequest->forceFill([
            'status' => PrivacyRequestStatus::Processing,
            'verified_at' => $verifiedAt ?? now(),
        ])->save();

        return $privacyRequest->refresh();
    }
}
