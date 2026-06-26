<?php

declare(strict_types=1);

namespace Capell\SeoSuite\DataObjects;

use Capell\AIOrchestrator\Contracts\AiCreatorContextInterface;

final readonly class AiCreatorData implements AiCreatorContextInterface
{
    public function __construct(
        public int $siteId,
        public int $userId,
        public string $intent,
        public int $pageCount = 1,
        public ?string $tone = null,
        public ?string $industry = null,
        public ?string $targetAudience = null,
        public ?string $brandVoiceNotes = null,
        public ?int $existingSessionId = null,
    ) {}

    public function getSiteId(): int
    {
        return $this->siteId;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }
}
