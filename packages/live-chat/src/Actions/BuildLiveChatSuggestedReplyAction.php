<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Models\LiveChatConversation;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildLiveChatSuggestedReplyAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>|null  $state
     */
    public function handle(LiveChatConversation $conversation, ?array $state = null): string
    {
        $state ??= (new BuildLiveChatOperatorStateAction)->handle($conversation);
        $latestMessage = is_string($state['latest_visitor_message'] ?? null)
            ? Str::limit($state['latest_visitor_message'], 180, '')
            : __('capell-live-chat::generic.ai.no_visitor_message');
        $sourceDocuments = $this->sourceDocumentTitles($state);

        if (($state['risk'] ?? null) === 'urgent') {
            return __('capell-live-chat::generic.ai.urgent_reply_template', [
                'message' => $latestMessage,
            ]);
        }

        if ($sourceDocuments !== []) {
            return __('capell-live-chat::generic.ai.sourced_reply_template', [
                'message' => $latestMessage,
                'sources' => implode(', ', $sourceDocuments),
            ]);
        }

        if (($state['lead_qualification'] ?? null) === 'needs_contact' && ! $this->hasContactDetails($conversation)) {
            return __('capell-live-chat::generic.ai.contact_reply_template', [
                'message' => $latestMessage,
            ]);
        }

        return __('capell-live-chat::generic.ai.general_reply_template', [
            'message' => $latestMessage,
        ]);
    }

    private function hasContactDetails(LiveChatConversation $conversation): bool
    {
        return trim((string) $conversation->visitor_email) !== ''
            || trim((string) $conversation->visitor_phone) !== ''
            || trim((string) $conversation->visitor_name) !== '';
    }

    /**
     * @param  array<string, mixed>  $state
     * @return list<string>
     */
    private function sourceDocumentTitles(array $state): array
    {
        $documents = $state['source_documents'] ?? [];

        if (! is_array($documents)) {
            return [];
        }

        $titles = [];

        foreach ($documents as $document) {
            if (! is_array($document)) {
                continue;
            }

            $title = $document['title'] ?? null;

            if (is_string($title) && $title !== '') {
                $titles[] = $title;
            }
        }

        return $titles;
    }
}
