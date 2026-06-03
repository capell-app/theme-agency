<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\Pages;

use Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\CampaignCtaWidgetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListCampaignCtaWidgets extends ListRecords
{
    protected static string $resource = CampaignCtaWidgetResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
