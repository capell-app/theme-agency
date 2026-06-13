<?php

declare(strict_types=1);

namespace Capell\LiveChat\Support;

use Capell\LiveChat\Actions\DetectLiveChatIntentAction;
use Capell\LiveChat\Actions\SearchLiveChatKnowledgeDocumentsAction;
use Capell\LiveChat\Contracts\LiveChatResponder;
use Capell\LiveChat\Data\LiveChatKnowledgeSearchResultData;
use Capell\LiveChat\Data\LiveChatResponseData;
use Capell\LiveChat\Enums\LiveChatAIRunStatus;
use Capell\LiveChat\Enums\LiveChatIntent;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatKnowledgeDocument;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Support\Str;

final class LocalLiveChatResponder implements LiveChatResponder
{
    public function respond(LiveChatConversation $conversation, LiveChatMessage $message): LiveChatResponseData
    {
        $body = Str::lower($message->body);
        $intent = (new DetectLiveChatIntentAction)->handle($message->body);
        $matches = (new SearchLiveChatKnowledgeDocumentsAction)->handle(
            $conversation,
            $message,
            $this->maxSourceDocuments(),
        );
        $sources = $this->knowledgeSources($matches);

        if ($intent === LiveChatIntent::Urgent || $intent === LiveChatIntent::Complaint) {
            return new LiveChatResponseData(
                body: __('capell-live-chat::generic.responses.urgent'),
                confidence: 0.35,
                intent: $intent,
                requiresContact: true,
                suggestedFields: ['name', 'email', 'phone'],
                knowledgeSources: $sources,
                sourceDocumentIds: $this->sourceDocumentIds($matches),
                sourceArea: 'local-rules',
            );
        }

        $groundedResponse = $this->groundedResponse($matches, $message, $intent);

        if ($groundedResponse instanceof LiveChatResponseData) {
            return $groundedResponse;
        }

        if (Str::contains($body, ['price', 'pricing', 'quote', 'cost', 'demo'])) {
            return new LiveChatResponseData(
                body: __('capell-live-chat::generic.responses.pricing'),
                confidence: 0.74,
                intent: LiveChatIntent::Sales,
                requiresContact: true,
                suggestedFields: ['name', 'email', 'company'],
                knowledgeSources: $sources,
                sourceDocumentIds: $this->sourceDocumentIds($matches),
                sourceArea: 'local-rules',
            );
        }

        if (Str::contains($body, ['support', 'broken', 'issue', 'error', 'bug', 'technical'])) {
            return new LiveChatResponseData(
                body: __('capell-live-chat::generic.responses.technical'),
                confidence: 0.68,
                intent: LiveChatIntent::TechnicalIssue,
                requiresContact: false,
                suggestedFields: ['email'],
                knowledgeSources: $sources,
                sourceDocumentIds: $this->sourceDocumentIds($matches),
                sourceArea: 'local-rules',
            );
        }

        if (Str::of($message->body)->trim()->length() < 18) {
            return new LiveChatResponseData(
                body: __('capell-live-chat::generic.responses.clarify'),
                confidence: 0.5,
                intent: $intent,
                requiresContact: false,
                suggestedFields: [],
                knowledgeSources: $sources,
                sourceDocumentIds: $this->sourceDocumentIds($matches),
                sourceArea: 'local-rules',
            );
        }

        return new LiveChatResponseData(
            body: __('capell-live-chat::generic.responses.fallback'),
            confidence: 0.62,
            intent: $intent,
            requiresContact: false,
            suggestedFields: [],
            knowledgeSources: $sources,
            sourceDocumentIds: $this->sourceDocumentIds($matches),
            aiRunStatus: LiveChatAIRunStatus::Fallback,
            knowledgeGapReason: $matches === [] ? 'no_matching_source' : null,
            sourceArea: 'local-fallback',
        );
    }

    /**
     * @param  list<LiveChatKnowledgeSearchResultData>  $matches
     * @return list<string>
     */
    private function knowledgeSources(array $matches): array
    {
        return array_values(array_unique(array_map(
            static fn (LiveChatKnowledgeSearchResultData $match): string => $match->document->title,
            $matches,
        )));
    }

    /**
     * @param  list<LiveChatKnowledgeSearchResultData>  $matches
     */
    private function groundedResponse(array $matches, LiveChatMessage $message, LiveChatIntent $intent): ?LiveChatResponseData
    {
        $match = $matches[0] ?? null;

        if (! $match instanceof LiveChatKnowledgeSearchResultData || $match->score < $this->minimumScore()) {
            return null;
        }

        return new LiveChatResponseData(
            body: __('capell-live-chat::generic.responses.grounded', [
                'source' => $match->document->title,
                'answer' => $this->snippet($match->document, $message->body),
            ]),
            confidence: min(0.94, 0.62 + ($match->score / 25)),
            intent: $intent,
            requiresContact: false,
            suggestedFields: [],
            knowledgeSources: $this->knowledgeSources($matches),
            sourceDocumentIds: $this->sourceDocumentIds($matches),
            sourceArea: 'local-grounded-answer',
        );
    }

    /**
     * @param  list<LiveChatKnowledgeSearchResultData>  $matches
     * @return list<int>
     */
    private function sourceDocumentIds(array $matches): array
    {
        return array_values(array_map(
            static fn (LiveChatKnowledgeSearchResultData $match): int => $match->document->id,
            $matches,
        ));
    }

    private function snippet(LiveChatKnowledgeDocument $document, string $question): string
    {
        $sentences = preg_split('/(?<=[.!?])\s+/', trim($document->content)) ?: [$document->content];
        $tokens = (new SearchLiveChatKnowledgeDocumentsAction)->tokens($question);
        $bestSentence = trim($document->content);
        $bestScore = -1;

        foreach ($sentences as $sentence) {
            if (! is_string($sentence) || trim($sentence) === '') {
                continue;
            }

            $score = 0;
            $normalizedSentence = Str::lower($sentence);

            foreach ($tokens as $token) {
                if (Str::contains($normalizedSentence, $token)) {
                    $score++;
                }
            }

            if ($score > $bestScore) {
                $bestSentence = trim($sentence);
                $bestScore = $score;
            }
        }

        return Str::limit($bestSentence, 320, '');
    }

    private function maxSourceDocuments(): int
    {
        return max(1, $this->configInt('capell-live-chat.knowledge.max_source_documents', 3));
    }

    private function minimumScore(): int
    {
        return max(1, $this->configInt('capell-live-chat.knowledge.minimum_score', 2));
    }

    private function configInt(string $key, int $fallback): int
    {
        $value = config($key);

        return is_int($value) || (is_string($value) && ctype_digit($value)) ? (int) $value : $fallback;
    }
}
