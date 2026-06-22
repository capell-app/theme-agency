<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AiCreator\Actions\StartAiCreatorSessionAction;
use Capell\AiCreator\Data\AiCreatorStartSessionData;
use Illuminate\Support\Arr;

final class StartAiCreatorSessionCapabilityAction implements CapellAgentBridgeCapabilityAction
{
    public function preview(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $intent = $this->intentFromPayload($invocation->payload);

        return new CapabilityResultData(
            ok: true,
            message: __('capell-ai-creator::package.capability_start_preview'),
            data: [
                'intent' => $intent,
                'willCreateSession' => true,
            ],
        );
    }

    public function execute(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $payload = $invocation->payload;
        $session = StartAiCreatorSessionAction::run(new AiCreatorStartSessionData(
            intent: $this->intentFromPayload($payload),
            siteId: $this->nullableInteger($payload, 'site_id'),
            workspaceId: $this->nullableInteger($payload, 'workspace_id'),
            userId: $invocation->user !== null ? (int) $invocation->user->getAuthIdentifier() : $this->nullableInteger($payload, 'user_id'),
            answers: $this->arrayPayload($payload, 'answers'),
        ));

        return new CapabilityResultData(
            ok: true,
            message: __('capell-ai-creator::package.capability_start_executed'),
            data: [
                'sessionId' => (int) $session->getKey(),
                'recommendations' => $session->package_recommendations ?? [],
                'requiredPackages' => $session->package_requirements ?? [],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function intentFromPayload(array $payload): string
    {
        $intent = Arr::get($payload, 'intent');

        return is_string($intent) && trim($intent) !== '' ? trim($intent) : 'Create Capell content';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function nullableInteger(array $payload, string $key): ?int
    {
        $value = Arr::get($payload, $key);

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function arrayPayload(array $payload, string $key): array
    {
        $value = Arr::get($payload, $key, []);

        return is_array($value) ? $value : [];
    }
}
