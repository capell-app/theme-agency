<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AiCreator\Actions\Discovery\ValidateSiteSpecAction;
use Illuminate\Support\Arr;

/**
 * Discovery capability: validate + normalise a candidate site spec. Read-only —
 * it builds nothing. A spec that fails validation is returned as ok:true with
 * valid:false plus structured errors, so the agent can correct and retry.
 */
final class ValidateSiteSpecCapabilityAction implements CapellAgentBridgeCapabilityAction
{
    public function preview(CapabilityInvocationData $invocation): CapabilityResultData
    {
        return $this->result($invocation);
    }

    public function execute(CapabilityInvocationData $invocation): CapabilityResultData
    {
        return $this->result($invocation);
    }

    private function result(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $spec = Arr::get($invocation->payload, 'spec', []);

        $verdict = ValidateSiteSpecAction::run(is_array($spec) ? $spec : []);

        return new CapabilityResultData(
            ok: true,
            message: $verdict['valid'] ? 'Spec is valid.' : 'Spec failed validation.',
            data: $verdict,
        );
    }
}
