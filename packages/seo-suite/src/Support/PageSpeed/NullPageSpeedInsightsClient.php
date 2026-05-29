<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support\PageSpeed;

use Capell\SeoSuite\Contracts\PageSpeedInsightsClientInterface;
use Capell\SeoSuite\Data\PageSpeedAuditResultData;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;

final class NullPageSpeedInsightsClient implements PageSpeedInsightsClientInterface
{
    public function isConfigured(): bool
    {
        return false;
    }

    public function analyze(string $url, PageSpeedStrategyEnum $strategy): PageSpeedAuditResultData
    {
        return new PageSpeedAuditResultData(
            strategy: $strategy,
            url: $url,
            successful: false,
            errorMessage: __('capell-seo-suite::generic.pagespeed_not_configured'),
        );
    }
}
