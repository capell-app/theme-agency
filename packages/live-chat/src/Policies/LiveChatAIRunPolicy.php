<?php

declare(strict_types=1);

namespace Capell\LiveChat\Policies;

final class LiveChatAIRunPolicy extends AbstractLiveChatResourcePolicy
{
    protected static function subject(): string
    {
        return 'LiveChatAIRun';
    }
}
