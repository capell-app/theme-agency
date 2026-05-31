<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Data\ConsentPolicyData;
use Capell\PrivacyCenter\Models\ConsentPolicy;
use Lorisleiva\Actions\Concerns\AsAction;

final class RegisterConsentPolicyAction
{
    use AsAction;

    public function handle(ConsentPolicyData $policyData): ConsentPolicy
    {
        return ConsentPolicy::query()->updateOrCreate([
            'site_id' => $policyData->siteId,
            'key' => $policyData->key,
            'version' => $policyData->version,
        ], [
            'type' => $policyData->type,
            'title' => $policyData->title,
            'content_hash' => $policyData->contentHash,
            'effective_at' => $policyData->effectiveAt,
            'published_at' => $policyData->publishedAt,
            'retired_at' => $policyData->retiredAt,
            'metadata' => $policyData->metadata === [] ? null : $policyData->metadata,
        ]);
    }
}
