<?php

declare(strict_types=1);

namespace Capell\LiveChat\Http\Controllers\Concerns;

use Capell\LiveChat\Data\IncomingLiveChatMessageData;
use Capell\LiveChat\Data\LiveChatVisitorData;
use Carbon\CarbonImmutable;

trait BuildsLiveChatPayloads
{
    /**
     * @param  array<string, mixed>  $validated
     * @param  list<array{name: string, disk?: string, path?: string, url?: string, mime: string|null, size: int|null}>  $attachments
     */
    private function incomingMessageData(array $validated, array $attachments = []): IncomingLiveChatMessageData
    {
        return new IncomingLiveChatMessageData(
            body: $this->requiredString($validated['body'] ?? null),
            visitorToken: $this->nullableString($validated['visitor_token'] ?? null),
            conversationUuid: $this->nullableString($validated['conversation_uuid'] ?? null),
            visitor: $this->visitorData($validated['visitor'] ?? null),
            flow: $this->nullableString($validated['flow'] ?? null) ?? 'message_first',
            timezone: $this->nullableString($validated['timezone'] ?? null),
            locale: $this->nullableString($validated['locale'] ?? null),
            attachments: $attachments,
            page: is_array($validated['page'] ?? null) ? $validated['page'] : [],
        );
    }

    private function visitorData(mixed $visitor): ?LiveChatVisitorData
    {
        if (! is_array($visitor)) {
            return null;
        }

        return new LiveChatVisitorData(
            name: $this->nullableString($visitor['name'] ?? null),
            email: $this->nullableString($visitor['email'] ?? null),
            phone: $this->nullableString($visitor['phone'] ?? null),
            company: $this->nullableString($visitor['company'] ?? null),
            topic: $this->nullableString($visitor['topic'] ?? null),
            preferredCallbackAt: $this->date($visitor['preferred_callback_at'] ?? null),
            processingConsent: (bool) ($visitor['processing_consent'] ?? false),
            marketingConsent: (bool) ($visitor['marketing_consent'] ?? false),
        );
    }

    private function nullableString(mixed $value): ?string
    {
        return is_string($value) && trim($value) !== '' ? trim($value) : null;
    }

    private function requiredString(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function date(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        return CarbonImmutable::parse($value);
    }
}
