<?php

declare(strict_types=1);

use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Capell\SeoSuite\Support\PageSpeed\GooglePageSpeedInsightsClient;
use Illuminate\Support\Facades\Http;

it('maps PageSpeed Insights lighthouse payloads into audit data', function (): void {
    Http::fake([
        'https://www.googleapis.com/pagespeedonline/v5/runPagespeed*' => Http::response([
            'lighthouseResult' => [
                'lighthouseVersion' => '12.0.0',
                'fetchTime' => '2026-05-29T06:00:00.000Z',
                'categories' => [
                    'performance' => ['score' => 0.82],
                    'accessibility' => ['score' => 0.91],
                    'best-practices' => ['score' => 0.77],
                    'seo' => ['score' => 1],
                ],
                'audits' => [
                    'largest-contentful-paint' => [
                        'displayValue' => '2.4 s',
                        'numericValue' => 2400,
                        'score' => 0.85,
                    ],
                    'cumulative-layout-shift' => [
                        'displayValue' => '0.02',
                        'numericValue' => 0.02,
                        'score' => 1,
                    ],
                    'uses-optimized-images' => [
                        'title' => 'Efficiently encode images',
                        'description' => 'Image resources can be smaller.',
                        'displayValue' => 'Potential savings of 120 KiB',
                        'numericValue' => 120,
                        'score' => 0.2,
                        'details' => ['type' => 'opportunity'],
                    ],
                    'dom-size' => [
                        'title' => 'Avoid an excessive DOM size',
                        'description' => 'Large DOMs increase memory usage.',
                        'displayValue' => '1,600 elements',
                        'numericValue' => 1600,
                        'score' => 0.4,
                        'details' => ['type' => 'table'],
                    ],
                ],
            ],
        ]),
    ]);

    $client = new GooglePageSpeedInsightsClient([
        'enabled' => true,
        'api_key' => 'test-key',
        'timeout' => 5,
    ]);

    $result = $client->analyze('https://example.test/about', PageSpeedStrategyEnum::Mobile);
    $largestContentfulPaint = $result->metrics['largest-contentful-paint'] ?? [];

    expect($result->successful)->toBeTrue()
        ->and($result->performanceScore())->toBe(82)
        ->and($result->categoryScores['accessibility'])->toBe(91)
        ->and($largestContentfulPaint['display_value'] ?? null)->toBe('2.4 s')
        ->and($result->opportunities[0]->key)->toBe('uses-optimized-images')
        ->and($result->diagnostics[0]->key)->toBe('dom-size')
        ->and($result->lighthouseVersion)->toBe('12.0.0')
        ->and($result->fetchedAt?->toIso8601String())->toBe('2026-05-29T06:00:00+00:00');
});

it('returns failed audit data for PageSpeed API errors', function (): void {
    Http::fake([
        'https://www.googleapis.com/pagespeedonline/v5/runPagespeed*' => Http::response([
            'error' => ['message' => 'Quota exceeded.'],
        ], 429),
    ]);

    $client = new GooglePageSpeedInsightsClient([
        'enabled' => true,
        'api_key' => 'test-key',
    ]);

    $result = $client->analyze('https://example.test/about', PageSpeedStrategyEnum::Desktop);

    expect($result->successful)->toBeFalse()
        ->and($result->errorMessage)->toBe('Quota exceeded.');
});
