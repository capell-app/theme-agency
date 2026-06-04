<?php

declare(strict_types=1);

namespace Capell\Comments\Contracts;

use Capell\Comments\Data\CommentSpamCheckData;
use Capell\Comments\Data\CommentSpamScoreData;

interface CommentSpamProvider
{
    public function score(CommentSpamCheckData $check): CommentSpamScoreData;
}
