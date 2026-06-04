<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Models\RedirectRule;
use Lorisleiva\Actions\Concerns\AsAction;

final class UpdateRedirectRuleAction
{
    use AsAction;

    public function handle(RedirectRule $redirectRule, RedirectRuleData $data): RedirectRule
    {
        $prepared = PrepareRedirectRuleDataAction::run($data, (int) $redirectRule->getKey());

        $redirectRule->forceFill([
            'site_id' => $data->siteId,
            'language_id' => $data->languageId,
            'source_url' => $prepared->sourceUrl,
            'source_hash' => $this->hash($prepared->sourceUrl),
            'target_url' => $prepared->targetUrl,
            'target_hash' => $this->hash($prepared->targetUrl),
            'status_code' => $data->statusCode,
            'match_type' => $data->matchType->value,
            'status' => $data->status->value,
            'priority' => $prepared->priority,
            'preserve_query' => $data->preserveQuery,
            'notes' => $data->notes,
        ])->save();

        return $redirectRule;
    }

    private function hash(string $url): string
    {
        return hash('sha256', $url);
    }
}
