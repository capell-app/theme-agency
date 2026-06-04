<?php

declare(strict_types=1);

namespace Capell\Comments\Tests\Fixtures;

use Capell\Comments\Contracts\CommentSpamProvider;
use Capell\Comments\Data\CommentSpamCheckData;
use Capell\Comments\Data\CommentSpamScoreData;

final class FlaggingCommentSpamProvider implements CommentSpamProvider
{
    public function score(CommentSpamCheckData $check): CommentSpamScoreData
    {
        return new CommentSpamScoreData(
            linkCount: $check->linkCount,
            reasons: [
                'external_fixture:site:' . (string) $check->siteId,
                'external_fixture:type:' . (string) $check->commentableType,
            ],
        );
    }
}
