<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Data\IncomingLiveChatMessageData;
use Capell\LiveChat\Enums\MessageRole;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatMessage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class StoreLiveChatMessageAction
{
    use AsAction;

    /**
     * @return array{conversation: LiveChatConversation, visitor_message: LiveChatMessage, assistant_message: LiveChatMessage}
     */
    public function handle(LiveChatConversation $conversation, IncomingLiveChatMessageData $data): array
    {
        $visitor = $data->visitor;

        if ($visitor !== null) {
            $conversation->fill([
                'visitor_name' => $visitor->name ?? $conversation->visitor_name,
                'visitor_email' => $visitor->email ?? $conversation->visitor_email,
                'visitor_phone' => $visitor->phone ?? $conversation->visitor_phone,
                'visitor_company' => $visitor->company ?? $conversation->visitor_company,
                'preferred_callback_at' => $visitor->preferredCallbackAt ?? $conversation->preferred_callback_at,
                'processing_consent' => $visitor->processingConsent || $conversation->processing_consent,
                'marketing_consent' => $visitor->marketingConsent || $conversation->marketing_consent,
            ]);
        }

        $conversation->forceFill([
            'last_page_url' => $this->pageValue($data->page, 'url') ?? $conversation->last_page_url,
            'last_message_at' => CarbonImmutable::now(),
        ])->save();

        $visitorMessage = $conversation->messages()->create([
            'role' => MessageRole::Visitor,
            'body' => $this->trimBody($data->body),
            'attachments' => $data->attachments,
        ]);

        $assistantMessage = app(ReplyToLiveChatMessageAction::class)->handle($conversation, $visitorMessage);

        SyncLiveChatConversationContactAction::run($conversation);

        return [
            'conversation' => $conversation->fresh() ?? $conversation,
            'visitor_message' => $visitorMessage,
            'assistant_message' => $assistantMessage,
        ];
    }

    private function trimBody(string $body): string
    {
        $maxLength = $this->configInt('capell-live-chat.max_message_length', 4000);

        return Str::limit(trim($body), max(1, $maxLength), '');
    }

    /**
     * @param  array<string, mixed>  $page
     */
    private function pageValue(array $page, string $key): ?string
    {
        $value = $page[$key] ?? null;

        return is_string($value) && trim($value) !== '' ? $value : null;
    }

    private function configInt(string $key, int $fallback): int
    {
        $value = config($key);

        return is_int($value) || (is_string($value) && ctype_digit($value)) ? (int) $value : $fallback;
    }
}
