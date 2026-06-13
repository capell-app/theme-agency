<?php

declare(strict_types=1);

namespace Capell\LiveChat\Data;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class LiveChatWidgetConfigData extends Data
{
    /**
     * @param  array<string, string>  $branding
     * @param  list<array{name: string, path: string, delay_seconds: int, message: string}>  $proactiveTriggers
     * @param  array<string, string>  $labels
     */
    public function __construct(
        public bool $enabled,
        public string $agentName,
        public string $avatarInitials,
        public string $brandName,
        public string $welcomeMessage,
        public string $aiDisclosure,
        public string $messagePlaceholder,
        public string $messageFirstLabel,
        public string $detailsFirstLabel,
        public string $handoffLabel,
        public string $statusMessage,
        public string $startUrl,
        public string $messageUrl,
        public string $handoffUrl,
        public array $branding,
        public array $proactiveTriggers = [],
        public array $labels = [],
    ) {}
}
