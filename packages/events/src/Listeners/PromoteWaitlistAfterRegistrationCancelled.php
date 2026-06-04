<?php

declare(strict_types=1);

namespace Capell\Events\Listeners;

use Capell\Events\Actions\PromoteWaitlistAction;
use Capell\Events\Events\EventRegistrationCancelled;
use Capell\Events\Models\EventOccurrence;

final class PromoteWaitlistAfterRegistrationCancelled
{
    public function handle(EventRegistrationCancelled $event): void
    {
        $occurrence = $event->registration->occurrence;

        if (! $occurrence instanceof EventOccurrence) {
            return;
        }

        PromoteWaitlistAction::run($occurrence);
    }
}
