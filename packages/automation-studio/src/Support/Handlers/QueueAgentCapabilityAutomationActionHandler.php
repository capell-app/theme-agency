<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Support\Handlers;

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Support\Handlers\Concerns\ResolvesContacts;
use Override;
use Throwable;

final class QueueAgentCapabilityAutomationActionHandler implements AutomationActionHandler
{
    use ResolvesContacts;

    #[Override]
    public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
    {
        $invokeActionClass = 'Capell\\AgentBridge\\Actions\\InvokeAgentBridgeCapabilityPreviewAction';
        $authenticatedClientDataClass = 'Capell\\AgentBridge\\Data\\AuthenticatedAgentBridgeClientData';

        if (! class_exists($invokeActionClass) || ! class_exists($authenticatedClientDataClass)) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.agent_bridge_unavailable', 'Agent Bridge is not available.'),
            );
        }

        $capabilityKey = $this->capabilityKey($action);

        if ($capabilityKey === null) {
            return new AutomationActionResultData(
                success: false,
                message: $this->automationMessage('capell-automation-studio::generic.dispatcher.agent_capability_key_required', 'An Agent Bridge capability key is required.'),
            );
        }

        try {
            /** @var array<string, mixed> $result */
            $result = $invokeActionClass::run(
                capabilityKey: $capabilityKey,
                payload: $this->payload($event, $action),
                client: $this->client($action, $authenticatedClientDataClass),
                token: null,
                user: null,
            );
        } catch (Throwable $throwable) {
            return new AutomationActionResultData(
                success: false,
                message: $throwable->getMessage(),
                context: [
                    'error_type' => $throwable::class,
                ],
            );
        }

        return new AutomationActionResultData(
            success: true,
            message: is_string($result['mode'] ?? null) ? $result['mode'] : null,
            context: [
                'agent_bridge' => $result,
            ],
        );
    }

    private function capabilityKey(AutomationRuleActionData $action): ?string
    {
        $capabilityKey = $action->settings['capability_key'] ?? $action->settings['capability'] ?? null;

        return is_string($capabilityKey) && trim($capabilityKey) !== '' ? trim($capabilityKey) : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(AutomationTriggerEventData $event, AutomationRuleActionData $action): array
    {
        $payload = $action->settings['payload'] ?? [];
        $payload = is_array($payload) ? $payload : [];

        return [
            ...$event->payload,
            ...$payload,
            'automation' => [
                'trigger_type' => $event->triggerType->value,
                'source_type' => $event->sourceType,
                'source_id' => $event->sourceId,
                'action_key' => $action->key,
            ],
        ];
    }

    /**
     * @param  class-string  $authenticatedClientDataClass
     */
    private function client(AutomationRuleActionData $action, string $authenticatedClientDataClass): ?object
    {
        $scopes = $action->settings['client_scopes'] ?? null;

        if (! is_array($scopes)) {
            return null;
        }

        $scopes = collect($scopes)
            ->filter(fn (mixed $scope): bool => is_string($scope) && trim($scope) !== '')
            ->map(fn (string $scope): string => trim($scope))
            ->values()
            ->all();

        if ($scopes === []) {
            return null;
        }

        return new $authenticatedClientDataClass(
            tokenId: 0,
            name: 'Automation Studio',
            scopes: $scopes,
        );
    }
}
