<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Data\IncomingLiveChatMessageData;
use Capell\LiveChat\Enums\ConversationFlow;
use Capell\LiveChat\Enums\ConversationStatus;
use Capell\LiveChat\Enums\LiveChatPriority;
use Capell\LiveChat\Enums\MessageRole;
use Capell\LiveChat\Models\LiveChatConversation;
use Capell\LiveChat\Models\LiveChatInstallation;
use Capell\LiveChat\Models\LiveChatMessage;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class StartLiveChatConversationAction
{
    use AsAction;

    /**
     * @return array{conversation: LiveChatConversation, visitor_message: LiveChatMessage, assistant_message: LiveChatMessage}
     */
    public function handle(IncomingLiveChatMessageData $data, int $siteId, ?LiveChatInstallation $installation = null): array
    {
        $now = CarbonImmutable::now();
        $visitor = $data->visitor;
        $conversation = new LiveChatConversation([
            'site_id' => $siteId,
            'installation_id' => $installation?->getKey(),
            'uuid' => $data->conversationUuid,
            'status' => ConversationStatus::Active,
            'flow' => ConversationFlow::tryFrom($data->flow) ?? ConversationFlow::MessageFirst,
            'priority' => LiveChatPriority::Normal,
            'locale' => $data->locale ?? app()->getLocale(),
            'timezone' => $data->timezone ?? $this->configString('capell-live-chat.default_timezone', 'UTC'),
            'visitor_token_hash' => LiveChatConversation::hashVisitorToken($data->visitorToken),
            'visitor_name' => $visitor?->name,
            'visitor_email' => $visitor?->email,
            'visitor_phone' => $visitor?->phone,
            'visitor_company' => $visitor?->company,
            'preferred_callback_at' => $visitor?->preferredCallbackAt,
            'processing_consent' => $visitor !== null && $visitor->processingConsent,
            'marketing_consent' => $visitor !== null && $visitor->marketingConsent,
            'ai_disclosure_at' => $now,
            'first_page_url' => $this->pageValue($data->page, 'url'),
            'last_page_url' => $this->pageValue($data->page, 'url'),
            'referrer_url' => $this->pageValue($data->page, 'referrer'),
            'last_message_at' => $now,
            'metadata' => [
                'topic' => $visitor?->topic,
                'page' => $data->page,
            ],
        ]);

        $conversation->save();

        $visitorMessage = $conversation->messages()->create([
            'role' => MessageRole::Visitor,
            'body' => $this->trimBody($data->body),
            'attachments' => $data->attachments,
        ]);

        $result = app(ReplyToLiveChatMessageAction::class)->handle($conversation, $visitorMessage);

        SyncLiveChatConversationContactAction::run($conversation);

        return [
            'conversation' => $conversation->fresh() ?? $conversation,
            'visitor_message' => $visitorMessage,
            'assistant_message' => $result,
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

    private function configString(string $key, string $fallback): string
    {
        $value = config($key);

        return is_string($value) && $value !== '' ? $value : $fallback;
    }

    private function configInt(string $key, int $fallback): int
    {
        $value = config($key);

        return is_int($value) || (is_string($value) && ctype_digit($value)) ? (int) $value : $fallback;
    }
}
