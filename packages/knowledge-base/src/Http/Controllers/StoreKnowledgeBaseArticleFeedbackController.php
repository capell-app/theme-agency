<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Http\Controllers;

use Capell\KnowledgeBase\Actions\BuildPublicKnowledgeBaseArticleDataAction;
use Capell\KnowledgeBase\Actions\RecordKnowledgeBaseArticleFeedbackAction;
use Capell\KnowledgeBase\Data\RecordKnowledgeBaseArticleFeedbackData;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticle;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class StoreKnowledgeBaseArticleFeedbackController
{
    public function __invoke(Request $request, string $collectionSlug, string $articleSlug): RedirectResponse
    {
        /** @var KnowledgeBaseArticle|null $article */
        $article = KnowledgeBaseArticle::query()
            ->where('slug', $articleSlug)
            ->whereHas('collection', static function (Builder $query) use ($collectionSlug): void {
                $query->where('slug', $collectionSlug);
            })
            ->with(['collection', 'currentVersion'])
            ->first();

        abort_if($article === null, 404);
        abort_if(BuildPublicKnowledgeBaseArticleDataAction::run($article) === null, 404);

        $validated = $request->validate([
            'helpful' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        RecordKnowledgeBaseArticleFeedbackAction::run(new RecordKnowledgeBaseArticleFeedbackData(
            article: $article,
            helpful: (bool) (int) $validated['helpful'],
            articleVersion: $article->currentVersion,
            comment: is_string($validated['comment'] ?? null) ? $validated['comment'] : null,
            visitorIdentifier: $request->ip(),
            userAgent: $request->userAgent(),
        ));

        return redirect()
            ->to($request->headers->get('referer') ?: url()->previous())
            ->with('knowledge_base_feedback_status', __('capell-knowledge-base::generic.frontend.feedback_submitted'));
    }
}
