<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Enums\SupportRequestStatus;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class UpdateSupportRequestStatusAction
{
    use AsAction;

    public function handle(PortalSupportRequest $supportRequest, SupportRequestStatus $status): PortalSupportRequest
    {
        $now = CarbonImmutable::now();

        $supportRequest->forceFill([
            'status' => $status,
            'resolved_at' => $status === SupportRequestStatus::Resolved ? ($supportRequest->resolved_at ?? $now) : null,
            'closed_at' => $status === SupportRequestStatus::Closed ? ($supportRequest->closed_at ?? $now) : null,
        ]);

        $supportRequest->save();

        return $supportRequest->refresh();
    }
}
