<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\RedirectResolutionData;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use Capell\UrlManager\Models\RedirectRule;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class ResolveRedirectRuleAction
{
    use AsAction;

    public function handle(
        string $requestUrl,
        ?int $siteId = null,
        ?int $languageId = null,
        bool $recordHit = true,
        ?string $refererUrl = null,
        ?string $userAgent = null,
        ?string $ipAddress = null,
    ): ?RedirectResolutionData {
        $sourceUrl = NormalizeManagedUrlAction::run($requestUrl);
        $redirectRule = $this->findExactRule($sourceUrl, $siteId, $languageId)
            ?? $this->findPrefixRule($sourceUrl, $siteId, $languageId)
            ?? $this->findRegexRule($sourceUrl, $siteId, $languageId);

        if (! $redirectRule instanceof RedirectRule) {
            return null;
        }

        if ($recordHit) {
            RecordRedirectHitAction::run($redirectRule, $sourceUrl, $refererUrl, $userAgent, $ipAddress);
            $redirectRule->refresh();
        }

        return new RedirectResolutionData(
            redirectRuleId: (int) $redirectRule->getKey(),
            sourceUrl: $redirectRule->source_url,
            targetUrl: $this->targetUrl($redirectRule, $sourceUrl),
            statusCode: $redirectRule->status_code,
            matchType: $redirectRule->match_type,
            preserveQuery: $redirectRule->preserve_query,
        );
    }

    private function findExactRule(string $sourceUrl, ?int $siteId, ?int $languageId): ?RedirectRule
    {
        /** @var RedirectRule|null $redirectRule */
        $redirectRule = $this->baseRuleQuery($siteId, $languageId)
            ->where('match_type', RedirectMatchType::Exact->value)
            ->where('source_hash', hash('sha256', $sourceUrl))
            ->first();

        return $redirectRule;
    }

    private function findPrefixRule(string $sourceUrl, ?int $siteId, ?int $languageId): ?RedirectRule
    {
        /** @var RedirectRule|null $redirectRule */
        $redirectRule = $this->baseRuleQuery($siteId, $languageId)
            ->where('match_type', RedirectMatchType::Prefix->value)
            ->orderByRaw('LENGTH(source_url) DESC')
            ->get()
            ->first(fn (RedirectRule $candidateRule): bool => str_starts_with($sourceUrl, $candidateRule->source_url));

        return $redirectRule;
    }

    private function findRegexRule(string $sourceUrl, ?int $siteId, ?int $languageId): ?RedirectRule
    {
        /** @var RedirectRule|null $redirectRule */
        $redirectRule = $this->baseRuleQuery($siteId, $languageId)
            ->where('match_type', RedirectMatchType::Regex->value)
            ->get()
            ->first(fn (RedirectRule $candidateRule): bool => @preg_match($candidateRule->source_url, $sourceUrl) === 1);

        return $redirectRule;
    }

    /**
     * @return Builder<RedirectRule>
     */
    private function baseRuleQuery(?int $siteId, ?int $languageId): Builder
    {
        return RedirectRule::query()
            ->where('status', RedirectRuleStatus::Active->value)
            ->where(function (Builder $query) use ($siteId): void {
                $query->whereNull('site_id')
                    ->when($siteId !== null, fn (Builder $query): Builder => $query->orWhere('site_id', $siteId));
            })
            ->where(function (Builder $query) use ($languageId): void {
                $query->whereNull('language_id')
                    ->when($languageId !== null, fn (Builder $query): Builder => $query->orWhere('language_id', $languageId));
            })
            ->orderByRaw('site_id IS NULL')
            ->orderByRaw('language_id IS NULL');
    }

    private function targetUrl(RedirectRule $redirectRule, string $sourceUrl): string
    {
        if ($redirectRule->match_type === RedirectMatchType::Prefix) {
            return $redirectRule->target_url . mb_substr($sourceUrl, mb_strlen($redirectRule->source_url));
        }

        if ($redirectRule->match_type === RedirectMatchType::Regex) {
            $resolvedTargetUrl = @preg_replace($redirectRule->source_url, $redirectRule->target_url, $sourceUrl, 1);

            return is_string($resolvedTargetUrl) ? $resolvedTargetUrl : $redirectRule->target_url;
        }

        return $redirectRule->target_url;
    }
}
