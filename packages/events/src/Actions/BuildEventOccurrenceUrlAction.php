<?php

declare(strict_types=1);

namespace Capell\Events\Actions;

use Capell\Core\Models\PageUrl;
use Capell\Events\Models\EventOccurrence;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildEventOccurrenceUrlAction
{
    use AsAction;

    public function handle(EventOccurrence $occurrence): ?string
    {
        if (! $occurrence->isPubliclyVisible()) {
            return null;
        }

        $pageUrl = $occurrence->event->getRelationValue('pageUrl');

        if (! $pageUrl instanceof PageUrl || ! $pageUrl->exists) {
            return null;
        }

        $baseUrl = $pageUrl->full_url;

        if ($baseUrl === null || trim($baseUrl) === '') {
            return null;
        }

        $dateSegment = $occurrence->starts_at
            ->setTimezone($occurrence->timezone)
            ->toDateString();

        return rtrim($baseUrl, '/') . '/' . $dateSegment;
    }
}
