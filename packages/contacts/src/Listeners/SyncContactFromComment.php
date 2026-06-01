<?php

declare(strict_types=1);

namespace Capell\Contacts\Listeners;

use Capell\Contacts\Actions\SyncCommentContactAction;

final class SyncContactFromComment
{
    public function handle(object $event): void
    {
        SyncCommentContactAction::run($event);
    }
}
