<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Support\PageSpeed;

use Capell\SeoSuite\Contracts\PageSpeedInsightsClientInterface;
use Capell\SeoSuite\Data\PageSpeedAuditItemData;
use Capell\SeoSuite\Data\PageSpeedAuditResultData;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Carbon\CarbonImmutable;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Throwable;

final class GooglePageSpeedInsightsClient implements PageSpeedInsightsClientInterface
{
    private const string ENDPOINT = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';

    /** @var list<string> */
    private const array CATEGORIES = [
        'performance',
        'accessibility',
        'best-practices',
        'seo',
    ];

    /** @var list<string> */
    private const array METRIC_KEYS = [
        'largest-contentful-paint',
        'cumulative-layout-shift',
        'total-blocking-time',
        'speed-index',
        'interactive',
    ];

    /**
     * @param  array{enabled?: bool, api_key?: string|null, timeout?: int|null}  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    public function isConfigured(): bool
    {
        return ($this->config['enabled'] ?? false) === true
            && is_string($this->config['api_key'] ?? null)
            && trim((string) $this->config['api_key']) !== '';
    }

    public function analyze(string $url, PageSpeedStrategyEnum $strategy): PageSpeedAuditResultData
    {
        if (! $this->isConfigured()) {
            return new PageSpeedAuditResultData(
                strategy: $strategy,
                url: $url,
                successful: false,
                errorMessage: __('capell-seo-suite::generic.pagespeed_not_configured'),
            );
        }

        $response = $this->request($url, $strategy);

        if (! $response->successful()) {
            return new PageSpeedAuditResultData(
                strategy: $strategy,
                url: $url,
                successful: false,
                errorMessage: $this->errorMessage($response),
            );
        }

        /** @var array<string, mixed> $payload */
        $payload = $response->json();

        return new PageSpeedAuditResultData(
            strategy: $strategy,
            url: $url,
            successful: true,
            categoryScores: $this->categoryScores($payload),
            metrics: $this->metrics($payload),
            opportunities: $this->auditItems($payload, opportunities: true),
            diagnostics: $this->auditItems($payload, opportunities: false),
            lighthouseVersion: $this->stringValue(data_get($payload, 'lighthouseResult.lighthouseVersion')),
            fetchedAt: $this->fetchedAt($payload),
        );
    }

    private function request(string $url, PageSpeedStrategyEnum $strategy): Response
    {
        return Http::acceptJson()
            ->timeout((int) ($this->config['timeout'] ?? 90))
            ->connectTimeout(10)
            ->retry(2, 500)
            ->get($this->requestUrl($url, $strategy));
    }

    private function requestUrl(string $url, PageSpeedStrategyEnum $strategy): string
    {
        $query = http_build_query([
            'url' => $url,
            'strategy' => $strategy->value,
            'key' => trim((string) $this->config['api_key']),
        ]);

        foreach (self::CATEGORIES as $category) {
            $query .= '&category=' . rawurlencode($category);
        }

        return self::ENDPOINT . '?' . $query;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, int|null>
     */
    private function categoryScores(array $payload): array
    {
        $scores = [];

        foreach (self::CATEGORIES as $category) {
            $score = data_get($payload, 'lighthouseResult.categories.' . $category . '.score');
            $scores[$category] = is_numeric($score) ? (int) round(((float) $score) * 100) : null;
        }

        return $scores;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, array{display_value?: string|null, numeric_value?: float|null}>
     */
    private function metrics(array $payload): array
    {
        $metrics = [];

        foreach (self::METRIC_KEYS as $key) {
            $audit = data_get($payload, 'lighthouseResult.audits.' . $key);

            if (! is_array($audit)) {
                continue;
            }

            $numericValue = $audit['numericValue'] ?? null;

            $metrics[$key] = [
                'display_value' => $this->stringValue($audit['displayValue'] ?? null),
                'numeric_value' => is_numeric($numericValue) ? (float) $numericValue : null,
            ];
        }

        return $metrics;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<PageSpeedAuditItemData>
     */
    private function auditItems(array $payload, bool $opportunities): array
    {
        $audits = data_get($payload, 'lighthouseResult.audits', []);

        if (! is_array($audits)) {
            return [];
        }

        $items = [];

        foreach ($audits as $key => $audit) {
            if (! is_string($key) || ! is_array($audit)) {
                continue;
            }

            $details = $audit['details'] ?? [];
            $type = is_array($details) ? ($details['type'] ?? null) : null;
            $score = $audit['score'] ?? null;
            $numericValue = $audit['numericValue'] ?? null;

            if ($opportunities !== ($type === 'opportunity')) {
                continue;
            }

            if (! $opportunities && (! is_numeric($score) || (float) $score >= 1.0)) {
                continue;
            }

            $title = $this->stringValue($audit['title'] ?? null);

            if ($title === null) {
                continue;
            }

            $items[] = new PageSpeedAuditItemData(
                key: $key,
                title: $title,
                description: $this->stringValue($audit['description'] ?? null),
                displayValue: $this->stringValue($audit['displayValue'] ?? null),
                score: is_numeric($score) ? (int) round(((float) $score) * 100) : null,
                numericValue: is_numeric($numericValue) ? (float) $numericValue : null,
            );
        }

        usort(
            $items,
            static fn (PageSpeedAuditItemData $first, PageSpeedAuditItemData $second): int => ($second->numericValue ?? 0) <=> ($first->numericValue ?? 0),
        );

        return array_slice($items, 0, 5);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function fetchedAt(array $payload): ?CarbonImmutable
    {
        $fetchTime = $this->stringValue(data_get($payload, 'lighthouseResult.fetchTime'));

        if ($fetchTime === null) {
            return null;
        }

        try {
            return CarbonImmutable::parse($fetchTime);
        } catch (Throwable) {
            return null;
        }
    }

    private function errorMessage(Response $response): string
    {
        $message = $this->stringValue($response->json('error.message'));

        return $message ?? __('capell-seo-suite::generic.pagespeed_request_failed', ['status' => $response->status()]);
    }

    private function stringValue(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }
}
