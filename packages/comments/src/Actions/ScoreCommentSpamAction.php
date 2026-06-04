<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Contracts\CommentSpamProvider;
use Capell\Comments\Data\CommentSpamCheckData;
use Capell\Comments\Data\CommentSpamScoreData;
use Lorisleiva\Actions\Concerns\AsAction;

final class ScoreCommentSpamAction
{
    use AsAction;

    public function __construct(
        private readonly CommentSpamProvider $provider,
    ) {}

    public function handle(string $body, int $linkCount, ?CommentSpamCheckData $check = null): CommentSpamScoreData
    {
        return $this->provider->score($check ?? new CommentSpamCheckData(
            body: $body,
            linkCount: $linkCount,
        ));
    }
}
