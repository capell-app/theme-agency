<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Data\CommentSpamScoreData;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class ScoreCommentSpamAction
{
    use AsAction;

    public function handle(string $body, int $linkCount): CommentSpamScoreData
    {
        $reasons = [];
        $maxLinks = $this->maxLinks();

        if ($maxLinks >= 0 && $linkCount > $maxLinks) {
            $reasons[] = 'too_many_links';
        }

        foreach ($this->blockedTerms() as $blockedTerm) {
            if (str_contains(Str::lower($body), Str::lower($blockedTerm))) {
                $reasons[] = 'blocked_term:' . $blockedTerm;
            }
        }

        return new CommentSpamScoreData(
            linkCount: $linkCount,
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
