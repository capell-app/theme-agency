<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\Pages;

use Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\CampaignCtaWidgetResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateCampaignCtaWidget extends CreateRecord
{
    protected static string $resource = CampaignCtaWidgetResource::class;
}
