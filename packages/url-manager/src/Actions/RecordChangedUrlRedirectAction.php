<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\ChangedUrlRedirectData;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Models\RedirectRule;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static RedirectRule|null run(ChangedUrlRedirectData $data)
 */
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

        $redirectRule = UpsertRedirectRuleAction::run(new RedirectRuleData(
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

        $this->recordChildPrefixRedirects($previousUrl, $currentUrl, $data);

        return $redirectRule;
    }

    private function recordChildPrefixRedirects(string $previousUrl, string $currentUrl, ChangedUrlRedirectData $data): void
    {
        if (! (bool) config('capell-url-manager.redirects.create_prefix_redirect_on_parent_move', true)) {
            return;
        }

        if (! $this->looksLikeParentMove($previousUrl, $currentUrl)) {
            return;
        }

        UpsertRedirectRuleAction::run(new RedirectRuleData(
            sourceUrl: $previousUrl . '/',
            targetUrl: $currentUrl . '/',
            siteId: $data->siteId,
            languageId: $data->languageId,
            statusCode: $data->statusCode,
            matchType: RedirectMatchType::Prefix,
            status: $data->status,
            preserveQuery: $data->preserveQuery,
            notes: $data->notes,
            createdByUserId: $data->createdByUserId,
        ));
    }

    private function looksLikeParentMove(string $previousUrl, string $currentUrl): bool
    {
        $previousSegments = $this->segments($previousUrl);
        $currentSegments = $this->segments($currentUrl);

        return count($previousSegments) === count($currentSegments)
            && $previousSegments !== []
            && Collection::make($previousSegments)->slice(0, -1)->values()->all() === Collection::make($currentSegments)->slice(0, -1)->values()->all();
    }

    /**
     * @return list<string>
     */
    private function segments(string $url): array
    {
        return array_values(array_filter(explode('/', trim($url, '/')), static fn (string $segment): bool => $segment !== ''));
    }
}
