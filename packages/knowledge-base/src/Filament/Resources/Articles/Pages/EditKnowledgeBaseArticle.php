<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Filament\Resources\Articles\Pages;

use Capell\KnowledgeBase\Actions\UpdateKnowledgeBaseArticleAction;
use Capell\KnowledgeBase\Data\UpdateKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Filament\Resources\Articles\KnowledgeBaseArticleResource;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Override;

final class EditKnowledgeBaseArticle extends EditRecord
{
    protected static string $resource = KnowledgeBaseArticleResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    #[Override]
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record instanceof KnowledgeBaseArticle) {
            $currentVersion = $this->record->currentVersion()->first();

            $data['body'] = $currentVersion->body ?? '';
            $data['version'] = $this->nextVersionLabel($this->record);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    #[Override]
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if (! $record instanceof KnowledgeBaseArticle) {
            return $record;
        }

        $collectionId = $data['collection_id'] ?? null;

        if (! is_int($collectionId) && ! is_string($collectionId)) {
            $collectionId = 0;
        }

        /** @var KnowledgeBaseCollection $collection */
        $collection = KnowledgeBaseCollection::query()->findOrFail($collectionId);

        return (new UpdateKnowledgeBaseArticleAction)->handle($record, new UpdateKnowledgeBaseArticleData(
            collection: $collection,
            title: $this->stringFromForm($data['title'] ?? null) ?? '',
            body: $this->stringFromForm($data['body'] ?? null) ?? '',
            slug: $this->stringFromForm($data['slug'] ?? null),
            summary: $this->stringFromForm($data['summary'] ?? null),
            version: $this->stringFromForm($data['version'] ?? null),
            status: KnowledgeBaseArticleStatus::tryFrom($this->stringFromForm($data['status'] ?? null) ?? '') ?? KnowledgeBaseArticleStatus::Draft,
            searchWeight: $this->integerFromForm($data['search_weight'] ?? null) ?? 50,
            isAiReadable: $this->booleanFromForm($data['is_ai_readable'] ?? true),
            author: auth()->user() instanceof Model ? auth()->user() : null,
        ));
    }

    #[Override]
    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label(__('capell-knowledge-base::generic.admin.actions.save_article'));
    }

    private function booleanFromForm(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private function stringFromForm(mixed $value): ?string
    {
        if (is_string($value) || is_numeric($value)) {
            return (string) $value;
        }

        return null;
    }

    private function integerFromForm(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) {
            return (int) $value;
        }

        return null;
    }

    private function nextVersionLabel(KnowledgeBaseArticle $article): string
    {
        $latestVersion = $article->versions()
            ->latest('id')
            ->value('version');

        if (is_string($latestVersion) && preg_match('/^v(?<number>\d+)$/', $latestVersion, $matches) === 1) {
            return 'v' . ((int) $matches['number'] + 1);
        }

        return 'v' . ($article->versions()->count() + 1);
    }
}
