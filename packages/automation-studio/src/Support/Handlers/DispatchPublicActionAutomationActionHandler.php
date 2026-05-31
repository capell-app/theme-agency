<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Support\Handlers;

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\PublicActions\Actions\SubmitPublicActionAction;
use Illuminate\Support\Arr;
use Override;

final class DispatchPublicActionAutomationActionHandler implements AutomationActionHandler
{
    private const string SubmitPublicActionAction = SubmitPublicActionAction::class;

    #[Override]
    public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
    {
        if (! class_exists(self::SubmitPublicActionAction)) {
            return new AutomationActionResultData(
                success: false,
                message: __('capell-automation-studio::generic.dispatcher.public_actions_unavailable'),
            );
        }

        $publicActionKey = $this->publicActionKey($action);

        if ($publicActionKey === null) {
            return new AutomationActionResultData(
                success: false,
                message: __('capell-automation-studio::generic.dispatcher.public_action_key_required'),
            );
        }

        $payload = [
            ...$event->payload,
            ...$this->configuredPayload($action),
            'source_type' => $event->sourceType,
            'source_id' => $event->sourceId,
        ];

        $submitPublicActionAction = self::SubmitPublicActionAction;
        $result = $submitPublicActionAction::run($publicActionKey, $payload);

        return new AutomationActionResultData(
            success: (bool) data_get($result, 'success', false),
            message: is_string(data_get($result, 'message')) ? data_get($result, 'message') : null,
            context: [
                'public_action_key' => $publicActionKey,
                'redirect_url' => data_get($result, 'redirectUrl'),
            ],
        );
    }

    private function publicActionKey(AutomationRuleActionData $action): ?string
    {
        $key = $action->settings['public_action_key'] ?? $action->settings['action_key'] ?? null;

        return is_string($key) && trim($key) !== '' ? $key : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function configuredPayload(AutomationRuleActionData $action): array
    {
        $payload = $action->settings['payload'] ?? [];

        return is_array($payload) ? Arr::where($payload, static fn (mixed $value): bool => $value !== null) : [];
    }
}
