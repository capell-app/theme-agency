<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Filament\Resources\Collections\Pages;

use Capell\KnowledgeBase\Filament\Resources\Collections\KnowledgeBaseCollectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListKnowledgeBaseCollections extends ListRecords
{
    protected static string $resource = KnowledgeBaseCollectionResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('capell-knowledge-base::generic.admin.actions.create_collection')),
        ];
    }
}
