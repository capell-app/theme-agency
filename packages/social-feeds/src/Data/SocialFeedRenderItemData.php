<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Data;

use Carbon\CarbonImmutable;

final readonly class SocialFeedRenderItemData
{
    public function __construct(
        public string $provider,
        public string $type,
        public ?string $text,
        public ?string $permalink,
        public ?string $mediaUrl,
        public ?string $thumbnailUrl,
        public ?string $authorName,
        public ?string $authorAvatarUrl,
        public ?CarbonImmutable $publishedAt,
    ) {}
}
