<?php

declare(strict_types=1);

namespace Capell\UrlManager\Support\Redirects;

use Capell\Core\Contracts\RedirectResolver;
use Capell\Core\Data\RedirectDecisionData;
use Capell\Core\Models\Language;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\UrlManager\Actions\ResolveRedirectRuleAction;
use Capell\UrlManager\Data\RedirectResolutionData;
use Illuminate\Http\Request;

final class UrlManagerRedirectResolver implements RedirectResolver
{
    public function __construct(
        private readonly ?RedirectResolver $fallbackResolver = null,
    ) {}

    public function resolve(Site $site, Language $language, string $url, ?int $pageId = null, ?PageUrl $pageUrl = null): ?RedirectDecisionData
    {
        $fallbackDecision = $this->fallbackResolver?->resolve($site, $language, $url, $pageId, $pageUrl);

        if ($fallbackDecision instanceof RedirectDecisionData) {
            return $fallbackDecision;
        }

        if ($pageUrl instanceof PageUrl && ! $pageUrl->isRedirect()) {
            return null;
        }

        $resolution = ResolveRedirectRuleAction::run(
            requestUrl: $url,
            siteId: (int) $site->getKey(),
            languageId: (int) $language->getKey(),
        );

        if (! $resolution instanceof RedirectResolutionData) {
            return null;
        }

        if ($resolution->statusCode === 410) {
            return null;
        }

        return new RedirectDecisionData(
            targetUrl: $this->targetUrl($resolution),
            statusCode: $resolution->statusCode,
        );
    }

    private function targetUrl(RedirectResolutionData $resolution): string
    {
        if (! $resolution->preserveQuery) {
            return $resolution->targetUrl;
        }

        $request = request();

        if (! $request instanceof Request) {
            return $resolution->targetUrl;
        }

        $rawQuery = $request->server->get('QUERY_STRING');

        if (! is_string($rawQuery) || $rawQuery === '' || str_contains($resolution->targetUrl, '?')) {
            return $resolution->targetUrl;
        }

        return $resolution->targetUrl . '?' . $rawQuery;
    }
}
