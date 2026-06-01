<?php

declare(strict_types=1);

namespace Capell\Comments\Events;

use Capell\Comments\Models\Comment;
use Illuminate\Foundation\Events\Dispatchable;

final class CommentCreated
{
    use Dispatchable;

    public function __construct(
        public readonly Comment $comment,
    ) {}
}
