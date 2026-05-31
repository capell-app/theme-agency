<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Data\PolicyAcceptanceData;
use Capell\PrivacyCenter\Models\PolicyAcceptance;
use Capell\PrivacyCenter\Support\PrivacyIdentifier;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordPolicyAcceptanceAction
{
    use AsAction;

    public function handle(PolicyAcceptanceData $acceptanceData, ?Model $subject = null): PolicyAcceptance
    {
        return PolicyAcceptance::query()->create([
            'site_id' => $acceptanceData->siteId,
            'subject_type' => PrivacyIdentifier::morphType($subject),
            'subject_id' => $subject?->getKey(),
            'policy_id' => $acceptanceData->policyId,
            'policy_type' => $acceptanceData->policyType,
            'policy_key' => $acceptanceData->policyKey,
            'policy_version' => $acceptanceData->policyVersion,
            'context' => $acceptanceData->context,
            'ip_hash' => PrivacyIdentifier::hashNullable(request()->ip()),
            'user_agent_hash' => PrivacyIdentifier::hashNullable(request()->userAgent()),
            'accepted_at' => $acceptanceData->acceptedAt ?? now(),
            'metadata' => $acceptanceData->metadata === [] ? null : $acceptanceData->metadata,
        ]);
    }
}
