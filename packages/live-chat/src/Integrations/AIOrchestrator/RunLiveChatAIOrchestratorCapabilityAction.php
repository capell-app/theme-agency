<?php

declare(strict_types=1);

namespace Capell\LiveChat\Integrations\AIOrchestrator;

use Capell\AIOrchestrator\Data\AIOrchestratorRunData;
use Capell\LiveChat\Actions\BuildLiveChatOperatorStateAction;
use Capell\LiveChat\Actions\DetectLiveChatIntentAction;
use Capell\LiveChat\Actions\GenerateLiveChatSummaryAction;
use Capell\LiveChat\Actions\SearchLiveChatKnowledgeDocumentsAction;
use Capell\LiveChat\Actions\SuggestLiveChatHumanReplyAction;
use Capell\LiveChat\Data\LiveChatKnowledgeSearchResultData;
use Capell\LiveChat\Enums\LiveChatIntent;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsObject;
use RuntimeException;

final class RunLiveChatAIOrchestratorCapabilityAction
{
    use AsObject;

    /**
     * @return array<string, mixed>
     */
    public function handle(AIOrchestratorRunData $run): array
    {
        return match ($run->capabilityKey) {
            'classify-message' => $this->classifyMessage($run),
            'detect-risk-sentiment' => $this->riskSentiment($run),
            'answer-approved-sources' => $this->answerApprovedSources($run),
            'extract-lead-details' => $this->extractLeadDetails($run),
            'summarize-conversation' => $this->summarizeConversation($run),
            'suggest-human-reply' => $this->suggestHumanReply($run),
            'identify-knowledge-gap' => $this->identifyKnowledgeGap($run),
            default => throw new RuntimeException(sprintf('Unsupported Live Chat AI capability [%s].', $run->capabilityKey)),
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function classifyMessage(AIOrchestratorRunData $run): array
    {
        $message = $this->message($run);
        $body = $message instanceof LiveChatMessage ? $message->body : $run->prompt;
        $intent = (new DetectLiveChatIntentAction)->handle($body);

        return [
            'intent' => $intent->value,
            'model_tier' => 'low-cost',
            'public_auto_reply_safe' => in_array($intent, [LiveChatIntent::Sales, LiveChatIntent::Support, LiveChatIntent::TechnicalIssue, LiveChatIntent::General], true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function riskSentiment(AIOrchestratorRunData $run): array
    {
        $conversation = $this->conversation($run);

        if (! $conversation instanceof LiveChatConversation) {
            $intent = (new DetectLiveChatIntentAction)->handle($run->prompt);

            return [
                'risk' => in_array($intent, [LiveChatIntent::Complaint, LiveChatIntent::Urgent], true) ? 'urgent' : 'normal',
                'sentiment' => in_array($intent, [LiveChatIntent::Complaint, LiveChatIntent::Urgent], true) ? 'negative' : 'neutral',
                'model_tier' => 'low-cost',
            ];
        }

        $state = (new BuildLiveChatOperatorStateAction)->handle($conversation);

        return [
            'risk' => $state['risk'] ?? 'normal',
            'sentiment' => $state['sentiment'] ?? 'neutral',
            'model_tier' => 'low-cost',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function answerApprovedSources(AIOrchestratorRunData $run): array
    {
        $conversation = $this->requiredConversation($run);
        $message = $this->requiredMessage($run, $conversation);
        $matches = (new SearchLiveChatKnowledgeDocumentsAction)->handle($conversation, $message);
        $match = $matches[0] ?? null;

        if (! $match instanceof LiveChatKnowledgeSearchResultData) {
            return [
                'status' => 'fallback',
                'answer' => null,
                'source_document_ids' => [],
                'knowledge_gap_reason' => 'no_matching_source',
                'model_tier' => 'strong',
            ];
        }

        return [
            'status' => 'grounded',
            'answer' => Str::limit($match->document->content, 320, ''),
            'source_document_ids' => array_values(array_map(
                static fn (LiveChatKnowledgeSearchResultData $result): int => $result->document->id,
                $matches,
            )),
            'source_titles' => array_values(array_map(
                static fn (LiveChatKnowledgeSearchResultData $result): string => $result->document->title,
                $matches,
            )),
            'score' => $match->score,
            'model_tier' => 'strong',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function extractLeadDetails(AIOrchestratorRunData $run): array
    {
        $conversation = $this->conversation($run);

        return [
            'name' => $conversation?->visitor_name,
            'email' => $conversation?->visitor_email,
            'phone' => $conversation?->visitor_phone,
            'company' => $conversation?->visitor_company,
            'has_contact_details' => $conversation instanceof LiveChatConversation
                && (filled($conversation->visitor_email) || filled($conversation->visitor_phone) || filled($conversation->visitor_name)),
            'model_tier' => 'low-cost',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function summarizeConversation(AIOrchestratorRunData $run): array
    {
        return (new GenerateLiveChatSummaryAction)->handle($this->requiredConversation($run));
    }

    /**
     * @return array<string, mixed>
     */
    private function suggestHumanReply(AIOrchestratorRunData $run): array
    {
        return (new SuggestLiveChatHumanReplyAction)->handle($this->requiredConversation($run));
    }

    /**
     * @return array<string, mixed>
     */
    private function identifyKnowledgeGap(AIOrchestratorRunData $run): array
    {
        $conversation = $this->conversation($run);

        if (! $conversation instanceof LiveChatConversation) {
            return [
                'has_gap' => false,
                'reason' => null,
                'model_tier' => 'low-cost',
            ];
        }

        $state = (new BuildLiveChatOperatorStateAction)->handle($conversation);
        $reason = $state['knowledge_gap_reason'] ?? null;

        return [
            'has_gap' => is_string($reason) && $reason !== '',
            'reason' => is_string($reason) ? $reason : null,
            'source_document_ids' => $state['source_document_ids'] ?? [],
            'model_tier' => 'low-cost',
        ];
    }

    private function requiredConversation(AIOrchestratorRunData $run): LiveChatConversation
    {
        $conversation = $this->conversation($run);

        throw_unless($conversation instanceof LiveChatConversation, RuntimeException::class, 'Live Chat AI capability requires a conversation_id context value.');

        return $conversation;
    }

    private function conversation(AIOrchestratorRunData $run): ?LiveChatConversation
    {
        $conversationId = $run->context['conversation_id'] ?? null;

        if (! is_int($conversationId)) {
            return null;
        }

        return LiveChatConversation::query()->find($conversationId);
    }

    private function requiredMessage(AIOrchestratorRunData $run, LiveChatConversation $conversation): LiveChatMessage
    {
        $message = $this->message($run);

        if ($message instanceof LiveChatMessage && $message->conversation_id === $conversation->id) {
            return $message;
        }

        $message = $conversation->messages()->latest('id')->first();

        throw_unless($message instanceof LiveChatMessage, RuntimeException::class, 'Live Chat AI capability requires a message_id context value or an existing conversation message.');

        return $message;
    }

    private function message(AIOrchestratorRunData $run): ?LiveChatMessage
    {
        $messageId = $run->context['message_id'] ?? null;

        if (! is_int($messageId)) {
            return null;
        }

        return LiveChatMessage::query()->find($messageId);
    }
}
