<?php

declare(strict_types=1);

namespace Capell\LiveChat\Policies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

final class LiveChatConversationPolicy extends AbstractLiveChatResourcePolicy
{
    public function create(User $user): bool
    {
        unset($user);

        return false;
    }

    public function delete(User $user, Model $record): bool
    {
        unset($user, $record);

        return false;
    }

    protected static function subject(): string
    {
        return 'LiveChatConversation';
    }
}
