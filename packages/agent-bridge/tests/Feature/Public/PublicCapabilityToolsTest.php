<?php

declare(strict_types=1);

use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\AgentBridge\Tests\Fixtures\FakeCapabilityAction;
use Capell\AgentBridge\Tools\Public\ListPublicCapabilitiesTool;
use Capell\AgentBridge\Tools\Public\RunPublicCapabilityTool;
use Laravel\Mcp\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Build a CapabilityData with sane defaults. Leaves requiredPackage null so the
 * registry's package-availability filter does not hide it in the test env.
 */
function makeCapability(
    string $key,
    CapabilityRiskEnum $risk = CapabilityRiskEnum::Read,
    bool $public = false,
): CapabilityData {
    return new CapabilityData(
        key: $key,
        name: $key,
        description: $key,
        scope: $key,
        server: CapabilityServerEnum::Site,
        risk: $risk,
        actionClass: FakeCapabilityAction::class,
        requiresConfirmation: false,
        public: $public,
    );
}

it('defaults public to false and serialises it in the payload', function (): void {
    $capability = makeCapability('discovery.list_themes');

    expect($capability->public)->toBeFalse();
    expect($capability->toPayload())->toHaveKey('public');
});

it('publiclyReadable returns only public Read caps whose package is available', function (): void {
    $registry = new CapellAgentBridgeCapabilityRegistry;
    $registry->register(makeCapability('pub.read', risk: CapabilityRiskEnum::Read, public: true));
    $registry->register(makeCapability('pub.write', risk: CapabilityRiskEnum::High, public: true));
    $registry->register(makeCapability('priv.read', risk: CapabilityRiskEnum::Read, public: false));

    $keys = $registry->publiclyReadable()->map->key->all();

    expect($keys)->toContain('pub.read')
        ->and($keys)->not->toContain('pub.write')   // write excluded even if flagged public
        ->and($keys)->not->toContain('priv.read');  // non-public excluded
});

it('lists only publicly readable capability payloads', function (): void {
    $registry = new CapellAgentBridgeCapabilityRegistry;
    $registry->register(makeCapability('pub.read', risk: CapabilityRiskEnum::Read, public: true));
    $registry->register(makeCapability('priv.read', risk: CapabilityRiskEnum::Read, public: false));
    app()->instance(CapellAgentBridgeCapabilityRegistry::class, $registry);

    $response = (new ListPublicCapabilitiesTool)->handle($registry);
    $capabilities = $response->getStructuredContent()['capabilities'] ?? null;

    throw_unless(is_array($capabilities), RuntimeException::class, 'Expected public capabilities list.');

    $keys = array_column($capabilities, 'key');
    expect($keys)->toContain('pub.read')->and($keys)->not->toContain('priv.read');
});

it('runs a public Read capability with a null client', function (): void {
    $registry = new CapellAgentBridgeCapabilityRegistry;
    $registry->register(makeCapability('pub.read', risk: CapabilityRiskEnum::Read, public: true));
    app()->instance(CapellAgentBridgeCapabilityRegistry::class, $registry);

    $response = (new RunPublicCapabilityTool)->handle(
        new Request(['capability' => 'pub.read', 'payload' => ['name' => 'Example']]),
        $registry,
    );

    $structured = $response->getStructuredContent();

    expect($structured)->toBeArray()
        ->and($structured['mode'])->toBe('executed')        // executed, no auth required
        ->and($structured['capability'])->toBe('pub.read');
});

it('REFUSES a non-public capability even though the action would tolerate a null client', function (): void {
    $registry = new CapellAgentBridgeCapabilityRegistry;
    // A genuinely registered, executable write cap — only the allowlist gate stops it.
    $registry->register(makeCapability('build_preview', risk: CapabilityRiskEnum::High, public: false));
    app()->instance(CapellAgentBridgeCapabilityRegistry::class, $registry);

    (new RunPublicCapabilityTool)->handle(
        new Request(['capability' => 'build_preview', 'payload' => []]),
        $registry,
    );
})->throws(HttpException::class);
