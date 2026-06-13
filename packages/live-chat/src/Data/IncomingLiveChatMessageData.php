<?php

declare(strict_types=1);

namespace Capell\LiveChat\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class IncomingLiveChatMessageData extends Data
{
    /**
     * @param  list<array{name?: string, url?: string, mime?: string, size?: int}>  $attachments
     * @param  array<string, mixed>  $page
     */
    public function __construct(
        public string $body,
        public ?string $visitorToken = null,
        public ?string $conversationUuid = null,
        public ?LiveChatVisitorData $visitor = null,
        public string $flow = 'message_first',
        public ?string $timezone = null,
        public ?string $locale = null,
        public array $attachments = [],
        public array $page = [],
    ) {}
}
