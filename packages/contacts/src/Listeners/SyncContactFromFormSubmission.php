<?php

declare(strict_types=1);

namespace Capell\Contacts\Listeners;

use Capell\Contacts\Actions\SyncFormSubmissionContactAction;

final class SyncContactFromFormSubmission
{
    public function handle(object $event): void
    {
        SyncFormSubmissionContactAction::run($event);
    }
}
