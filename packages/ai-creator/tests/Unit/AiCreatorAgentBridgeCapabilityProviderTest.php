<?php

declare(strict_types=1);

use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\AiCreator\AgentBridge\AiCreatorAgentBridgeCapabilityProvider;

it('registers ai creator agent bridge capabilities with confirmation on apply', function (): void {
    $registry = new CapellAgentBridgeCapabilityRegistry;

    (new AiCreatorAgentBridgeCapabilityProvider)->registerCapabilities($registry);

    expect($registry->has('capell.ai-creator.sessions.start'))->toBeTrue()
        ->and($registry->has('capell.ai-creator.sessions.preview_apply'))->toBeTrue()
        ->and($registry->has('capell.ai-creator.sessions.apply'))->toBeTrue();

    $applyCapability = $registry->get('capell.ai-creator.sessions.apply');

    expect($applyCapability->scope)->toBe('capell.ai-creator.apply')
        ->and($applyCapability->risk)->toBe(CapabilityRiskEnum::High)
        ->and($applyCapability->needsConfirmation())->toBeTrue();

    $startCapability = $registry->get('capell.ai-creator.sessions.start');
    $previewCapability = $registry->get('capell.ai-creator.sessions.preview_apply');

    expect($startCapability->inputSchema)->toHaveKey('required', ['intent'])
        ->and($previewCapability->inputSchema)->toHaveKey('required', ['session_id'])
        ->and($applyCapability->inputSchema)->toHaveKey('required', ['session_id']);
});
