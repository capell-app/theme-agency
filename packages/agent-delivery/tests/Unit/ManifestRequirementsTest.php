<?php

declare(strict_types=1);

use Capell\AgentDelivery\Contracts\AgentDeliveryChunkContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryMetadataContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryReferenceContributor;
use Capell\AgentDelivery\Contracts\AgentDeliveryRelatedUrlContributor;
use Capell\AgentDelivery\Health\AgentDeliveryHealthCheck;
use Capell\AgentDelivery\Manifest\AgentDeliveryContractsContribution;
use Capell\AgentDelivery\Manifest\AgentDeliveryRoutesContribution;
use Capell\AgentDelivery\Tests\AgentDeliveryTestCase;
use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionRoute;
use Capell\Core\Support\Manifest\ManifestValidator;

uses(AgentDeliveryTestCase::class);

it('declares shipped agent delivery manifest contributions', function (): void {
    $packagePath = dirname(__DIR__, 2);
    $manifest = capell_json_file_array($packagePath . '/capell.json');
    $composer = capell_json_file_array($packagePath . '/composer.json');
    $contributions = data_get($manifest, 'contributes');

    throw_unless(is_array($contributions), RuntimeException::class, 'Expected Agent Delivery manifest contributions.');

    (new ManifestValidator)->validate($manifest, $composer, 'capell-app/agent-delivery', $packagePath . '/capell.json');

    expect($manifest)
        ->toHaveKey('manifest-version', 3)
        ->toHaveKey('name', 'capell-app/agent-delivery')
        ->toHaveKey('namespace', 'Capell\\AgentDelivery')
        ->and(data_get($manifest, 'security.publicSurface.routeNames'))->toBe([
            'capell-agent-delivery.pages.chunks',
            'capell-agent-delivery.pages.index',
            'capell-agent-delivery.pages.manifest',
        ])
        ->and(data_get($manifest, 'security.publicSurface.auth'))->toBe('public')
        ->and(data_get($manifest, 'security.publicSurface.throttledRoutes'))->toBe([
            'capell-agent-delivery.pages.chunks',
            'capell-agent-delivery.pages.index',
            'capell-agent-delivery.pages.manifest',
        ])
        ->and(data_get($manifest, 'healthChecks.0.class'))->toBe(AgentDeliveryHealthCheck::class)
        ->and(data_get($manifest, 'contributionTraceability.deferredContributions'))->toBe([]);

    expect($contributions)
        ->toContain([
            'type' => 'route',
            'class' => AgentDeliveryRoutesContribution::class,
            'apiSurface' => 'public-json',
            'endpointType' => 'public-api-endpoint',
            'version' => 'v1',
            'methods' => ['GET'],
            'contentType' => 'application/json',
            'auth' => 'public',
            'cachePolicy' => 'short-lived-public-json',
            'conditionalRequests' => true,
            'routes' => [
                'capell-agent-delivery.pages.index',
                'capell-agent-delivery.pages.manifest',
                'capell-agent-delivery.pages.chunks',
            ],
            'prefix' => 'api/capell/agent/v1',
            'middleware' => [
                'api',
                'throttle:capell-agent-delivery',
            ],
        ])
        ->toContain([
            'type' => 'agent-capability',
            'class' => AgentDeliveryContractsContribution::class,
            'contracts' => [
                AgentDeliveryContributor::class,
                AgentDeliveryMetadataContributor::class,
                AgentDeliveryChunkContributor::class,
                AgentDeliveryReferenceContributor::class,
                AgentDeliveryRelatedUrlContributor::class,
            ],
        ])
        ->toContain([
            'type' => 'health-check',
            'class' => AgentDeliveryHealthCheck::class,
        ]);

    expect(class_implements(AgentDeliveryRoutesContribution::class))->toContain(RegistersExtensionRoute::class)
        ->and(class_implements(AgentDeliveryContractsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(AgentDeliveryHealthCheck::class))->toContain(ChecksExtensionHealth::class);
});
