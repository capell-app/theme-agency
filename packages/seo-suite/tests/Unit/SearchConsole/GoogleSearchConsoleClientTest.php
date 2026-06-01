<?php

declare(strict_types=1);

use Capell\Core\Models\Site;
use Capell\Core\Models\SiteDomain;
use Capell\SeoSuite\Actions\PersistSearchConsoleUrlMetricAction;
use Capell\SeoSuite\Enums\SearchConsoleMetricEnum;
use Capell\SeoSuite\Support\SearchConsole\GoogleSearchConsoleClient;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Http;

function createSeoSuiteSearchConsoleCredentialsFile(): string
{
    $credentialsPath = tempnam(sys_get_temp_dir(), 'search-console-credentials');
    $privateKey = openssl_pkey_new([
        'private_key_bits' => 1024,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);
    $privateKeyContents = '';

    expect($credentialsPath)->toBeString();
    expect($privateKey)->not()->toBeFalse();
    assert($privateKey instanceof OpenSSLAsymmetricKey);

    openssl_pkey_export($privateKey, $privateKeyContents);

    file_put_contents($credentialsPath, json_encode([
        'client_email' => 'seo-suite@example.iam.gserviceaccount.com',
        'private_key' => $privateKeyContents,
        'token_uri' => 'https://oauth2.googleapis.com/token',
    ], JSON_THROW_ON_ERROR));

    return $credentialsPath;
}

it('maps search insights rows into page insights', function (): void {
    $credentialsPath = tempnam(sys_get_temp_dir(), 'search-console-credentials');
    $privateKey = openssl_pkey_new([
        'private_key_bits' => 1024,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);
    $privateKeyContents = '';

    expect($credentialsPath)->toBeString();
    expect($privateKey)->not()->toBeFalse();
    assert($privateKey instanceof OpenSSLAsymmetricKey);

    openssl_pkey_export($privateKey, $privateKeyContents);

    file_put_contents($credentialsPath, json_encode([
        'client_email' => 'seo-suite@example.iam.gserviceaccount.com',
        'private_key' => $privateKeyContents,
        'token_uri' => 'https://oauth2.googleapis.com/token',
    ], JSON_THROW_ON_ERROR));

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-token'], 200),
        'https://searchconsole.googleapis.com/*' => Http::response([
            'rows' => [[
                'clicks' => 12,
                'impressions' => 120,
                'ctr' => 0.1,
                'position' => 4.2,
            ]],
        ], 200),
    ]);

    $client = new GoogleSearchConsoleClient([
        'enabled' => true,
        'credentials_path' => $credentialsPath,
        'property_url' => 'https://example.com/',
    ]);

    $insights = $client->pageInsights('https://example.com/about');

    unlink($credentialsPath);

    expect($insights)->toHaveCount(4)
        ->and($insights[0]->metric)->toBe(SearchConsoleMetricEnum::Clicks)
        ->and($insights[0]->value)->toBe(12)
        ->and($insights[1]->metric)->toBe(SearchConsoleMetricEnum::Impressions)
        ->and($insights[1]->value)->toBe(120)
        ->and($insights[2]->metric)->toBe(SearchConsoleMetricEnum::Ctr)
        ->and($insights[2]->value)->toBe(0.1)
        ->and($insights[3]->metric)->toBe(SearchConsoleMetricEnum::Position)
        ->and($insights[3]->value)->toBe(4.2);
});

it('uses the configured timeout for search console token and query requests', function (): void {
    $credentialsPath = createSeoSuiteSearchConsoleCredentialsFile();
    $timeouts = [];

    Http::fake(function (ClientRequest $request, array $options) use (&$timeouts): PromiseInterface {
        $timeouts[] = $options['timeout'] ?? null;

        if ($request->url() === 'https://oauth2.googleapis.com/token') {
            return Http::response(['access_token' => 'test-token'], 200);
        }

        return Http::response([
            'rows' => [[
                'clicks' => 12,
                'impressions' => 120,
                'ctr' => 0.1,
                'position' => 4.2,
            ]],
        ], 200);
    });

    $client = new GoogleSearchConsoleClient([
        'enabled' => true,
        'credentials_path' => $credentialsPath,
        'property_url' => 'https://example.com/',
        'timeout' => 17,
    ]);

    $insights = $client->pageInsights('https://example.com/about');

    unlink($credentialsPath);

    expect($insights)->toHaveCount(4)
        ->and($timeouts)->toBe([17, 17]);
});

