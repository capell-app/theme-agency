<?php

declare(strict_types=1);

namespace Capell\Contacts\Listeners;

use Capell\Contacts\Actions\SyncCampaignConversionContactAction;

final class SyncContactFromCampaignConversion
{
    public function handle(object $event): void
    {
        SyncCampaignConversionContactAction::run($event);
    }
}
