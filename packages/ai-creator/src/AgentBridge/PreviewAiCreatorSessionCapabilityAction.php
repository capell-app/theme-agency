<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AiCreator\Actions\BuildAiCreatorSessionPreviewAction;
use Capell\AiCreator\Models\AiCreatorSession;
use Capell\AiCreator\Support\AiCreatorSessionAccess;
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
        AiCreatorSessionAccess::authorizeSession($invocation->user, $session);
        $preview = BuildAiCreatorSessionPreviewAction::make()->handle($session);

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
        $validated = validator($payload, [
            'session_id' => ['required', 'integer'],
        ])->validate();

        $sessionId = Arr::get($validated, 'session_id');

        return AiCreatorSession::query()->findOrFail(is_numeric($sessionId) ? (int) $sessionId : 0);
    }
}
