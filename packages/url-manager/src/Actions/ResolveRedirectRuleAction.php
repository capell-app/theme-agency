<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\RedirectResolutionData;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use Capell\UrlManager\Models\RedirectRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Application;
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
        ?int $statusCode = null,
    ): ?RedirectResolutionData {
        $sourceUrl = NormalizeManagedUrlAction::run($requestUrl);
        $redirectRule = $this->findExactRule($sourceUrl, $siteId, $languageId, $statusCode)
            ?? $this->findPrefixRule($sourceUrl, $siteId, $languageId, $statusCode)
            ?? $this->findRegexRule($sourceUrl, $siteId, $languageId, $statusCode);

        if (! $redirectRule instanceof RedirectRule) {
            return null;
        }

        if ($recordHit) {
            $this->recordHit($redirectRule, $sourceUrl, $refererUrl, $userAgent, $ipAddress);
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

    private function findExactRule(string $sourceUrl, ?int $siteId, ?int $languageId, ?int $statusCode): ?RedirectRule
    {
        /** @var RedirectRule|null $redirectRule */
        $redirectRule = $this->baseRuleQuery($siteId, $languageId, $statusCode)
            ->where('match_type', RedirectMatchType::Exact->value)
            ->where('source_hash', hash('sha256', $sourceUrl))
            ->first();

        return $redirectRule;
    }

    private function findPrefixRule(string $sourceUrl, ?int $siteId, ?int $languageId, ?int $statusCode): ?RedirectRule
    {
        $prefixCandidates = $this->prefixCandidates($sourceUrl);

        if ($prefixCandidates === []) {
            return null;
        }

        /** @var RedirectRule|null $redirectRule */
        $redirectRule = $this->baseRuleQuery($siteId, $languageId, $statusCode)
            ->where('match_type', RedirectMatchType::Prefix->value)
            ->whereIn('source_url', $prefixCandidates)
            ->orderByDesc('priority')
            ->orderByRaw('LENGTH(source_url) DESC')
            ->first();

        return $redirectRule;
    }

    private function findRegexRule(string $sourceUrl, ?int $siteId, ?int $languageId, ?int $statusCode): ?RedirectRule
    {
        /** @var RedirectRule|null $redirectRule */
        $redirectRule = $this->baseRuleQuery($siteId, $languageId, $statusCode)
            ->where('match_type', RedirectMatchType::Regex->value)
            ->orderByDesc('priority')
            ->orderBy('id')
            ->limit(max(1, (int) config('capell-url-manager.redirects.regex.max_rules_checked', 100)))
            ->get()
            ->first(fn (RedirectRule $candidateRule): bool => @preg_match($candidateRule->source_url, $sourceUrl) === 1);

        return $redirectRule;
    }

    /**
     * @return Builder<RedirectRule>
     */
    private function baseRuleQuery(?int $siteId, ?int $languageId, ?int $statusCode): Builder
    {
        return RedirectRule::query()
            ->where('status', RedirectRuleStatus::Active->value)
            ->when($statusCode !== null, fn (Builder $query): Builder => $query->where('status_code', $statusCode))
            ->where(function (Builder $query) use ($siteId): void {
                $query->whereNull('site_id')
                    ->when($siteId !== null, fn (Builder $query): Builder => $query->orWhere('site_id', $siteId));
            })
            ->where(function (Builder $query) use ($languageId): void {
                $query->whereNull('language_id')
                    ->when($languageId !== null, fn (Builder $query): Builder => $query->orWhere('language_id', $languageId));
            })
            ->orderByRaw('site_id IS NULL')
            ->orderByRaw('language_id IS NULL')
            ->orderByDesc('priority');
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

    private function recordHit(
        RedirectRule $redirectRule,
        string $sourceUrl,
        ?string $refererUrl,
        ?string $userAgent,
        ?string $ipAddress,
    ): void {
        $record = static function () use ($redirectRule, $sourceUrl, $refererUrl, $userAgent, $ipAddress): void {
            RecordRedirectHitAction::run($redirectRule, $sourceUrl, $refererUrl, $userAgent, $ipAddress);
        };

        if ((bool) config('capell-url-manager.hit_recording.defer', true) && app() instanceof Application) {
            app()->terminating($record);

            return;
        }

        $record();
        $redirectRule->refresh();
    }

    /**
     * @return list<string>
     */
    private function prefixCandidates(string $sourceUrl): array
    {
        $segments = array_values(array_filter(explode('/', trim($sourceUrl, '/')), static fn (string $segment): bool => $segment !== ''));
        $candidates = ['/'];
        $current = '';

        foreach ($segments as $segment) {
            $current .= '/' . $segment;
            $candidates[] = $current;
        }

        return array_reverse($candidates);
    }
}
