<?php

declare(strict_types=1);

namespace Capell\Comments\Data;

use Spatie\LaravelData\Data;

final class CommentSpamScoreData extends Data
{
    /**
     * @param  list<string>  $reasons
     */
    public function __construct(
        public readonly int $linkCount,
        public readonly array $reasons,
    ) {}

    public function isSpam(): bool
    {
        return $this->reasons !== [];
    }
}
