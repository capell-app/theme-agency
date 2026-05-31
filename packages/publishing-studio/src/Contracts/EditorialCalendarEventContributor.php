<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Contracts;

use Capell\PublishingStudio\Data\EditorialCalendarEventData;
use Capell\PublishingStudio\Data\EditorialCalendarQueryData;

interface EditorialCalendarEventContributor
{
    public const string TAG = 'capell.publishing-studio.editorial-calendar-event-contributor';

    /**
     * @return iterable<EditorialCalendarEventData>
     */
    public function editorialCalendarEvents(EditorialCalendarQueryData $query): iterable;
}
