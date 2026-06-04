<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Data\CommentPrivacyRetentionResultData;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Models\CommentToken;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

final class ApplyCommentPrivacyRetentionAction
{
    use AsAction;

    public function handle(?int $retentionDays = null, ?string $email = null, ?int $siteId = null, bool $dryRun = false): CommentPrivacyRetentionResultData
    {
        $resolvedRetentionDays = max(1, $retentionDays ?? (int) config('capell-comments.retention.days', 180));
        $cutoff = CarbonImmutable::now()->subDays($resolvedRetentionDays);

        return DB::transaction(function () use ($resolvedRetentionDays, $email, $siteId, $dryRun, $cutoff): CommentPrivacyRetentionResultData {
            $authorSummary = $this->anonymizeAuthors($email, $siteId, $dryRun);
            $expiredTokensQuery = $this->expiredTokensQuery($cutoff);
            $matchedExpiredTokens = (clone $expiredTokensQuery)->count();
            $deletedExpiredTokens = $dryRun ? 0 : $expiredTokensQuery->delete();
            $staleIdentifiersQuery = $this->staleCommentIdentifiersQuery($cutoff);
            $matchedStaleCommentIdentifiers = (clone $staleIdentifiersQuery)->count();
            $prunedStaleCommentIdentifiers = $dryRun ? 0 : $staleIdentifiersQuery->update($this->commentIdentifierErasureAttributes());

            return new CommentPrivacyRetentionResultData(
                retentionDays: $resolvedRetentionDays,
                dryRun: $dryRun,
                matchedAuthors: $authorSummary['matched_authors'],
                anonymizedAuthors: $authorSummary['anonymized_authors'],
                matchedAuthorComments: $authorSummary['matched_author_comments'],
                anonymizedAuthorComments: $authorSummary['anonymized_author_comments'],
                matchedAuthorTokens: $authorSummary['matched_author_tokens'],
                deletedAuthorTokens: $authorSummary['deleted_author_tokens'],
                matchedExpiredTokens: $matchedExpiredTokens,
                deletedExpiredTokens: $deletedExpiredTokens,
                matchedStaleCommentIdentifiers: $matchedStaleCommentIdentifiers,
                prunedStaleCommentIdentifiers: $prunedStaleCommentIdentifiers,
            );
        });
    }

    /**
     * @return array{
     *     matched_authors: int,
     *     anonymized_authors: int,
     *     matched_author_comments: int,
     *     anonymized_author_comments: int,
     *     matched_author_tokens: int,
     *     deleted_author_tokens: int
     * }
     */
    private function anonymizeAuthors(?string $email, ?int $siteId, bool $dryRun): array
    {
        $emailHash = CommentAuthor::emailHash($email);

        if ($emailHash === null) {
            return [
                'matched_authors' => 0,
                'anonymized_authors' => 0,
                'matched_author_comments' => 0,
                'anonymized_author_comments' => 0,
                'matched_author_tokens' => 0,
                'deleted_author_tokens' => 0,
            ];
        }

        $authors = CommentAuthor::query()
            ->where('email_hash', $emailHash)
            ->when(
                $siteId !== null,
                fn (Builder $query): Builder => $query->where('site_id', $siteId),
            )
            ->get();

        $matchedAuthorComments = 0;
        $anonymizedAuthorComments = 0;
        $matchedAuthorTokens = 0;
        $deletedAuthorTokens = 0;

        foreach ($authors as $author) {
            $commentsQuery = $author->comments()
                ->where(function (Builder $query): void {
                    $query
                        ->whereNotNull('visitor_ip_hash')
                        ->orWhereNotNull('visitor_user_agent_hash')
                        ->orWhereNotNull('moderation_note');
                });
            $tokensQuery = $author->tokens();

            $matchedAuthorComments += (clone $commentsQuery)->count();
            $matchedAuthorTokens += (clone $tokensQuery)->count();

            if ($dryRun) {
                continue;
            }

            $anonymizedAuthorComments += $commentsQuery->update($this->commentIdentifierErasureAttributes());
            $deletedAuthorTokens += $tokensQuery->delete();
            $this->anonymizeAuthor($author);
        }

        return [
            'matched_authors' => $authors->count(),
            'anonymized_authors' => $dryRun ? 0 : $authors->count(),
            'matched_author_comments' => $matchedAuthorComments,
            'anonymized_author_comments' => $anonymizedAuthorComments,
            'matched_author_tokens' => $matchedAuthorTokens,
            'deleted_author_tokens' => $deletedAuthorTokens,
        ];
    }

    private function anonymizeAuthor(CommentAuthor $author): void
    {
        $author->forceFill([
            'user_type' => null,
            'user_id' => null,
            'name' => __('capell-comments::generic.anonymized_author'),
            'email' => null,
            'email_hash' => null,
            'email_verified_at' => null,
            'trusted_at' => null,
            'blocked_at' => null,
            'internal_notes' => null,
        ]);
        $author->save();
    }

    /**
     * @return Builder<CommentToken>
     */
    private function expiredTokensQuery(CarbonImmutable $cutoff): Builder
    {
        return CommentToken::query()
            ->where(function (Builder $query) use ($cutoff): void {
                $query
                    ->where(function (Builder $query) use ($cutoff): void {
                        $query
                            ->whereNotNull('expires_at')
                            ->where('expires_at', '<=', $cutoff);
                    })
                    ->orWhere(function (Builder $query) use ($cutoff): void {
                        $query
                            ->whereNotNull('consumed_at')
                            ->where('consumed_at', '<=', $cutoff);
                    });
            });
    }

    /**
     * @return Builder<Comment>
     */
    private function staleCommentIdentifiersQuery(CarbonImmutable $cutoff): Builder
    {
        return Comment::query()
            ->where('submitted_at', '<=', $cutoff)
            ->where(function (Builder $query): void {
                $query
                    ->whereNotNull('visitor_ip_hash')
                    ->orWhereNotNull('visitor_user_agent_hash')
                    ->orWhereNotNull('moderation_note');
            });
    }

    /**
     * @return array{visitor_ip_hash: null, visitor_user_agent_hash: null, moderation_note: null}
     */
    private function commentIdentifierErasureAttributes(): array
    {
        return [
            'visitor_ip_hash' => null,
            'visitor_user_agent_hash' => null,
            'moderation_note' => null,
        ];
    }
}
