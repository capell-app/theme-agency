<?php

declare(strict_types=1);

namespace Capell\AiCreator\AgentBridge;

use Capell\AgentBridge\Contracts\CapellAgentBridgeCapabilityAction;
use Capell\AgentBridge\Data\CapabilityInvocationData;
use Capell\AgentBridge\Data\CapabilityResultData;
use Capell\AiCreator\Actions\StartAiCreatorSessionAction;
use Capell\AiCreator\Data\AiCreatorStartSessionData;
use Capell\AiCreator\Support\AiCreatorSessionAccess;

final class StartAiCreatorSessionCapabilityAction implements CapellAgentBridgeCapabilityAction
{
    public function preview(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $payload = $this->validatedPayload($invocation->payload);
        AiCreatorSessionAccess::authorizeStart($invocation->user, $payload['site_id'] ?? null);

        return new CapabilityResultData(
            ok: true,
            message: __('capell-ai-creator::package.capability_start_preview'),
            data: [
                'intent' => $payload['intent'],
                'willCreateSession' => true,
            ],
        );
    }

    public function execute(CapabilityInvocationData $invocation): CapabilityResultData
    {
        $payload = $this->validatedPayload($invocation->payload);
        AiCreatorSessionAccess::authorizeStart($invocation->user, $payload['site_id'] ?? null);

        $userIdentifier = $invocation->user?->getAuthIdentifier();

        $session = StartAiCreatorSessionAction::make()->handle(new AiCreatorStartSessionData(
            intent: $payload['intent'],
            siteId: $payload['site_id'] ?? null,
            workspaceId: $payload['workspace_id'] ?? null,
            userId: is_numeric($userIdentifier) ? (int) $userIdentifier : null,
            answers: $payload['answers'] ?? [],
        ));

        return new CapabilityResultData(
            ok: true,
            message: __('capell-ai-creator::package.capability_start_executed'),
            data: [
                'sessionId' => $session->id,
                'recommendations' => $session->package_recommendations ?? [],
                'requiredPackages' => $session->package_requirements ?? [],
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{intent: string, site_id?: int|null, workspace_id?: int|null, answers?: array<string, mixed>}
     */
    private function validatedPayload(array $payload): array
    {
        $validated = validator($payload, [
            'intent' => ['required', 'string', 'max:2000'],
            'site_id' => ['nullable', 'integer'],
            'workspace_id' => ['nullable', 'integer'],
            'answers' => ['nullable', 'array'],
        ])->validate();

        /** @var array{intent: string, site_id?: int|null, workspace_id?: int|null, answers?: array<string, mixed>} $validated */
        $validated['intent'] = trim($validated['intent']);

        if ($validated['intent'] === '') {
            validator(['intent' => null], ['intent' => ['required']])->validate();
        }

        if (array_key_exists('site_id', $validated) && $validated['site_id'] !== null) {
            $validated['site_id'] = (int) $validated['site_id'];
        }

        if (array_key_exists('workspace_id', $validated) && $validated['workspace_id'] !== null) {
            $validated['workspace_id'] = (int) $validated['workspace_id'];
        }

        return $validated;
    }
}
