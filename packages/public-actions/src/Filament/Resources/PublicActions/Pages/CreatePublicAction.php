<?php

declare(strict_types=1);

namespace Capell\PublicActions\Filament\Resources\PublicActions\Pages;

use Capell\PublicActions\Filament\Resources\PublicActions\PublicActionResource;
use Filament\Resources\Pages\CreateRecord;
use Override;

final class CreatePublicAction extends CreateRecord
{
    protected static string $resource = PublicActionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return PublicActionResource::prepareFormDataForPersistence($data);
    }
}
