<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\Installations\Pages;

use Capell\LiveChat\Filament\Resources\Installations\InstallationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListInstallations extends ListRecords
{
    protected static string $resource = InstallationResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
