<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Policies;

use Override;

final class AutomationRulePolicy extends AbstractAutomationStudioResourcePolicy
{
    #[Override]
    protected static function subject(): string
    {
        return 'AutomationRule';
    }
}
