<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AiCreator\Support\CapellSiteSpecSchema;

/**
 * Discovery capability: return the JSON schema for CapellSiteSpecData so the
 * agent can assemble a valid spec. Read-only and input-free.
 */
final class GetSiteSpecSchemaCapabilityAction implements CapellAgentBridgeCapabilityAction
{
    public function preview(CapabilityInvocationData $invocation): CapabilityResultData
    {
        return $this->result();
    }

    public function execute(CapabilityInvocationData $invocation): CapabilityResultData
    {
        return $this->result();
    }

    private function result(): CapabilityResultData
    {
        return new CapabilityResultData(
            ok: true,
            message: 'Site spec schema.',
            data: ['schema' => CapellSiteSpecSchema::toArray()],
        );
    }
}
