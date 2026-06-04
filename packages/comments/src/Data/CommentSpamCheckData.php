<?php

declare(strict_types=1);

namespace Capell\Comments\Data;

use Spatie\LaravelData\Data;

final class CommentSpamCheckData extends Data
{
    public function __construct(
        public readonly string $body,
        public readonly int $linkCount,
        public readonly ?int $siteId = null,
        public readonly ?string $commentableType = null,
        public readonly int|string|null $commentableId = null,
        public readonly ?string $parentPublicId = null,
        public readonly ?string $authorName = null,
        public readonly ?string $authorEmail = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $userAgent = null,
        public readonly ?string $authenticatedUserType = null,
        public readonly int|string|null $authenticatedUserId = null,
    ) {}
}
