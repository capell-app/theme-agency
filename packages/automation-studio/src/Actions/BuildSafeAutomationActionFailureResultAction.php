<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Actions;

use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Enums\AutomationActionType;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class BuildSafeAutomationActionFailureResultAction
{
    use AsAction;

    public function handle(AutomationActionType|string $actionType): AutomationActionResultData
    {
        $actionType = $actionType instanceof AutomationActionType ? $actionType->value : $actionType;

        return new AutomationActionResultData(
            success: false,
            message: $this->message($actionType),
            context: [
                'error' => 'handler_failed',
            ],
        );
    }

    private function message(string $actionType): string
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
