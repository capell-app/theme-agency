<?php

declare(strict_types=1);

use Capell\AgentDelivery\Providers\AgentDeliveryServiceProvider;
use Capell\AgentDelivery\Tests\AgentDeliveryTestCase;
use Capell\Core\Facades\CapellCore;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\getJson;

uses(AgentDeliveryTestCase::class);

it('returns public agent delivery discovery metadata', function (): void {
    getJson('http://example.com/api/capell/agent/v1/discovery')
        ->assertOk()
        ->assertHeader('X-Capell-Agent-Delivery-Version', 'v1')
        ->assertHeader('X-Capell-Cache-Tags', 'agent-delivery')
        ->assertHeader('X-Capell-Agent-Delivery-Variation', 'site,language,url,page')
        ->assertHeader('Cache-Control', 'max-age=300, public')
        ->assertHeader('Vary', 'Host')
        ->assertHeader('ETag')
        ->assertJsonPath('data.type', 'capell-agent-delivery-discovery')
        ->assertJsonPath('data.version', 'v1')
        ->assertJsonPath('data.capabilities', [
            'public-page-index',
            'public-page-manifest',
            'public-page-chunks',
        ])
        ->assertJsonPath('data.endpoints.0.rel', 'index')
        ->assertJsonPath('data.endpoints.0.url', 'http://example.com/api/capell/agent/v1/pages')
        ->assertJsonPath('data.endpoints.1.urlTemplate', 'http://example.com/api/capell/agent/v1/pages/manifest{?url,locale}')
        ->assertJsonPath('data.endpoints.2.urlTemplate', 'http://example.com/api/capell/agent/v1/pages/chunks{?url,locale}')
        ->assertJsonPath('data.cacheVariation', 'site,language,url,page')
        ->assertJsonStructure(['meta' => ['generatedAt']]);
});

it('returns a 304 response when discovery etag matches', function (): void {
    $response = getJson('http://example.com/api/capell/agent/v1/discovery')
        ->assertOk()
        ->assertHeader('ETag');

    getJson('http://example.com/api/capell/agent/v1/discovery', [
        'If-None-Match' => discoveryResponseHeader($response, 'ETag'),
    ])
        ->assertStatus(304)
        ->assertHeader('ETag', discoveryResponseHeader($response, 'ETag'))
        ->assertHeader('X-Capell-Agent-Delivery-Variation', 'site,language,url,page')
        ->assertHeader('Vary', 'Host');
});

it('returns 404 when package is not installed', function (): void {
    CapellCore::forcePackageInstalled(AgentDeliveryServiceProvider::$packageName, false);

    getJson('http://example.com/api/capell/agent/v1/discovery')
        ->assertNotFound()
        ->assertExactJson(['message' => 'Page not found'])
        ->assertHeader('X-Capell-Agent-Delivery-Version', 'v1')
        ->assertHeaderMissing('X-Capell-Cache-Tags');
});

function discoveryResponseHeader(TestResponse $response, string $header): string
{
    $value = $response->baseResponse->headers->get($header);

    return is_string($value) ? $value : '';
}
