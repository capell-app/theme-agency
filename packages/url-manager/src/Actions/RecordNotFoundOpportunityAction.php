<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\NotFoundOpportunityData;
use Capell\UrlManager\Enums\NotFoundOpportunityStatus;
use Capell\UrlManager\Models\NotFoundOpportunity;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordNotFoundOpportunityAction
{
    use AsAction;

    public function handle(NotFoundOpportunityData $data): NotFoundOpportunity
    {
        $sourceUrl = NormalizeManagedUrlAction::run($data->sourceUrl);
        $now = now();

        /** @var NotFoundOpportunity $opportunity */
        $opportunity = NotFoundOpportunity::query()->firstOrNew([
            'site_id' => $data->siteId,
            'language_id' => $data->languageId,
            'source_hash' => hash('sha256', (string) $sourceUrl),
        ]);

        $opportunity->fill([
            'source_url' => $sourceUrl,
            'hit_count' => $opportunity->exists ? $opportunity->hit_count + 1 : 1,
            'first_seen_at' => $opportunity->first_seen_at ?? $now,
            'last_seen_at' => $now,
            'suggested_target_url' => $data->suggestedTargetUrl,
            'status' => $opportunity->status ?? NotFoundOpportunityStatus::Open,
            'context' => $data->context,
        ])->save();

        return $opportunity;
    }
}
