<?php

declare(strict_types=1);

namespace Capell\Contacts\Policies;

use Capell\Contacts\Models\Contact;
use Illuminate\Foundation\Auth\User;

final class ContactPolicy extends AbstractContactsResourcePolicy
{
    public function exportPrivacy(User $user, Contact $record): bool
    {
        return true;
    }

    public function anonymizePrivacy(User $user, Contact $record): bool
    {
        return true;
    }
}
