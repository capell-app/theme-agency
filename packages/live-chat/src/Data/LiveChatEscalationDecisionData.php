<?php

declare(strict_types=1);

namespace Capell\LiveChat\Data;

use Capell\LiveChat\Enums\EscalationReason;
use Capell\LiveChat\Enums\LiveChatPriority;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class LiveChatEscalationDecisionData extends Data
{
    public function __construct(
        public bool $shouldEscalate,
        public ?EscalationReason $reason = null,
        public LiveChatPriority $priority = LiveChatPriority::Normal,
        public ?string $routeTo = null,
        public ?string $message = null,
    ) {}
}
