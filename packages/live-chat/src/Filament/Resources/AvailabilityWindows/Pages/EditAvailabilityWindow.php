<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\AvailabilityWindows\Pages;

use Capell\LiveChat\Filament\Resources\AvailabilityWindows\AvailabilityWindowResource;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditAvailabilityWindow extends EditRecord
{
    protected static string $resource = AvailabilityWindowResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return AvailabilityWindowResource::prepareFormDataForPersistence($data);
    }
}
