<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Lorisleiva\Actions\Concerns\AsObject;

/**
 * @method static array<string, mixed>|null run(KnowledgeBaseArticle $article, ?string $canonicalUrl = null)
 */
final class BuildKnowledgeBaseArticleSchemaAction
{
    use AsObject;

    /**
     * @return array<string, mixed>|null
     */
    public function handle(KnowledgeBaseArticle $article, ?string $canonicalUrl = null): ?array
    {
        $articleData = BuildPublicKnowledgeBaseArticleDataAction::run($article);

        if ($articleData === null) {
            return null;
        }

        $url = $canonicalUrl ?? $articleData->publicPath;

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            '@id' => $url . '#article',
            'url' => $url,
            'headline' => $articleData->title,
            'description' => $articleData->summary,
            'articleBody' => $this->plainText($articleData->body),
            'dateModified' => $articleData->lastModified?->toDateString(),
            'datePublished' => $article->published_at?->toDateString(),
            'isPartOf' => [
                '@type' => 'CollectionPage',
                'name' => $articleData->collectionTitle,
            ],
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    private function plainText(string $html): string
    {
        $html = (string) preg_replace('/>\s*</', '> <', $html);

        return trim(html_entity_decode((string) preg_replace('/\s+/', ' ', strip_tags($html)), ENT_QUOTES | ENT_HTML5));
    }
}
