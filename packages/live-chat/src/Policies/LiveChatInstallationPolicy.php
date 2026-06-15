<?php

declare(strict_types=1);

namespace Capell\LiveChat\Policies;

final class LiveChatInstallationPolicy extends AbstractLiveChatResourcePolicy
{
    protected static function subject(): string
    {
        return 'LiveChatInstallation';
    }
}
