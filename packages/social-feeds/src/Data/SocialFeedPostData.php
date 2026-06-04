<?php

declare(strict_types=1);

namespace Capell\SocialFeeds\Data;

use Capell\SocialFeeds\Enums\SocialFeedItemType;
use Carbon\CarbonImmutable;

final readonly class SocialFeedPostData
{
    /**
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        public string $externalId,
        public SocialFeedItemType $type,
        public ?string $text = null,
        public ?string $permalink = null,
        public ?string $mediaUrl = null,
        public ?string $thumbnailUrl = null,
        public ?string $authorName = null,
        public ?string $authorAvatarUrl = null,
        public ?CarbonImmutable $publishedAt = null,
        public array $raw = [],
    ) {}
}
