<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Events;

use Capell\CampaignStudio\Models\CampaignConversion;
use Illuminate\Foundation\Events\Dispatchable;

final class CampaignConverted
{
    use Dispatchable;

    public function __construct(
        public readonly CampaignConversion $conversion,
    ) {}
}
