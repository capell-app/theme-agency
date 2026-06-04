<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Models\RedirectRule;
use Lorisleiva\Actions\Concerns\AsAction;

final class UpsertRedirectRuleAction
{
    use AsAction;

    public function handle(RedirectRuleData $data): RedirectRule
    {
        $prepared = PrepareRedirectRuleDataAction::run($data);

        /** @var RedirectRule $redirectRule */
        $redirectRule = RedirectRule::query()->updateOrCreate(
            [
                'site_id' => $data->siteId,
                'language_id' => $data->languageId,
                'source_hash' => $this->hash($prepared->sourceUrl),
                'match_type' => $data->matchType->value,
            ],
            [
                'source_url' => $prepared->sourceUrl,
                'target_url' => $prepared->targetUrl,
                'target_hash' => $this->hash($prepared->targetUrl),
                'status_code' => $data->statusCode,
                'status' => $data->status->value,
                'priority' => $prepared->priority,
                'preserve_query' => $data->preserveQuery,
                'notes' => $data->notes,
                'created_by_user_id' => $data->createdByUserId,
            ],
        );

        return $redirectRule;
    }

    private function hash(string $url): string
    {
        return hash('sha256', $url);
    }
}
