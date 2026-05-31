<?php

declare(strict_types=1);

namespace Capell\AccessGate\Filament\Resources\AccessAreas\Pages;

use Capell\AccessGate\Filament\Resources\AccessAreas\AccessAreaResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateAccessArea extends CreateRecord
{
    protected static string $resource = AccessAreaResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return AccessAreaResource::prepareFormDataForPersistence($data);
    }
}
