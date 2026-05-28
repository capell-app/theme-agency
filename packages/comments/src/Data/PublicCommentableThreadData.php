<?php

declare(strict_types=1);

namespace Capell\Comments\Data;

use Spatie\LaravelData\Data;

final class PublicCommentableThreadData extends Data
{
    /**
     * @param  list<PublicCommentData>  $comments
     */
    public function __construct(
        public readonly string $commentableType,
        public readonly string $label,
        public readonly ?string $url,
        public readonly int $siteId,
        public readonly array $comments,
    ) {}
}
