<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Events\Models\Event;
use Capell\Events\Models\EventOccurrence;
use Lorisleiva\Actions\Concerns\AsAction;

class ResolvePublicEventSchemaOccurrenceAction
{
    use AsAction;

    public function handle(Event $event): ?EventOccurrence
    {
        return EventOccurrence::query()
            ->with(['event.pageUrl', 'event.translation', 'venue'])
            ->where('event_id', $event->getKey())
            ->public()
            ->ordered()
            ->first();
    }
}
