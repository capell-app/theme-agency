<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Contracts\LiveChatResponder;
use Capell\LiveChat\Data\LiveChatAIRunData;
use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Enums\MessageRole;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatMessage;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

final class ReplyToLiveChatMessageAction
{
    use AsAction;

    public function __construct(private readonly LiveChatResponder $responder) {}

    public function handle(LiveChatConversation $conversation, LiveChatMessage $message): LiveChatMessage
    {
        $availability = (new ResolveLiveChatAvailabilityAction)->handle(
            siteId: (int) $conversation->site_id,
            timezone: $conversation->timezone,
        );
        $response = $this->responder->respond($conversation, $message);
        $decision = (new DetermineLiveChatEscalationAction)->handle($conversation, $message, $response, $availability->available);

        if ($decision->shouldEscalate) {
            $conversation->forceFill([
                'status' => ConversationStatus::WaitingForHuman,
                'priority' => $decision->priority,
                'assignment_queue' => $decision->routeTo,
                'escalation_reason' => $decision->reason,
                'escalated_at' => $conversation->escalated_at ?? CarbonImmutable::now(),
            ]);
        }

        $conversation->forceFill([
            'intent' => $response->intent,
            'last_message_at' => CarbonImmutable::now(),
        ])->save();

        $assistantMessage = $conversation->messages()->create([
            'role' => MessageRole::Assistant,
            'body' => $decision->message ?? $response->body,
            'intent' => $response->intent,
            'confidence' => $response->confidence,
            'requires_contact' => $response->requiresContact || $decision->shouldEscalate,
            'metadata' => [
                'ai_disclosure' => true,
                'availability' => $availability->toArray(),
                'escalation' => $decision->toArray(),
                'knowledge_sources' => $response->knowledgeSources,
                'source_document_ids' => $response->sourceDocumentIds,
                'suggested_fields' => $response->suggestedFields,
            ],
        ]);

        RecordLiveChatAIRunAction::run(new LiveChatAIRunData(
            capabilityKey: 'live-chat-local-response',
            installationId: $conversation->installation_id,
            conversationId: $conversation->id,
            messageId: $assistantMessage->id,
            modelTier: $response->modelTier,
            confidence: $response->confidence,
            status: $response->aiRunStatus,
            sourceDocumentIds: $response->sourceDocumentIds,
            refusalReason: $response->refusalReason,
            inputPayload: [
                'message_id' => $message->id,
                'body' => $message->body,
            ],
            outputPayload: [
                'message_id' => $assistantMessage->id,
                'body' => $assistantMessage->body,
                'source_area' => $response->sourceArea,
            ],
        ));

        if ($response->knowledgeGapReason !== null) {
            RecordLiveChatKnowledgeGapAction::run(
                question: $message->body,
                installationId: $conversation->installation_id,
                conversationId: $conversation->id,
                messageId: $message->id,
                sourceArea: $response->sourceArea,
                metadata: [
                    'reason' => $response->knowledgeGapReason,
                    'confidence' => $response->confidence,
                    'source_document_ids' => $response->sourceDocumentIds,
                ],
            );
        }

        return $assistantMessage;
    }
}
