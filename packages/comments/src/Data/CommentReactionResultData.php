<?php

declare(strict_types=1);

namespace Capell\Comments\Data;

use Spatie\LaravelData\Data;

final class CommentReactionResultData extends Data
{
    public function __construct(
        public readonly string $commentPublicId,
        public readonly int $reactionCount,
        public readonly bool $reacted,
    ) {}
}
