<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\Installations\Pages;

use Capell\LiveChat\Filament\Resources\Installations\InstallationResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateInstallation extends CreateRecord
{
    protected static string $resource = InstallationResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return InstallationResource::prepareFormDataForPersistence($data);
    }
}
