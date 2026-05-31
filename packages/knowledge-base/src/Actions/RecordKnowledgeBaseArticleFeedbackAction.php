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
        return KnowledgeBaseArticleFeedback::query()->create([
            'article_id' => $data->article->getKey(),
            'article_version_id' => $data->articleVersion?->getKey() ?? $data->article->current_version_id,
            'helpful' => $data->helpful,
            'comment' => $data->comment === null ? null : trim($data->comment),
            'visitor_hash' => $this->hashNullable($data->visitorIdentifier),
            'user_agent_hash' => $this->hashNullable($data->userAgent),
            'submitted_at' => now(),
        ]);
    }

    private function hashNullable(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        return hash('sha256', trim($value) . '|' . config('capell-knowledge-base.feedback.hash_salt'));
    }
}
