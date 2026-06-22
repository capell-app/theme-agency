<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityProvider;
use Capell\AgentBridge\Data\CapabilityData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AgentBridge\Enums\CapabilityRiskEnum;
use Capell\AgentBridge\Enums\CapabilityServerEnum;
use Capell\AgentBridge\Support\CapabilitySchemas;
use Capell\AgentBridge\Support\CapellAgentBridgeCapabilityRegistry;
use Capell\AiCreator\Support\AiCreatorCapabilitySchemas;

final class AiCreatorAgentBridgeCapabilityProvider implements CapellAgentBridgeCapabilityProvider
{
    public function registerCapabilities(CapellAgentBridgeCapabilityRegistry $registry): void
    {
        foreach ($this->capabilities() as $capability) {
            if ($registry->has($capability->key)) {
                continue;
            }

            $registry->register($capability);
        }
    }

    /**
     * @return list<CapabilityData>
     */
    private function capabilities(): array
    {
        return [
            new CapabilityData(
                key: 'capell.ai-creator.sessions.start',
                name: 'Start AI Creator session',
                description: 'Start a reviewed AI Creator session for an existing Capell site or content workflow.',
                scope: 'capell.ai-creator.write',
                server: CapabilityServerEnum::Site,
                risk: CapabilityRiskEnum::Low,
                actionClass: StartAiCreatorSessionCapabilityAction::class,
                requiredPackage: 'capell-app/ai-creator',
                outputDataClass: CapabilityResultData::class,
                inputSchema: AiCreatorCapabilitySchemas::startSessionInput(),
                outputSchema: CapabilitySchemas::capabilityResultOutput(),
                supportsPreview: true,
                requiresConfirmation: false,
                auditEvent: 'ai-creator.sessions.start',
            ),
            new CapabilityData(
                key: 'capell.ai-creator.sessions.preview_apply',
                name: 'Preview AI Creator apply',
                description: 'Read the deterministic preview for an AI Creator session before applying changes.',
                scope: 'capell.ai-creator.read',
                server: CapabilityServerEnum::Site,
                risk: CapabilityRiskEnum::Read,
                actionClass: PreviewAiCreatorSessionCapabilityAction::class,
                requiredPackage: 'capell-app/ai-creator',
                outputDataClass: CapabilityResultData::class,
                inputSchema: AiCreatorCapabilitySchemas::sessionIdInput(),
                outputSchema: CapabilitySchemas::capabilityResultOutput(),
                supportsPreview: true,
                requiresConfirmation: false,
                auditEvent: 'ai-creator.sessions.preview',
            ),
            new CapabilityData(
                key: 'capell.ai-creator.sessions.apply',
                name: 'Apply AI Creator session',
                description: 'Apply a confirmed AI Creator session after review.',
                scope: 'capell.ai-creator.apply',
                server: CapabilityServerEnum::Site,
                risk: CapabilityRiskEnum::High,
                actionClass: ApplyAiCreatorSessionCapabilityAction::class,
                requiredPackage: 'capell-app/ai-creator',
                outputDataClass: CapabilityResultData::class,
                inputSchema: AiCreatorCapabilitySchemas::sessionIdInput(),
                outputSchema: CapabilitySchemas::capabilityResultOutput(),
                supportsPreview: true,
                requiresConfirmation: true,
                auditEvent: 'ai-creator.sessions.apply',
            ),
        ];
    }
}
