<?php

declare(strict_types=1);

use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\LiveChat\Integrations\AgentBridge\LiveChatAgentBridgeCapabilityProvider;
use Capell\LiveChat\Providers\LiveChatServiceProvider;

it('registers LiveChat capabilities from the Live Chat package provider', function (): void {
    $registry = new CapellAgentBridgeCapabilityRegistry;

    (new LiveChatAgentBridgeCapabilityProvider)->registerCapabilities($registry);

    $preview = $registry->get('capell.live-chat.summary.preview');
    $escalate = $registry->get('capell.live-chat.escalate');
    $close = $registry->get('capell.live-chat.close');

    expect($preview->requiredPackage)->toBe(LiveChatServiceProvider::$packageName)
        ->and($preview->needsConfirmation())->toBeFalse()
        ->and($escalate->risk)->toBe(CapabilityRiskEnum::High)
        ->and($escalate->needsConfirmation())->toBeTrue()
        ->and($escalate->auditEvent)->toBe('capell_agent-bridge.live-chat.escalated')
        ->and($close->needsConfirmation())->toBeTrue()
        ->and($close->auditEvent)->toBe('capell_agent-bridge.live-chat.closed');
});
