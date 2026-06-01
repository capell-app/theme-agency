<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Filament\Resources\Collections\Pages;

use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseCollectionAction;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseCollectionData;
use Capell\KnowledgeBase\Filament\Resources\Collections\KnowledgeBaseCollectionResource;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

final class CreateKnowledgeBaseCollection extends CreateRecord
{
    protected static string $resource = KnowledgeBaseCollectionResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    #[Override]
    protected function handleRecordCreation(array $data): Model
    {
        $parent = isset($data['parent_id']) && $data['parent_id'] !== null && $data['parent_id'] !== ''
            ? KnowledgeBaseCollection::query()->find((int) $data['parent_id'])
            : null;

        return CreateKnowledgeBaseCollectionAction::run(new CreateKnowledgeBaseCollectionData(
            title: (string) ($data['title'] ?? ''),
            slug: isset($data['slug']) ? (string) $data['slug'] : null,
            key: isset($data['key']) ? (string) $data['key'] : null,
            description: isset($data['description']) ? (string) $data['description'] : null,
            parent: $parent,
            sortOrder: isset($data['sort_order']) ? (int) $data['sort_order'] : 0,
            isPublic: (bool) ($data['is_public'] ?? true),
        ));
    }

    #[Override]
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('capell-knowledge-base::generic.admin.actions.create_collection'));
    }
}
