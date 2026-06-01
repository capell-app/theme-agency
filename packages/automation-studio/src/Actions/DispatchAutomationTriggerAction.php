<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Actions;

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;
use Capell\AutomationStudio\Support\AutomationActionRegistry;
use Capell\AutomationStudio\Support\AutomationRuleRegistry;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class DispatchAutomationTriggerAction
{
    use AsAction;

    public function __construct(
        private readonly AutomationRuleRegistry $rules,
        private readonly AutomationActionRegistry $actions,
    ) {}

    /**
     * @return list<AutomationActionResultData>
     */
    public function handle(AutomationTriggerEventData $event): array
    {
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
                    $result = new AutomationActionResultData(
                        success: false,
                        message: $exception->getMessage(),
                        context: [
                            'rule_key' => $rule->key,
                            'action_key' => $ruleAction->key,
                            'error_type' => $exception::class,
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
}
