<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AiCreator\Actions\Discovery\ListSectionTypesAction;

/**
 * Discovery capability: list section blueprints. Read-only.
 */
final class ListSectionTypesCapabilityAction implements CapellAgentBridgeCapabilityAction
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
            message: 'Installed section types.',
            data: ['section_types' => ListSectionTypesAction::run()],
        );
    }
}
