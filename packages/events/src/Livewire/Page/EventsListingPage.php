<?php

declare(strict_types=1);

namespace Capell\Events\Livewire\Page;

use Capell\Core\Models\Site;
use Capell\Events\Actions\QueryPublicEventOccurrencesAction;
use Capell\Frontend\Facades\Frontend;
use Capell\Frontend\Livewire\Page\AbstractPage;
use Carbon\CarbonImmutable;
use RuntimeException;

class EventsListingPage extends AbstractPage
{
    protected static string $defaultView = 'capell-events::livewire.page.events-listing';

    protected function setup(): void
    {
        $now = CarbonImmutable::now();
        $site = Frontend::site();

        throw_unless($site instanceof Site, RuntimeException::class, 'Events listing requires a frontend site context.');

        $this->results = QueryPublicEventOccurrencesAction::run(
            $site,
            $now->subDay(),
            $now->addYear(),
            (int) (Frontend::page()->meta['limit'] ?? config('capell-frontend.pagination_limit', 12)),
        );
    }
}
