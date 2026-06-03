<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\Pages;

use Capell\CampaignStudio\Filament\Resources\CampaignCtaWidgets\CampaignCtaWidgetResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditCampaignCtaWidget extends EditRecord
{
    protected static string $resource = CampaignCtaWidgetResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
