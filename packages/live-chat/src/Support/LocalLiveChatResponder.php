<?php

declare(strict_types=1);

namespace Capell\LiveChat\Support;

use Capell\LiveChat\Actions\DetectLiveChatIntentAction;
use Capell\LiveChat\Contracts\LiveChatResponder;
use Capell\LiveChat\Data\LiveChatResponseData;
use Capell\LiveChat\Enums\LiveChatIntent;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatKnowledgeSource;
use Capell\LiveChat\Models\LiveChatMessage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class LocalLiveChatResponder implements LiveChatResponder
{
    public function respond(LiveChatConversation $conversation, LiveChatMessage $message): LiveChatResponseData
    {
        $body = Str::lower($message->body);
        $intent = DetectLiveChatIntentAction::run($message->body);
        $sources = $this->knowledgeSources((int) $conversation->site_id);

        if ($intent === LiveChatIntent::Urgent || $intent === LiveChatIntent::Complaint) {
            return new LiveChatResponseData(
                body: __('capell-live-chat::generic.responses.urgent'),
                confidence: 0.35,
                intent: $intent,
                requiresContact: true,
                suggestedFields: ['name', 'email', 'phone'],
                knowledgeSources: $sources,
            );
        }

        if (Str::contains($body, ['price', 'pricing', 'quote', 'cost', 'demo'])) {
            return new LiveChatResponseData(
                body: __('capell-live-chat::generic.responses.pricing'),
                confidence: 0.74,
                intent: LiveChatIntent::Sales,
                requiresContact: true,
                suggestedFields: ['name', 'email', 'company'],
                knowledgeSources: $sources,
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
            );
        }

        return new LiveChatResponseData(
            body: __('capell-live-chat::generic.responses.fallback'),
            confidence: 0.62,
            intent: $intent,
            requiresContact: false,
            suggestedFields: [],
            knowledgeSources: $sources,
        );
    }

    /**
     * @return list<string>
     */
    private function knowledgeSources(int $siteId): array
    {
        return LiveChatKnowledgeSource::query()
            ->where(static function (Builder $query) use ($siteId): void {
                $query->where('site_id', $siteId)->orWhereNull('site_id');
            })
            ->where('status', 'active')
            ->orderBy('title')
            ->limit(5)
            ->pluck('title')
            ->all();
    }
}
