<?php

declare(strict_types=1);

namespace Capell\AiCreator\Data;

use Spatie\LaravelData\Data;

final class AiCreatorPreviewData extends Data
{
    /**
     * @param  array<int, array{package: string, level: string, reason: string, consequence: string}>  $recommendations
     * @param  array<int, string>  $requiredPackages
     */
    public function __construct(
        public readonly int $sessionId,
        public readonly string $intent,
        public readonly array $recommendations,
        public readonly array $requiredPackages,
    ) {}

    /**
     * @return array{sessionId: int, intent: string, recommendations: array<int, array{package: string, level: string, reason: string, consequence: string}>, requiredPackages: array<int, string>}
     */
    public function toPayload(): array
    {
        return [
            'sessionId' => $this->sessionId,
            'intent' => $this->intent,
            'recommendations' => $this->recommendations,
            'requiredPackages' => $this->requiredPackages,
        ];
    }
}
