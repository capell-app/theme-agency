<?php

declare(strict_types=1);

namespace Capell\KnowledgeBase\Actions;

use Capell\KnowledgeBase\Data\RecordKnowledgeBaseArticleFeedbackData;
use Capell\KnowledgeBase\Models\KnowledgeBaseArticleFeedback;
use Lorisleiva\Actions\Concerns\AsObject;

final class RecordKnowledgeBaseArticleFeedbackAction
{
    use AsObject;

    public function handle(RecordKnowledgeBaseArticleFeedbackData $data): KnowledgeBaseArticleFeedback
    {
        $visitorHash = $this->hashNullable($data->visitorIdentifier);
        $articleVersionId = $data->articleVersion?->getKey() ?? $data->article->current_version_id;

        $feedback = $visitorHash === null
            ? new KnowledgeBaseArticleFeedback
            : KnowledgeBaseArticleFeedback::query()->firstOrNew([
                'article_id' => $data->article->getKey(),
                'article_version_id' => $articleVersionId,
                'visitor_hash' => $visitorHash,
            ]);

        $feedback->fill([
            'article_id' => $data->article->getKey(),
            'article_version_id' => $articleVersionId,
            'helpful' => $data->helpful,
            'comment' => $data->comment === null ? null : trim($data->comment),
            'visitor_hash' => $visitorHash,
            'user_agent_hash' => $this->hashNullable($data->userAgent),
            'submitted_at' => now(),
        ]);

        $feedback->save();

        return $feedback;
    }

    private function hashNullable(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return hash('sha256', trim($value) . '|' . config('capell-knowledge-base.feedback.hash_salt'));
    }
}
