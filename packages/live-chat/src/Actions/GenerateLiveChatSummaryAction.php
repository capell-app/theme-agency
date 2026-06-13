<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Data\LiveChatAIRunData;
use Capell\LiveChat\Models\LiveChatConversation;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class GenerateLiveChatSummaryAction
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(LiveChatConversation $conversation): array
    {
        $state = (new BuildLiveChatOperatorStateAction)->handle($conversation);
        $metadata = $conversation->metadata ?? [];
        $aiMetadata = array_replace($this->metadataArray($metadata['ai'] ?? []), [
            ...$state,
            'summary_generated_at' => CarbonImmutable::now()->toISOString(),
            'summary_model_tier' => 'low-cost',
        ]);

        $conversation->forceFill([
            'metadata' => array_replace($metadata, ['ai' => $aiMetadata]),
        ])->save();

        RecordLiveChatAIRunAction::run(new LiveChatAIRunData(
            capabilityKey: 'live-chat-summarize-conversation',
            installationId: $conversation->installation_id,
            conversationId: $conversation->id,
            modelTier: 'low-cost',
            confidence: 0.82,
            sourceDocumentIds: $this->sourceDocumentIds($state),
            inputPayload: ['conversation_id' => $conversation->id],
            outputPayload: $state,
        ));

        return $aiMetadata;
    }

    /**
     * @return array<string, mixed>
     */
    private function metadataArray(mixed $value): array
    {
        return is_array($value) ? $value : [];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return list<int>
     */
    private function sourceDocumentIds(array $state): array
    {
        $ids = $state['source_document_ids'] ?? [];

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_filter($ids, static fn (mixed $id): bool => is_int($id)));
    }
}
