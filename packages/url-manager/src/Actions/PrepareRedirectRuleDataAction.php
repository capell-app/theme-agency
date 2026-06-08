<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use Capell\UrlManager\Data\PreparedRedirectRuleData;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use Capell\UrlManager\Models\RedirectRule;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PreparedRedirectRuleData run(RedirectRuleData $data, ?int $ignoredRuleId = null)
 */
final class PrepareRedirectRuleDataAction
{
    use AsAction;

    public function handle(RedirectRuleData $data, ?int $ignoredRuleId = null): PreparedRedirectRuleData
    {
        $sourceUrl = $data->matchType === RedirectMatchType::Regex
            ? $this->normalizeRegexSource($data->sourceUrl)
            : NormalizeManagedUrlAction::run($data->sourceUrl);

        $this->assertAllowedStatusCode($data->statusCode);

        if ($data->statusCode === 410) {
            return new PreparedRedirectRuleData(
                sourceUrl: $sourceUrl,
                targetUrl: '',
                priority: $this->boundedPriority($data->priority),
            );
        }

        $targetUrl = NormalizeManagedUrlAction::run($data->targetUrl, allowAbsolute: true);
        $this->assertAllowedTargetUrl($targetUrl);
        $this->assertNotSelfRedirect($data->matchType, $sourceUrl, $targetUrl);
        $targetUrl = $this->resolveFinalTargetUrl($sourceUrl, $targetUrl, $data, $ignoredRuleId);
        $this->assertAllowedTargetUrl($targetUrl);
        $this->assertNotSelfRedirect($data->matchType, $sourceUrl, $targetUrl);

        return new PreparedRedirectRuleData(
            sourceUrl: $sourceUrl,
            targetUrl: $targetUrl,
            priority: $this->boundedPriority($data->priority),
        );
    }

    public function normalizeRegexSource(string $sourceUrl): string
    {
        $sourceUrl = trim($sourceUrl);
        $maxLength = max(1, (int) config('capell-url-manager.redirects.regex.max_pattern_length', 512));

        throw_if($sourceUrl === '', InvalidArgumentException::class, __('capell-url-manager::validation.regex_required'));
        throw_if(mb_strlen($sourceUrl) > $maxLength, InvalidArgumentException::class, __('capell-url-manager::validation.regex_too_long', ['max' => $maxLength]));
        throw_if(@preg_match($sourceUrl, '') === false, InvalidArgumentException::class, __('capell-url-manager::validation.regex_invalid'));

        return $sourceUrl;
    }

    private function assertAllowedStatusCode(int $statusCode): void
    {
        $allowedStatusCodes = $this->allowedStatusCodes();

        throw_unless(in_array($statusCode, $allowedStatusCodes, true), InvalidArgumentException::class, __('capell-url-manager::validation.status_code_invalid'));
    }

    private function assertAllowedTargetUrl(string $targetUrl): void
    {
        $host = parse_url($targetUrl, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return;
        }

        throw_unless(in_array(strtolower($host), $this->allowedAbsoluteTargetHosts(), true), InvalidArgumentException::class, __('capell-url-manager::validation.absolute_target_host_not_allowed', ['host' => $host]));
    }

    private function assertNotSelfRedirect(RedirectMatchType $matchType, string $sourceUrl, string $targetUrl): void
    {
        throw_if($matchType !== RedirectMatchType::Regex && $sourceUrl === $targetUrl, InvalidArgumentException::class, __('capell-url-manager::validation.self_redirect'));
    }

    private function resolveFinalTargetUrl(string $sourceUrl, string $targetUrl, RedirectRuleData $data, ?int $ignoredRuleId): string
    {
        if ($data->matchType !== RedirectMatchType::Exact || parse_url($targetUrl, PHP_URL_HOST) !== null) {
            return $targetUrl;
        }

        $visitedUrls = [$sourceUrl];
        $currentUrl = $targetUrl;
        $maxDepth = max(1, (int) config('capell-url-manager.redirects.max_chain_depth', 10));

        for ($depth = 0; $depth < $maxDepth; $depth++) {
            if (in_array($currentUrl, $visitedUrls, true)) {
                throw new InvalidArgumentException((string) __('capell-url-manager::validation.redirect_loop'));
            }

            $visitedUrls[] = $currentUrl;

            $nextRule = $this->findActiveExactRule($currentUrl, $data, $ignoredRuleId);

            if (! $nextRule instanceof RedirectRule || parse_url($nextRule->target_url, PHP_URL_HOST) !== null) {
                return $nextRule instanceof RedirectRule ? $nextRule->target_url : $currentUrl;
            }

            $currentUrl = $nextRule->target_url;
        }

        throw new InvalidArgumentException((string) __('capell-url-manager::validation.redirect_loop'));
    }

    private function findActiveExactRule(string $sourceUrl, RedirectRuleData $data, ?int $ignoredRuleId): ?RedirectRule
    {
        /** @var RedirectRule|null $redirectRule */
        $redirectRule = RedirectRule::query()
            ->when($ignoredRuleId !== null, fn (Builder $query): Builder => $query->whereKeyNot($ignoredRuleId))
            ->where('status', RedirectRuleStatus::Active->value)
            ->where('match_type', RedirectMatchType::Exact->value)
            ->where('source_hash', $this->hash($sourceUrl))
            ->where(function (Builder $query) use ($data): void {
                $query->whereNull('site_id')
                    ->when($data->siteId !== null, fn (Builder $query): Builder => $query->orWhere('site_id', $data->siteId));
            })
            ->where(function (Builder $query) use ($data): void {
                $query->whereNull('language_id')
                    ->when($data->languageId !== null, fn (Builder $query): Builder => $query->orWhere('language_id', $data->languageId));
            })
            ->first();

        return $redirectRule;
    }

    /**
     * @return list<string>
     */
    private function allowedAbsoluteTargetHosts(): array
    {
        $configuredHosts = [];
        $configuredHostValues = config('capell-url-manager.redirects.absolute_target_allowed_hosts', []);

        if (is_array($configuredHostValues)) {
            foreach ($configuredHostValues as $host) {
                if (is_string($host) && trim($host) !== '') {
                    $configuredHosts[] = strtolower(trim($host));
                }
            }
        }

        if ((bool) config('capell-url-manager.redirects.allow_app_url_host', true)) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

            if (is_string($appHost) && $appHost !== '') {
                $configuredHosts[] = strtolower($appHost);
            }
        }

        return array_values(array_unique($configuredHosts));
    }

    /**
     * @return list<int>
     */
    private function allowedStatusCodes(): array
    {
        $configuredStatusCodes = config('capell-url-manager.redirects.allowed_status_codes', [301, 302, 307, 308, 410]);

        if (! is_array($configuredStatusCodes)) {
            return [301, 302, 307, 308, 410];
        }

        $statusCodes = [];

        foreach ($configuredStatusCodes as $statusCode) {
            if (is_numeric($statusCode)) {
                $statusCodes[] = (int) $statusCode;
            }
        }

        return $statusCodes === [] ? [301, 302, 307, 308, 410] : array_values(array_unique($statusCodes));
    }

    private function boundedPriority(int $priority): int
    {
        return max(-1000, min(1000, $priority));
    }

    private function hash(string $url): string
    {
        return hash('sha256', $url);
    }
}
