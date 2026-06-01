<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Filament\Resources\Articles\Pages;

use Capell\KnowledgeBase\Actions\CreateKnowledgeBaseArticleAction;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Filament\Resources\Articles\KnowledgeBaseArticleResource;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

final class CreateKnowledgeBaseArticle extends CreateRecord
{
    protected static string $resource = KnowledgeBaseArticleResource::class;

    /**
     * @param  array<string, mixed>  $data
     */
    #[Override]
    protected function handleRecordCreation(array $data): Model
    {
        /** @var KnowledgeBaseCollection $collection */
        $collection = KnowledgeBaseCollection::query()->findOrFail((int) ($data['collection_id'] ?? 0));

        return CreateKnowledgeBaseArticleAction::run(new CreateKnowledgeBaseArticleData(
            collection: $collection,
            title: (string) ($data['title'] ?? ''),
            body: (string) ($data['body'] ?? ''),
            slug: isset($data['slug']) ? (string) $data['slug'] : null,
            summary: isset($data['summary']) ? (string) $data['summary'] : null,
            version: isset($data['version']) ? (string) $data['version'] : 'v1',
            status: KnowledgeBaseArticleStatus::tryFrom((string) ($data['status'] ?? '')) ?? KnowledgeBaseArticleStatus::Draft,
            searchWeight: isset($data['search_weight']) ? (int) $data['search_weight'] : 50,
            isAiReadable: (bool) ($data['is_ai_readable'] ?? true),
        ));
    }

    #[Override]
    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label(__('capell-knowledge-base::generic.admin.actions.create_article'));
    }
}
