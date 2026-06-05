<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Data\CreateKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Data\CreateKnowledgeBaseArticleVersionData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static KnowledgeBaseArticle run(CreateKnowledgeBaseArticleData $data)
 */
final class CreateKnowledgeBaseArticleAction
{
    use AsObject;

    public function handle(CreateKnowledgeBaseArticleData $data): KnowledgeBaseArticle
    {
        $title = trim($data->title);
        $body = trim($data->body);
        $slug = Str::slug(trim($data->slug ?? $title));

        if ($title === '') {
            throw ValidationException::withMessages([
                'title' => __('capell-knowledge-base::generic.validation.article_title_required'),
            ]);
        }

        if ($body === '') {
            throw ValidationException::withMessages([
                'body' => __('capell-knowledge-base::generic.validation.article_body_required'),
            ]);
        }

        if ($slug === '') {
            throw ValidationException::withMessages([
                'slug' => __('capell-knowledge-base::generic.validation.slug_required'),
            ]);
        }

        if ($this->slugExistsInCollection($data, $slug)) {
            throw ValidationException::withMessages([
                'slug' => __('capell-knowledge-base::generic.validation.slug_unique'),
            ]);
        }

        return DB::transaction(function () use ($data, $title, $slug): KnowledgeBaseArticle {
            $article = KnowledgeBaseArticle::query()->create([
                'collection_id' => $data->collection->getKey(),
                'title' => $title,
                'slug' => $slug,
                'summary' => $data->summary === null ? null : trim($data->summary),
                'status' => KnowledgeBaseArticleStatus::Draft,
                'search_weight' => min(1000, max(0, $data->searchWeight)),
                'is_ai_readable' => $data->isAiReadable,
                'published_at' => null,
            ]);

            $version = CreateKnowledgeBaseArticleVersionAction::run(new CreateKnowledgeBaseArticleVersionData(
                article: $article,
                version: $data->version,
                title: $title,
                body: $data->body,
                summary: $data->summary,
                author: $data->author,
            ));

            $article->forceFill([
                'current_version_id' => $version->getKey(),
            ])->save();

            if ($data->status === KnowledgeBaseArticleStatus::Published) {
                return PublishKnowledgeBaseArticleVersionAction::run($version);
            }

            return $article->refresh()->load(['collection', 'currentVersion']);
        });
    }

    private function slugExistsInCollection(CreateKnowledgeBaseArticleData $data, string $slug): bool
    {
        return KnowledgeBaseArticle::query()
            ->whereBelongsTo($data->collection, 'collection')
            ->where('slug', $slug)
            ->exists();
    }
}
