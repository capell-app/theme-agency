<?php

declare(strict_types=1);

namespace Capell\PublicActions\Filament\Resources\PublicActions\Pages;

use Capell\PublicActions\Filament\Resources\PublicActions\PublicActionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditPublicAction extends EditRecord
{
    protected static string $resource = PublicActionResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return PublicActionResource::prepareFormDataForPersistence($data);
    }

    #[Override]
    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
