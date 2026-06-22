<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AiCreator\Actions\ApplyAiCreatorSessionAction;
use Capell\AiCreator\Actions\BuildAiCreatorSessionPreviewAction;
use Capell\AiCreator\Models\AiCreatorSession;
use Capell\AiCreator\Support\AiCreatorSessionAccess;
use Illuminate\Support\Arr;

final class ApplyAiCreatorSessionCapabilityAction implements CapellAgentBridgeCapabilityAction
{
    public function preview(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $session = $this->sessionFromPayload($invocation->payload);
        AiCreatorSessionAccess::authorizeSession($invocation->user, $session);
        $preview = BuildAiCreatorSessionPreviewAction::run($session);

        return new CapabilityResultData(
            ok: true,
            message: __('capell-ai-creator::package.capability_apply_preview'),
            data: $preview->toPayload(),
        );
    }

    public function execute(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $session = $this->sessionFromPayload($invocation->payload);
        AiCreatorSessionAccess::authorizeSession($invocation->user, $session);
        $session = ApplyAiCreatorSessionAction::run($session);

        return new CapabilityResultData(
            ok: true,
            message: __('capell-ai-creator::package.capability_apply_executed'),
            data: [
                'sessionId' => (int) $session->getKey(),
                'status' => $session->status->value,
                'appliedAt' => $session->applied_at?->toIso8601String(),
            ],
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

        return AiCreatorSession::query()->findOrFail((int) $sessionId);
    }
}
