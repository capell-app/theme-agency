<?php

declare(strict_types=1);

namespace Capell\Comments\Data;

use Carbon\CarbonImmutable;
use Spatie\LaravelData\Data;

class PublicCommentData extends Data
{
    /**
     * @param  list<PublicCommentData>  $children
     */
    public function __construct(
        public string $publicId,
        public string $body,
        public string $authorName,
        public CarbonImmutable $submittedAt,
        public int $depth,
        public int $replyCount,
        public array $children = [],
    ) {}
}
