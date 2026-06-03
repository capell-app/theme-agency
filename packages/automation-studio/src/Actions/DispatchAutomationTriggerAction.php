<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Actions;

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Lorisleiva\Actions\Concerns\AsAction;
use Psr\Log\LoggerInterface;
use Throwable;

final class DispatchAutomationTriggerAction
{
    use AsAction;

    public function __construct(
        private readonly AutomationRuleRegistry $rules,
        private readonly AutomationActionRegistry $actions,
        private readonly ?PersistAutomationTriggerResultsAction $persistResults = null,
        private readonly ?LoggerInterface $logger = null,
    ) {}

    /**
     * @return list<AutomationActionResultData>
     */
    public function handle(
        AutomationTriggerEventData $event,
        ?string $idempotencyKey = null,
        ?int $siteId = null,
        int $attemptNumber = 1,
        ?int $maxAttempts = null,
    ): array {
        $results = [];

        foreach ($this->rules->matching($event) as $rule) {
            foreach ($rule->actions as $ruleAction) {
                $handler = $this->actions->handler($ruleAction->type);

                if (! $handler instanceof AutomationActionHandler) {
                    $results[] = new AutomationActionResultData(
                        success: false,
                        message: $this->handlerMissingMessage($ruleAction->type->value),
                        context: [
                            'rule_key' => $rule->key,
                            'action_key' => $ruleAction->key,
                        ],
                    );

                    continue;
                }

                try {
                    $result = $handler->handle($event, $ruleAction);
                } catch (Throwable $exception) {
                    $this->logger?->warning('Automation Studio action handler failed.', [
                        'exception' => $exception,
                        'rule_key' => $rule->key,
                        'action_key' => $ruleAction->key,
                        'action_type' => $ruleAction->type->value,
                        'trigger_type' => $event->triggerType->value,
                        'source_type' => $event->sourceType,
                        'source_id' => $event->sourceId,
                    ]);

                    $result = new AutomationActionResultData(
                        success: false,
                        message: $this->handlerFailedMessage($ruleAction->type->value),
                        context: [
                            'rule_key' => $rule->key,
                            'action_key' => $ruleAction->key,
                            'error' => 'handler_failed',
                        ],
                    );
                }

                $results[] = new AutomationActionResultData(
                    success: $result->success,
                    message: $result->message,
                    context: [
                        ...$result->context,
                        'rule_key' => $rule->key,
                        'action_key' => $ruleAction->key,
                    ],
                );
            }
        }

        $this->persistResults?->handle(
            event: $event,
            results: $results,
            idempotencyKey: $idempotencyKey,
            siteId: $siteId,
            attemptNumber: $attemptNumber,
            maxAttempts: $maxAttempts,
        );

        return $results;
    }

    private function handlerMissingMessage(string $actionType): string
    {
        try {
            if (function_exists('app') && app()->bound('translator')) {
                return __('capell-automation-studio::generic.dispatcher.handler_missing', ['action' => $actionType]);
            }
        } catch (Throwable) {
            //
        }

        return sprintf('No Automation Studio handler is registered for %s.', $actionType);
    }

    private function handlerFailedMessage(string $actionType): string
    {
        try {
            if (function_exists('app') && app()->bound('translator')) {
                return __('capell-automation-studio::generic.dispatcher.handler_failed', ['action' => $actionType]);
            }
        } catch (Throwable) {
            //
        }

        return sprintf('Automation Studio action %s failed. Check the application logs for details.', $actionType);
    }
}
