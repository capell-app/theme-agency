<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Enums\EscalationReason;
use Capell\LiveChat\Enums\LiveChatIntent;
use Capell\LiveChat\Enums\LiveChatPriority;
use Capell\LiveChat\Enums\MessageRole;
use Capell\LiveChat\Models\LiveChatAIRun;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatKnowledgeGap;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildLiveChatOperatorStateAction
{
    use AsAction;

    /**
     * @return array<string, mixed>
     */
    public function handle(LiveChatConversation $conversation): array
    {
        $latestVisitorMessage = $this->latestMessage($conversation, MessageRole::Visitor);
        $latestAssistantMessage = $this->latestMessage($conversation, MessageRole::Assistant);
        $latestRun = $this->latestRun($conversation);
        $latestGap = $this->latestGap($conversation);
        $sourceDocumentIds = $this->sourceDocumentIds($latestAssistantMessage, $latestRun);
        $sourceDocuments = $this->sourceDocuments($sourceDocumentIds);

        return [
            'summary' => $this->summary($conversation, $latestVisitorMessage),
            'sentiment' => $this->sentiment($conversation, $latestVisitorMessage),
            'risk' => $this->risk($conversation, $latestRun, $latestGap),
            'lead_qualification' => $this->leadQualification($conversation, $latestAssistantMessage),
            'source_document_ids' => $sourceDocumentIds,
            'source_documents' => $sourceDocuments,
            'knowledge_gap_reason' => $this->knowledgeGapReason($latestGap),
            'latest_visitor_message' => $latestVisitorMessage?->body,
            'latest_assistant_message' => $latestAssistantMessage?->body,
        ];
    }

    private function latestMessage(LiveChatConversation $conversation, MessageRole $role): ?LiveChatMessage
    {
        return $conversation
            ->messages()
            ->where('role', $role)
            ->latest('id')
            ->first();
    }

    private function latestRun(LiveChatConversation $conversation): ?LiveChatAIRun
    {
        return $conversation->aiRuns()->latest('id')->first();
    }

    private function latestGap(LiveChatConversation $conversation): ?LiveChatKnowledgeGap
    {
        return $conversation->knowledgeGaps()->latest('last_seen_at')->latest('id')->first();
    }

    private function summary(LiveChatConversation $conversation, ?LiveChatMessage $latestVisitorMessage): string
    {
        $visitor = $conversation->visitor_name ?: $conversation->visitor_email ?: $conversation->uuid;
        $intent = $conversation->intent instanceof LiveChatIntent ? $conversation->intent->getLabel() : __('capell-live-chat::generic.intent.general');
        $latestMessage = $latestVisitorMessage instanceof LiveChatMessage
            ? Str::limit($latestVisitorMessage->body, 220, '')
            : __('capell-live-chat::generic.ai.no_visitor_message');

        return __('capell-live-chat::generic.ai.summary_template', [
            'visitor' => $visitor,
            'intent' => $intent,
            'message' => $latestMessage,
        ]);
    }

    private function sentiment(LiveChatConversation $conversation, ?LiveChatMessage $latestVisitorMessage): string
    {
        $latestMessage = $latestVisitorMessage instanceof LiveChatMessage ? $latestVisitorMessage->body : '';
        $text = Str::lower($latestMessage . ' ' . (string) $conversation->escalation_reason?->value);

        if ($conversation->intent === LiveChatIntent::Complaint || Str::contains($text, ['angry', 'complaint', 'refund', 'broken', 'unhappy', 'urgent'])) {
            return 'negative';
        }

        if (Str::contains($text, ['thanks', 'thank you', 'great', 'helpful'])) {
            return 'positive';
        }

        return 'neutral';
    }

    private function risk(LiveChatConversation $conversation, ?LiveChatAIRun $latestRun, ?LiveChatKnowledgeGap $latestGap): string
    {
        if (
            $conversation->priority === LiveChatPriority::Urgent
            || $conversation->intent === LiveChatIntent::Urgent
            || $conversation->intent === LiveChatIntent::Complaint
            || $conversation->escalation_reason === EscalationReason::Sensitive
        ) {
            return 'urgent';
        }

        if (
            $conversation->status === ConversationStatus::WaitingForHuman
            || $conversation->handoff_requested_at !== null
            || $conversation->escalation_reason !== null
            || $latestGap instanceof LiveChatKnowledgeGap
            || ($latestRun instanceof LiveChatAIRun && $latestRun->confidence !== null && $latestRun->confidence < 0.55)
        ) {
            return 'elevated';
        }

        return 'normal';
    }

    private function leadQualification(LiveChatConversation $conversation, ?LiveChatMessage $latestAssistantMessage): string
    {
        if ($conversation->intent === LiveChatIntent::Sales && ($this->filled($conversation->visitor_email) || $this->filled($conversation->visitor_phone))) {
            return 'qualified_lead';
        }

        if ($conversation->intent === LiveChatIntent::Sales) {
            return 'needs_contact';
        }

        if ($latestAssistantMessage instanceof LiveChatMessage && $latestAssistantMessage->requires_contact) {
            return 'needs_contact';
        }

        if ($this->filled($conversation->visitor_email) || $this->filled($conversation->visitor_phone)) {
            return 'contactable';
        }

        return 'not_qualified';
    }

    private function filled(?string $value): bool
    {
        return is_string($value) && trim($value) !== '';
    }

    /**
     * @return list<int>
     */
    private function sourceDocumentIds(?LiveChatMessage $latestAssistantMessage, ?LiveChatAIRun $latestRun): array
    {
        $messageIds = $this->sourceDocumentIdsFromMessage($latestAssistantMessage);
        $runIds = $latestRun?->source_document_ids;

        if (! is_array($runIds)) {
            return $messageIds;
        }

        return array_values(array_unique([
            ...$messageIds,
            ...array_filter($runIds, static fn (mixed $id): bool => is_int($id)),
        ]));
    }

    /**
     * @return list<int>
     */
    private function sourceDocumentIdsFromMessage(?LiveChatMessage $message): array
    {
        if (! $message instanceof LiveChatMessage) {
            return [];
        }

        $metadata = $message->metadata ?? [];
        $ids = $metadata['source_document_ids'] ?? [];

        if (! is_array($ids)) {
            return [];
        }

        return array_values(array_filter($ids, static fn (mixed $id): bool => is_int($id)));
    }

    /**
     * @param  list<int>  $ids
     * @return list<array{id: int, title: string, source_type: string, url: string|null}>
     */
    private function sourceDocuments(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        /** @var Collection<int, LiveChatKnowledgeDocument> $documents */
        $documents = LiveChatKnowledgeDocument::query()
            ->whereIn('id', $ids)
            ->orderBy('title')
            ->get();

        $sourceDocuments = $documents
            ->map(static fn (LiveChatKnowledgeDocument $document): array => [
                'id' => $document->id,
                'title' => $document->title,
                'source_type' => $document->source_type->value,
                'url' => $document->url,
            ])
            ->values()
            ->all();

        return array_values($sourceDocuments);
    }

    private function knowledgeGapReason(?LiveChatKnowledgeGap $gap): ?string
    {
        if (! $gap instanceof LiveChatKnowledgeGap) {
            return null;
        }

        $metadata = $gap->metadata ?? [];
        $reason = $metadata['reason'] ?? null;

        return is_string($reason) && $reason !== '' ? $reason : null;
    }
}
