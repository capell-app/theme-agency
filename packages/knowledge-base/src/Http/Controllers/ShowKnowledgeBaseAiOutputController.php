<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Http\Controllers;

use Capell\KnowledgeBase\Actions\BuildAiReadableKnowledgeBaseOutputAction;
use Capell\KnowledgeBase\Data\AiReadableKnowledgeBaseArticleData;
use Illuminate\Http\Response;

final class ShowKnowledgeBaseAiOutputController
{
    public function __invoke(): Response
    {
        $articles = BuildAiReadableKnowledgeBaseOutputAction::run();

        $body = collect([
            '# ' . __('capell-knowledge-base::generic.frontend.ai_output_title'),
            '',
            ...$articles
                ->map(fn (AiReadableKnowledgeBaseArticleData $article): string => $this->articleMarkdown($article))
                ->all(),
        ])->implode("\n");

        return response($body . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300, stale-while-revalidate=300',
        ]);
    }

    private function articleMarkdown(AiReadableKnowledgeBaseArticleData $article): string
    {
        return collect([
            '## ' . $article->title,
            '',
            '- URL: ' . $article->publicPath,
            '- Version: ' . $article->version,
            $article->lastModified !== null ? '- Last modified: ' . $article->lastModified->toDateString() : null,
            $article->summary !== null && trim($article->summary) !== '' ? '- Summary: ' . trim($article->summary) : null,
            '',
            $article->content,
            '',
        ])->filter(static fn (?string $line): bool => $line !== null)->implode("\n");
    }
}
