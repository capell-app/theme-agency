<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\KnowledgeSources\Pages;

use Capell\LiveChat\Filament\Resources\KnowledgeSources\KnowledgeSourceResource;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditKnowledgeSource extends EditRecord
{
    protected static string $resource = KnowledgeSourceResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return KnowledgeSourceResource::prepareFormDataForPersistence($data);
    }
}
