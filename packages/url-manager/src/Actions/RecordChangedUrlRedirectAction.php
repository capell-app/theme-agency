<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\ChangedUrlRedirectData;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Models\RedirectRule;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordChangedUrlRedirectAction
{
    use AsAction;

    public function handle(ChangedUrlRedirectData $data): ?RedirectRule
    {
        $previousUrl = NormalizeManagedUrlAction::run($data->previousUrl);
        $currentUrl = NormalizeManagedUrlAction::run($data->currentUrl);

        if ($previousUrl === $currentUrl) {
            return null;
        }

        return UpsertRedirectRuleAction::run(new RedirectRuleData(
            sourceUrl: $previousUrl,
            targetUrl: $currentUrl,
            siteId: $data->siteId,
            languageId: $data->languageId,
            statusCode: $data->statusCode,
            status: $data->status,
            preserveQuery: $data->preserveQuery,
            notes: $data->notes,
            createdByUserId: $data->createdByUserId,
        ));
    }
}
