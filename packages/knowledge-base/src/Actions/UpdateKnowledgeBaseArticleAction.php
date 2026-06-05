<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Data\CreateKnowledgeBaseArticleVersionData;
use Capell\KnowledgeBase\Data\UpdateKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsObject;

final class UpdateKnowledgeBaseArticleAction
{
    use AsObject;

    public function handle(KnowledgeBaseArticle $article, UpdateKnowledgeBaseArticleData $data): KnowledgeBaseArticle
    {
        $title = trim($data->title);
        $body = trim($data->body);
        $slug = Str::slug(trim($data->slug ?? $title));
        $version = trim((string) $data->version);

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

        if ($this->slugExistsInCollection($article, $data, $slug)) {
            throw ValidationException::withMessages([
                'slug' => __('capell-knowledge-base::generic.validation.slug_unique'),
            ]);
        }

        $version = $version === '' ? $this->nextVersionLabel($article) : $version;

        return DB::transaction(function () use ($article, $body, $data, $slug, $title, $version): KnowledgeBaseArticle {
            $article->forceFill([
                'collection_id' => $data->collection->getKey(),
                'title' => $title,
                'slug' => $slug,
                'summary' => $data->summary === null ? null : trim($data->summary),
                'status' => $data->status,
                'search_weight' => min(1000, max(0, $data->searchWeight)),
                'is_ai_readable' => $data->isAiReadable,
                'published_at' => $data->status === KnowledgeBaseArticleStatus::Published ? $article->published_at : null,
            ])->save();

            $newVersion = (new CreateKnowledgeBaseArticleVersionAction)->handle(new CreateKnowledgeBaseArticleVersionData(
                article: $article,
                version: $version,
                title: $title,
                body: $body,
                summary: $data->summary,
                author: $data->author,
            ));

            if ($data->status === KnowledgeBaseArticleStatus::Published) {
                return (new PublishKnowledgeBaseArticleVersionAction)->handle($newVersion);
            }

            $article->forceFill([
                'current_version_id' => $newVersion->getKey(),
            ])->save();

            return $article->refresh()->load(['collection', 'currentVersion']);
        });
    }

    private function slugExistsInCollection(KnowledgeBaseArticle $article, UpdateKnowledgeBaseArticleData $data, string $slug): bool
    {
        return KnowledgeBaseArticle::query()
            ->whereBelongsTo($data->collection, 'collection')
            ->where('slug', $slug)
            ->whereKeyNot($article->getKey())
            ->exists();
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
