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
use Throwable;

final class DispatchWebhookAutomationActionHandler implements AutomationActionHandler
{
    private const string SubmitPublicActionAction = SubmitPublicActionAction::class;

    #[Override]
    public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
    {
        if (! class_exists(self::SubmitPublicActionAction)) {
            return new AutomationActionResultData(
                success: false,
                message: $this->message('capell-automation-studio::generic.dispatcher.public_actions_unavailable', 'Public Actions is not available.'),
            );
        }

        $webhookActionKey = $this->webhookActionKey($action);

        if ($webhookActionKey === null) {
            return new AutomationActionResultData(
                success: false,
                message: $this->message('capell-automation-studio::generic.dispatcher.webhook_action_key_required', 'A Public Action webhook key is required.'),
            );
        }

        $submitPublicActionAction = self::SubmitPublicActionAction;
        $result = $submitPublicActionAction::run($webhookActionKey, [
            ...$event->payload,
            ...$this->configuredPayload($action),
            'source_type' => $event->sourceType,
            'source_id' => $event->sourceId,
            'automation_action_key' => $action->key,
        ]);

        return new AutomationActionResultData(
            success: (bool) data_get($result, 'success', false),
            message: is_string(data_get($result, 'message')) ? data_get($result, 'message') : null,
            context: [
                'webhook_action_key' => $webhookActionKey,
                'redirect_url' => data_get($result, 'redirectUrl'),
            ],
        );
    }

    private function webhookActionKey(AutomationRuleActionData $action): ?string
    {
        $key = $action->settings['webhook_action_key']
            ?? $action->settings['public_action_key']
            ?? $action->settings['action_key']
            ?? null;

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

    private function message(string $key, string $fallback): string
    {
        try {
            if (function_exists('app') && app()->bound('translator')) {
                return __($key);
            }
        } catch (Throwable) {
            //
        }

        return $fallback;
    }
}