it('maps current and previous search insights rows into url metric rows', function (): void {
    Date::setTestNow(Date::create(2024, 3, 30, 12, 0, 0));

    $credentialsPath = tempnam(sys_get_temp_dir(), 'search-console-credentials');
    $privateKey = openssl_pkey_new([
        'private_key_bits' => 1024,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);
    $privateKeyContents = '';

    expect($credentialsPath)->toBeString();
    expect($privateKey)->not()->toBeFalse();
    assert($privateKey instanceof OpenSSLAsymmetricKey);

    openssl_pkey_export($privateKey, $privateKeyContents);

    file_put_contents($credentialsPath, json_encode([
        'client_email' => 'seo-suite@example.iam.gserviceaccount.com',
        'private_key' => $privateKeyContents,
        'token_uri' => 'https://oauth2.googleapis.com/token',
    ], JSON_THROW_ON_ERROR));

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-token'], 200),
        'https://searchconsole.googleapis.com/*' => Http::sequence()
            ->push([
                'rows' => [
                    [
                        'keys' => ['https://example.com/about'],
                        'clicks' => 10,
                        'impressions' => 100,
                        'ctr' => 0.1,
                        'position' => 4.2,
                    ],
                    [
                        'keys' => ['https://example.com/contact'],
                        'clicks' => 5,
                        'impressions' => 50,
                        'ctr' => 0.1,
                        'position' => 8.4,
                    ],
                ],
            ], 200)
            ->push([
                'rows' => [
                    [
                        'keys' => ['https://example.com/about'],
                        'clicks' => 30,
                        'impressions' => 180,
                        'ctr' => 0.16,
                        'position' => 3.1,
                    ],
                    [
                        'keys' => ['https://example.com/removed'],
                        'clicks' => 15,
                        'impressions' => 80,
                        'ctr' => 0.18,
                        'position' => 5.6,
                    ],
                ],
            ], 200),
    ]);

    $client = new GoogleSearchConsoleClient([
        'enabled' => true,
        'credentials_path' => $credentialsPath,
        'property_url' => 'https://example.com/',
    ]);

    $metricRows = $client->urlMetricRows(siteId: 1, limit: 100);

    unlink($credentialsPath);
    Date::setTestNow();

    expect($metricRows)->toHaveCount(3)
        ->and($metricRows[0])->toMatchArray([
            'url' => 'https://example.com/about',
            'clicks' => 10,
            'impressions' => 100,
            'ctr' => 0.1,
            'average_position' => 4.2,
            'previous_clicks' => 30,
            'previous_impressions' => 180,
            'previous_ctr' => 0.16,
            'previous_average_position' => 3.1,
            'window_start' => '2024-03-02',
            'window_end' => '2024-03-29',
        ])
        ->and($metricRows[1])->toMatchArray([
            'url' => 'https://example.com/contact',
            'clicks' => 5,
            'previous_clicks' => 0,
        ])
        ->and($metricRows[2])->toMatchArray([
            'url' => 'https://example.com/removed',
            'clicks' => 0,
            'previous_clicks' => 15,
        ]);
});

