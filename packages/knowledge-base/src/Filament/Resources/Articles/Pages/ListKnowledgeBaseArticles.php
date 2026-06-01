<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Filament\Resources\Articles\Pages;

use Capell\KnowledgeBase\Filament\Resources\Articles\KnowledgeBaseArticleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListKnowledgeBaseArticles extends ListRecords
{
    protected static string $resource = KnowledgeBaseArticleResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label(__('capell-knowledge-base::generic.admin.actions.create_article')),
        ];
    }
}
