<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AiCreator\Actions\PreviewAiCreatorSessionAction;
use Capell\AiCreator\Models\AiCreatorSession;
use Illuminate\Support\Arr;

final class PreviewAiCreatorSessionCapabilityAction implements CapellAgentBridgeCapabilityAction
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
        $session = $this->sessionFromPayload($invocation->payload);
        $preview = PreviewAiCreatorSessionAction::run($session);

        return new CapabilityResultData(
            ok: true,
            message: __('capell-ai-creator::package.capability_preview_ready'),
            data: $preview->toPayload(),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function sessionFromPayload(array $payload): AiCreatorSession
    {
        $sessionId = Arr::get($payload, 'session_id');

        return AiCreatorSession::query()->findOrFail((int) $sessionId);
    }
}
