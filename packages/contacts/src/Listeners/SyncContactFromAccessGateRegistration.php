<?php

declare(strict_types=1);

namespace Capell\Contacts\Listeners;

use Capell\Contacts\Actions\SyncAccessGateRegistrationContactAction;

final class SyncContactFromAccessGateRegistration
{
    public function handle(object $event): void
    {
        SyncAccessGateRegistrationContactAction::run($event);
    }
}
