<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Data\ConsentRecordData;
use Capell\PrivacyCenter\Models\ConsentRecord;
use Capell\PrivacyCenter\Support\PrivacyIdentifier;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordConsentAction
{
    use AsAction;

    public function handle(
        ConsentRecordData $consentData,
        ?Model $subject = null,
        ?Model $source = null,
    ): ConsentRecord {
        $resolvedSubject = $subject ?? $this->subjectFromSource($source);

        return ConsentRecord::query()->create([
            'site_id' => $consentData->siteId,
            'subject_type' => PrivacyIdentifier::morphType($resolvedSubject),
            'subject_id' => $resolvedSubject?->getKey(),
            'source_type' => PrivacyIdentifier::morphType($source),
            'source_id' => $source?->getKey(),
            'policy_id' => $consentData->policyId,
            'policy_version' => $consentData->policyVersion,
            'category' => $consentData->category,
            'decision' => $consentData->decision,
            'jurisdiction' => $consentData->jurisdiction,
            'ip_hash' => PrivacyIdentifier::hashNullable(request()->ip()),
            'user_agent_hash' => PrivacyIdentifier::hashNullable(request()->userAgent()),
            'evidence' => $consentData->evidence === [] ? null : $consentData->evidence,
            'decided_at' => $consentData->decidedAt ?? now(),
            'expires_at' => $consentData->expiresAt,
            'revoked_at' => $consentData->revokedAt,
            'metadata' => $consentData->metadata === [] ? null : $consentData->metadata,
        ]);
    }

    private function subjectFromSource(?Model $source): ?Model
    {
        if (! $source instanceof Model) {
            return null;
        }

        foreach (['subject', 'visit'] as $relationName) {
            if (! $source->relationLoaded($relationName)) {
                continue;
            }

            $relatedModel = $source->getRelation($relationName);

            if ($relatedModel instanceof Model) {
                return $relatedModel;
            }
        }

        return null;
    }
}
