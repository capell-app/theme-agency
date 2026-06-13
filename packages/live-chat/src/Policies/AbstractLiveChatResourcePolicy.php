<?php

declare(strict_types=1);

namespace Capell\LiveChat\Policies;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

abstract class AbstractLiveChatResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Model $record): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Model $record): bool
    {
        return true;
    }

    public function delete(User $user, Model $record): bool
    {
        return true;
    }
}
