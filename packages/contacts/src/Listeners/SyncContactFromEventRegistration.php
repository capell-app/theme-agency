<?php

declare(strict_types=1);

namespace Capell\Contacts\Listeners;

use Capell\Contacts\Actions\SyncEventRegistrationContactAction;

final class SyncContactFromEventRegistration
{
    public function handle(object $event): void
    {
        SyncEventRegistrationContactAction::run($event);
    }
}
