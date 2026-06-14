<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\AvailabilityWindows\Pages;

use Capell\LiveChat\Filament\Resources\AvailabilityWindows\AvailabilityWindowResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreateAvailabilityWindow extends CreateRecord
{
    protected static string $resource = AvailabilityWindowResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return AvailabilityWindowResource::prepareFormDataForPersistence($data);
    }
}