it('maps current and previous search analytics query page rows into query metric rows', function (): void {
    Date::setTestNow(Date::create(2024, 3, 30, 12, 0, 0));

    $credentialsPath = tempnam(sys_get_temp_dir(), 'search-console-credentials');
    $privateKey = openssl_pkey_new([
        'private_key_bits' => 1024,
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
    ]);
    $privateKeyContents = '';

    expect($credentialsPath)->toBeString();
    expect($privateKey)->not()->toBeFalse();
    assert($privateKey instanceof OpenSSLAsymmetricKey);

    openssl_pkey_export($privateKey, $privateKeyContents);

    file_put_contents($credentialsPath, json_encode([
        'client_email' => 'seo-suite@example.iam.gserviceaccount.com',
        'private_key' => $privateKeyContents,
        'token_uri' => 'https://oauth2.googleapis.com/token',
    ], JSON_THROW_ON_ERROR));

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-token'], 200),
        'https://searchconsole.googleapis.com/*' => Http::sequence()
            ->push([
                'rows' => [
                    [
                        'keys' => ['Capell CMS', 'https://example.com/about'],
                        'clicks' => 10,
                        'impressions' => 100,
                        'ctr' => 0.1,
                        'position' => 4.2,
                    ],
                ],
            ], 200)
            ->push([
                'rows' => [
                    [
                        'keys' => ['capell cms', 'https://example.com/about'],
                        'clicks' => 30,
                        'impressions' => 180,
                        'ctr' => 0.16,
                        'position' => 3.1,
                    ],
                ],
            ], 200),
    ]);

    $client = new GoogleSearchConsoleClient([
        'enabled' => true,
        'credentials_path' => $credentialsPath,
        'property_url' => 'https://example.com/',
    ]);

    $metricRows = $client->queryMetricRows(siteId: 1, limit: 100);

    Http::assertSent(fn ($request): bool => str_contains((string) $request->url(), '/searchAnalytics/query')
        && $request['dimensions'] === ['query', 'page']);

    unlink($credentialsPath);
    Date::setTestNow();

    expect($metricRows)->toHaveCount(1)
        ->and($metricRows[0])->toMatchArray([
            'query' => 'capell cms',
            'url' => 'https://example.com/about',
            'clicks' => 10,
            'impressions' => 100,
            'ctr' => 0.1,
            'average_position' => 4.2,
            'previous_clicks' => 30,
            'previous_impressions' => 180,
            'previous_ctr' => 0.16,
            'previous_average_position' => 3.1,
            'window_start' => '2024-03-02',
            'window_end' => '2024-03-29',
        ]);
});

it('returns top declining pages from stored search console metrics', function (): void {
    $credentialsPath = tempnam(sys_get_temp_dir(), 'search-console-credentials');
    $site = Site::factory()->create();

    expect($credentialsPath)->toBeString();

    PersistSearchConsoleUrlMetricAction::run(
        siteId: (int) $site->getKey(),
        url: 'https://example.com/about',
        windowStart: now()->subDays(28),
        windowEnd: now(),
        clicks: 10,
        impressions: 100,
        ctr: 0.1,
        averagePosition: 4.2,
        previousClicks: 30,
        previousImpressions: 140,
        previousCtr: 0.2,
        previousAveragePosition: 3.1,
    );
    PersistSearchConsoleUrlMetricAction::run(
        siteId: (int) $site->getKey(),
        url: 'https://example.com/contact',
        windowStart: now()->subDays(28),
        windowEnd: now(),
        clicks: 40,
        impressions: 120,
        ctr: 0.3,
        averagePosition: 2.2,
        previousClicks: 30,
        previousImpressions: 110,
        previousCtr: 0.2,
        previousAveragePosition: 2.5,
    );

    $client = new GoogleSearchConsoleClient([
        'enabled' => true,
        'credentials_path' => $credentialsPath,
        'property_url' => 'https://example.com/',
    ]);

    $decliningPages = $client->decliningPages(siteId: (int) $site->getKey(), limit: 5);

    unlink($credentialsPath);

    expect($decliningPages)->toHaveCount(1)
        ->and($decliningPages[0]['url'])->toBe('https://example.com/about')
        ->and($decliningPages[0]['clicks'])->toBe(10)
        ->and($decliningPages[0]['previous_clicks'])->toBe(30)
        ->and($decliningPages[0]['click_delta'])->toBe(-20);
});

it('returns empty search console data for disabled missing and failed upstream states', function (): void {
    $disabledClient = new GoogleSearchConsoleClient([
        'enabled' => false,
        'credentials_path' => null,
    ]);

    expect($disabledClient->pageInsights('https://example.com/about'))->toBe([])
        ->and($disabledClient->decliningPages(siteId: 999))->toBe([])
        ->and($disabledClient->urlMetricRows(siteId: 999))->toBe([])
        ->and($disabledClient->queryMetricRows(siteId: 999))->toBe([]);

    $credentialsPath = createSeoSuiteSearchConsoleCredentialsFile();

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-token'], 200),
        'https://searchconsole.googleapis.com/*' => Http::response(['rows' => []], 200),
    ]);

    $emptyClient = new GoogleSearchConsoleClient([
        'enabled' => true,
        'credentials_path' => $credentialsPath,
        'property_url' => 'https://example.com/',
    ]);

    expect($emptyClient->pageInsights('https://example.com/about'))->toBe([]);

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-token'], 200),
        'https://searchconsole.googleapis.com/*' => Http::response(['error' => 'rate limited'], 429),
    ]);

    $failedClient = new GoogleSearchConsoleClient([
        'enabled' => true,
        'credentials_path' => $credentialsPath,
        'property_url' => 'https://example.com/',
    ]);

    expect($failedClient->pageInsights('https://example.com/about'))->toBe([]);

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['error' => 'invalid_grant'], 500),
        'https://searchconsole.googleapis.com/*' => Http::response(['rows' => []], 401),
    ]);

    $tokenFailureClient = new GoogleSearchConsoleClient([
        'enabled' => true,
        'credentials_path' => $credentialsPath,
        'property_url' => 'https://example.com/',
    ]);

    expect($tokenFailureClient->pageInsights('https://example.com/about'))->toBe([]);

    unlink($credentialsPath);
});

