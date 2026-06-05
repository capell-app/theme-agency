<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Data\AiReadableKnowledgeBaseArticleData;
use Capell\KnowledgeBase\Enums\KnowledgeBaseArticleStatus;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleVersion;
use Capell\KnowledgeBase\Models\KnowledgeBaseCollection;
use Capell\KnowledgeBase\Support\KnowledgeBasePublicPath;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsObject;
use RuntimeException;

/**
 * @method static Collection<int, AiReadableKnowledgeBaseArticleData> run()
 */
final class BuildAiReadableKnowledgeBaseOutputAction
{
    use AsObject;

    /**
     * @return Collection<int, AiReadableKnowledgeBaseArticleData>
     */
    public function handle(): Collection
    {
        return KnowledgeBaseArticle::query()
            ->where('status', KnowledgeBaseArticleStatus::Published->value)
            ->where('is_ai_readable', true)
            ->whereNotNull('published_at')
            ->whereNotNull('current_version_id')
            ->whereHas('collection', fn (Builder $query): Builder => $query->where('is_public', true))
            ->with(['collection', 'currentVersion'])
            ->orderBy('title')
            ->get()
            ->filter(fn (KnowledgeBaseArticle $article): bool => $article->collection instanceof KnowledgeBaseCollection && $article->currentVersion instanceof KnowledgeBaseArticleVersion)
            ->map(function (KnowledgeBaseArticle $article): AiReadableKnowledgeBaseArticleData {
                $currentVersion = $article->currentVersion;

                throw_unless($currentVersion instanceof KnowledgeBaseArticleVersion, RuntimeException::class, 'Published knowledge base article is missing its current version.');

                return new AiReadableKnowledgeBaseArticleData(
                    title: $currentVersion->title,
                    publicPath: KnowledgeBasePublicPath::forArticle($article),
                    summary: $currentVersion->summary,
                    content: $this->plainText($currentVersion->body),
                    version: $currentVersion->version,
                    lastModified: $currentVersion->published_at ?? $article->published_at,
                );
            });
    }

    private function plainText(string $html): string
    {
        $html = (string) preg_replace('/>\s*</', '> <', $html);

        return trim(html_entity_decode((string) preg_replace('/\s+/', ' ', strip_tags($html)), ENT_QUOTES | ENT_HTML5));
    }
}
