<?php

declare(strict_types=1);

namespace Capell\Contacts\Listeners;

use Capell\Contacts\Actions\SyncCampaignConversionContactAction;
use Capell\Contacts\Listeners\Concerns\RunsQueuedContactSourceSync;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

final class SyncContactFromCampaignConversion implements ShouldQueue
{
    use Queueable;
    use RunsQueuedContactSourceSync;

    public function handle(object $event): void
    {
        $this->runQueuedContactSourceSync(static fn (): mixed => SyncCampaignConversionContactAction::run($event));
    }
}
