<?php

declare(strict_types=1);

namespace Capell\LiveChat\Data;

use Capell\LiveChat\Enums\LiveChatAIRunStatus;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class LiveChatAIRunData extends Data
{
    /**
     * @param  list<int>  $sourceDocumentIds
     * @param  array<string, mixed>  $inputPayload
     * @param  array<string, mixed>  $outputPayload
     */
    public function __construct(
        public readonly string $capabilityKey,
        public readonly ?int $installationId = null,
        public readonly ?int $conversationId = null,
        public readonly ?int $messageId = null,
        public readonly string $modelTier = 'local',
        public readonly ?float $confidence = null,
        public readonly ?int $latencyMs = null,
        public readonly LiveChatAIRunStatus $status = LiveChatAIRunStatus::Succeeded,
        public readonly array $sourceDocumentIds = [],
        public readonly ?string $refusalReason = null,
        public readonly ?string $errorMessage = null,
        public readonly array $inputPayload = [],
        public readonly array $outputPayload = [],
    ) {}
}
