<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\ConvertNotFoundOpportunityData;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Enums\NotFoundOpportunityStatus;
use Capell\UrlManager\Models\NotFoundOpportunity;
use Capell\UrlManager\Models\RedirectRule;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

final class ConvertNotFoundOpportunityToRedirectAction
{
    use AsAction;

    public function handle(ConvertNotFoundOpportunityData $data): RedirectRule
    {
        /** @var NotFoundOpportunity|null $opportunity */
        $opportunity = NotFoundOpportunity::query()->find($data->opportunityId);

        throw_unless(
            $opportunity instanceof NotFoundOpportunity,
            InvalidArgumentException::class,
            __('capell-url-manager::validation.not_found_opportunity_missing'),
        );

        $targetUrl = $data->targetUrl ?? $opportunity->suggested_target_url;

        throw_if(
            ! is_string($targetUrl) || trim($targetUrl) === '',
            InvalidArgumentException::class,
            __('capell-url-manager::validation.not_found_opportunity_target_required'),
        );

        $redirectRule = UpsertRedirectRuleAction::run(new RedirectRuleData(
            sourceUrl: $opportunity->source_url,
            targetUrl: $targetUrl,
            siteId: $opportunity->site_id,
            languageId: $opportunity->language_id,
            statusCode: $data->statusCode,
            status: $data->redirectStatus,
            preserveQuery: $data->preserveQuery,
            notes: $data->notes,
            createdByUserId: $data->createdByUserId,
        ));

        $opportunity->forceFill([
            'redirect_rule_id' => $redirectRule->getKey(),
            'suggested_target_url' => $targetUrl,
            'status' => NotFoundOpportunityStatus::Converted,
        ])->save();

        return $redirectRule;
    }
}
