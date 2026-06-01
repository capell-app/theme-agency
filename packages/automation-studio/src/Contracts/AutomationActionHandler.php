<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Contracts;

use Capell\AutomationStudio\Data\AutomationActionResultData;
use Capell\AutomationStudio\Data\AutomationRuleActionData;
use Capell\AutomationStudio\Data\AutomationTriggerEventData;

interface AutomationActionHandler
{
    public function handle(AutomationTriggerEventData $event, AutomationRuleActionData $action): AutomationActionResultData;
}
