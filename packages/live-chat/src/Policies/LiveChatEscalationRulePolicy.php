<?php

declare(strict_types=1);

namespace Capell\LiveChat\Policies;

final class LiveChatEscalationRulePolicy extends AbstractLiveChatResourcePolicy
{
    protected static function subject(): string
    {
        return 'LiveChatEscalationRule';
    }
}