it('normalizes malformed search console rows and reuses access tokens across requests', function (): void {
    Date::setTestNow(Date::create(2024, 3, 30, 12, 0, 0));

    $credentialsPath = createSeoSuiteSearchConsoleCredentialsFile();

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-token'], 200),
        'https://searchconsole.googleapis.com/*' => Http::sequence()
            ->push([
                'rows' => [
                    ['keys' => ['', ''], 'clicks' => 'not numeric'],
                    ['keys' => ['valid query', ''], 'impressions' => '12'],
                    ['query' => 'Fallback Query', 'url' => 'https://example.com/fallback', 'clicks' => '8', 'impressions' => '90', 'ctr' => '0.12', 'position' => '7.5'],
                ],
            ], 200)
            ->push([
                'rows' => [
                    ['query' => 'Fallback Query', 'url' => 'https://example.com/fallback', 'clicks' => '3', 'impressions' => '30', 'ctr' => '0.1', 'position' => '9.5'],
                ],
            ], 200)
            ->push([
                'rows' => [[
                    'clicks' => 1,
                    'impressions' => 10,
                    'ctr' => 0.1,
                    'position' => 2.0,
                ]],
            ], 200),
    ]);

    $client = new GoogleSearchConsoleClient([
        'enabled' => true,
        'credentials_path' => $credentialsPath,
        'property_url' => 'https://example.com/',
    ]);

    $queryRows = $client->queryMetricRows(siteId: 1, limit: 10);
    $pageInsights = $client->pageInsights('https://example.com/fallback');

    Http::assertSentCount(4);

    unlink($credentialsPath);
    Date::setTestNow();

    expect($queryRows)->toHaveCount(1)
        ->and($queryRows[0])->toMatchArray([
            'query' => 'fallback query',
            'url' => 'https://example.com/fallback',
            'clicks' => 8,
            'impressions' => 90,
            'ctr' => 0.12,
            'average_position' => 7.5,
            'previous_clicks' => 3,
        ])
        ->and($pageInsights)->toHaveCount(4);
});

it('derives search console property urls from the site default domain when no property is configured', function (): void {
    Date::setTestNow(Date::create(2024, 3, 30, 12, 0, 0));

    $siteDomain = SiteDomain::factory()->create([
        'scheme' => 'https',
        'domain' => 'example.test',
        'path' => null,
        'default' => true,
    ]);
    $credentialsPath = createSeoSuiteSearchConsoleCredentialsFile();

    Http::fake([
        'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-token'], 200),
        'https://searchconsole.googleapis.com/*' => Http::sequence()
            ->push(['rows' => [['keys' => ['https://example.test/about'], 'clicks' => 4, 'impressions' => 40, 'ctr' => 0.1, 'position' => 6.2]]], 200)
            ->push(['rows' => []], 200),
    ]);

    $client = new GoogleSearchConsoleClient([
        'enabled' => true,
        'credentials_path' => $credentialsPath,
    ]);

    $metricRows = $client->urlMetricRows((int) $siteDomain->site_id, 5);

    Http::assertSent(fn (ClientRequest $request): bool => str_contains(rawurldecode($request->url()), 'https://example.test/'));

    unlink($credentialsPath);
    Date::setTestNow();

    expect($metricRows)->toHaveCount(1)
        ->and($metricRows[0]['url'])->toBe('https://example.test/about')
        ->and($metricRows[0]['clicks'])->toBe(4);
});
