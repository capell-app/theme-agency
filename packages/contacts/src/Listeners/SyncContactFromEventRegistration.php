<?php

declare(strict_types=1);

namespace Capell\Contacts\Listeners;

use Capell\Contacts\Actions\SyncEventRegistrationContactAction;
use Capell\Contacts\Listeners\Concerns\RunsQueuedContactSourceSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SyncContactFromEventRegistration implements ShouldQueue
{
    use Queueable;
    use RunsQueuedContactSourceSync;

    public function handle(object $event): void
    {
        $this->runQueuedContactSourceSync(static fn (): mixed => SyncEventRegistrationContactAction::run($event));
    }
}
