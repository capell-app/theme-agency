<?php

declare(strict_types=1);

namespace Capell\LiveChat\Data;

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
     */
    public function __construct(
        public string $body,
        public float $confidence,
        public LiveChatIntent $intent = LiveChatIntent::General,
        public bool $requiresContact = false,
        public array $suggestedFields = [],
        public array $knowledgeSources = [],
    ) {}
}
