<?php

declare(strict_types=1);

namespace Capell\Newsletter\Filament\Resources\ProviderConnections\Pages;

use Capell\Newsletter\Filament\Resources\ProviderConnections\ProviderConnectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Override;

class EditProviderConnection extends EditRecord
{
    protected static string $resource = ProviderConnectionResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['credentials'] = [];

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $submittedCredentials = is_array($data['credentials'] ?? null) ? $data['credentials'] : [];

        if ($submittedCredentials === []) {
            unset($data['credentials']);
        }

        return $data;
    }
}
