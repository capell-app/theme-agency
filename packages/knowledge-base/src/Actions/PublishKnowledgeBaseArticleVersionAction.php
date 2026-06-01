<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsObject;

final class PublishKnowledgeBaseArticleVersionAction
{
    use AsObject;

    public function handle(KnowledgeBaseArticleVersion $version): KnowledgeBaseArticle
    {
        return DB::transaction(function () use ($version): KnowledgeBaseArticle {
            $publishedAt = now();

            $version->forceFill([
                'published_at' => $version->published_at ?? $publishedAt,
            ])->save();

            $article = $version->article()->lockForUpdate()->firstOrFail();

            $article->forceFill([
                'current_version_id' => $version->getKey(),
                'title' => $version->title,
                'summary' => $version->summary,
                'status' => KnowledgeBaseArticleStatus::Published,
                'published_at' => $article->published_at ?? $publishedAt,
            ])->save();

            return $article->refresh()->load(['collection', 'currentVersion']);
        });
    }
}
