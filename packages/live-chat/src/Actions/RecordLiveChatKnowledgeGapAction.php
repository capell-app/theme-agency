<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Enums\LiveChatKnowledgeGapStatus;
use Capell\LiveChat\Models\LiveChatKnowledgeGap;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordLiveChatKnowledgeGapAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $metadata
     */
    public function handle(
        string $question,
        ?int $installationId = null,
        ?int $conversationId = null,
        ?int $messageId = null,
        ?string $sourceArea = null,
        array $metadata = [],
    ): LiveChatKnowledgeGap {
        $now = CarbonImmutable::now();
        $questionHash = LiveChatKnowledgeGap::hashQuestion($question);

        /** @var LiveChatKnowledgeGap $gap */
        $gap = LiveChatKnowledgeGap::query()->firstOrNew([
            'installation_id' => $installationId,
            'question_hash' => $questionHash,
        ]);

        $gap->fill([
            'conversation_id' => $conversationId ?? $gap->conversation_id,
            'message_id' => $messageId ?? $gap->message_id,
            'question' => $gap->exists ? $gap->question : trim($question),
            'source_area' => $sourceArea ?? $gap->source_area,
            'status' => $gap->exists ? $gap->status : LiveChatKnowledgeGapStatus::Open,
            'occurrence_count' => $gap->exists ? $gap->occurrence_count + 1 : 1,
            'first_seen_at' => $gap->first_seen_at ?? $now,
            'last_seen_at' => $now,
            'metadata' => array_replace($gap->metadata ?? [], $metadata),
        ]);
        $gap->save();

        return $gap;
    }
}
