<?php

declare(strict_types=1);

namespace Capell\Comments\Support\Spam;

use Capell\Comments\Contracts\CommentSpamProvider;
use Capell\Comments\Data\CommentSpamCheckData;
use Capell\Comments\Data\CommentSpamScoreData;
use Illuminate\Support\Str;

final class LocalCommentSpamProvider implements CommentSpamProvider
{
    public function score(CommentSpamCheckData $check): CommentSpamScoreData
    {
        $reasons = [];
        $maxLinks = $this->maxLinks();

        if ($maxLinks >= 0 && $check->linkCount > $maxLinks) {
            $reasons[] = 'too_many_links';
        }

        foreach ($this->blockedTerms() as $blockedTerm) {
            if (str_contains(Str::lower($check->body), Str::lower($blockedTerm))) {
                $reasons[] = 'blocked_term:' . $blockedTerm;
            }
        }

        return new CommentSpamScoreData(
            linkCount: $check->linkCount,
            reasons: array_values(array_unique($reasons)),
        );
    }

    private function maxLinks(): int
    {
        $maxLinks = config('capell-comments.spam.max_links', 3);

        return is_numeric($maxLinks) ? (int) $maxLinks : 3;
    }

    /**
     * @return list<string>
     */
    private function blockedTerms(): array
    {
        $blockedTerms = config('capell-comments.spam.blocked_terms', []);

        if (! is_array($blockedTerms)) {
            return [];
        }

        return array_values(array_filter(
            array_map(
                static fn (mixed $blockedTerm): string => is_string($blockedTerm) ? trim($blockedTerm) : '',
                $blockedTerms,
            ),
            static fn (string $blockedTerm): bool => $blockedTerm !== '',
        ));
    }
}
