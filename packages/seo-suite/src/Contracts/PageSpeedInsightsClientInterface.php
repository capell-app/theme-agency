<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Contracts;

use Capell\SeoSuite\Data\PageSpeedAuditResultData;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;

interface PageSpeedInsightsClientInterface
{
    public function isConfigured(): bool;

    public function analyze(string $url, PageSpeedStrategyEnum $strategy): PageSpeedAuditResultData;
}
