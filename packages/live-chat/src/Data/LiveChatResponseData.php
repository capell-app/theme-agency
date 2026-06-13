<?php

declare(strict_types=1);

namespace Capell\LiveChat\Data;

use Capell\LiveChat\Enums\LiveChatAIRunStatus;
use Capell\LiveChat\Enums\LiveChatIntent;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class LiveChatResponseData extends Data
{
    /**
     * @param  list<string>  $suggestedFields
     * @param  list<string>  $knowledgeSources
     * @param  list<int>  $sourceDocumentIds
     */
    public function __construct(
        public string $body,
        public float $confidence,
        public LiveChatIntent $intent = LiveChatIntent::General,
        public bool $requiresContact = false,
        public array $suggestedFields = [],
        public array $knowledgeSources = [],
        public array $sourceDocumentIds = [],
        public string $modelTier = 'local',
        public string $sourceArea = 'local-responder',
        public LiveChatAIRunStatus $aiRunStatus = LiveChatAIRunStatus::Succeeded,
        public ?string $knowledgeGapReason = null,
        public ?string $refusalReason = null,
    ) {}
}
