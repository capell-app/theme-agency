<?php

declare(strict_types=1);

namespace Capell\AccessGate\Data;

final readonly class AnnouncementBarData
{
    public function __construct(
        public string $message,
        public string $shortMessage,
        public ?string $linkLabel,
        public ?string $linkShortLabel,
        public ?string $linkUrl,
    ) {}
}
