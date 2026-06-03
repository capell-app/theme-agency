<?php

declare(strict_types=1);

namespace Capell\Tests\Packages\Fixtures;

use Capell\AutomationStudio\Contracts\AutomationActionHandler;
use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;

final class CoverageGapCapturingAutomationHandler implements AutomationActionHandler
{
    /** @var list<AutomationTriggerEventData> */
    public array $events = [];

    /** @var list<AutomationRuleActionData> */
    public array $actions = [];

    public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData
    {
        $this->events[] = $event;
        $this->actions[] = $action;

        return new AutomationActionResultData(true, 'captured');
    }
}
