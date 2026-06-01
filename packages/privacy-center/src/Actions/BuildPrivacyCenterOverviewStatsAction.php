<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Actions;

use Capell\PrivacyCenter\Enums\ConsentDecision;
use Capell\PrivacyCenter\Enums\PrivacyRequestStatus;
use Capell\PrivacyCenter\Models\ConsentRecord;
use Capell\PrivacyCenter\Models\PrivacyRequest;
use Capell\PrivacyCenter\Models\RetentionRule;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildPrivacyCenterOverviewStatsAction
{
    use AsAction;

    /**
     * @return array{consent_records: int, granted_consents: int, open_privacy_requests: int, active_retention_rules: int}
     */
    public function handle(): array
    {
        return [
            'consent_records' => ConsentRecord::query()->count(),
            'granted_consents' => ConsentRecord::query()->where('decision', ConsentDecision::Granted->value)->count(),
            'open_privacy_requests' => PrivacyRequest::query()
                ->whereIn('status', [
                    PrivacyRequestStatus::Submitted->value,
                    PrivacyRequestStatus::Verifying->value,
                    PrivacyRequestStatus::Processing->value,
                ])
                ->count(),
            'active_retention_rules' => RetentionRule::query()->where('is_active', true)->count(),
        ];
    }
}
