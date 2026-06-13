<?php

declare(strict_types=1);

namespace Capell\LiveChat\Filament\Resources\KnowledgeSources\Pages;

use Capell\LiveChat\Filament\Resources\KnowledgeSources\KnowledgeSourceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListKnowledgeSources extends ListRecords
{
    protected static string $resource = KnowledgeSourceResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
