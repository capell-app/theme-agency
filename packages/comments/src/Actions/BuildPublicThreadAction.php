<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Data\CommentableTypeData;
use Capell\Comments\Data\PublicCommentData;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Support\CommentableRegistry;
use Capell\Comments\Support\CommentSettingsResolver;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

class BuildPublicThreadAction
{
    use AsAction;

    public function __construct(
        private readonly CommentableRegistry $commentableRegistry,
        private readonly CommentSettingsResolver $settings,
    ) {}

    /**
     * @param  array<string, int>  $replyLimitsByPublicId
     * @return list<PublicCommentData>
     */
    public function handle(Model $commentable, ?int $rootLimit = null, ?int $replyLimit = null, array $replyLimitsByPublicId = []): array
    {
        $commentableType = $this->commentableRegistry->forModel($commentable);

        if (! $commentableType instanceof CommentableTypeData || ! $commentableType->isVisible($commentable)) {
            return [];
        }

        $siteId = $commentableType->siteId($commentable);

        if ($siteId === null || ! $this->canRead($siteId, $commentableType->key)) {
            return [];
        }

        $resolvedRootLimit = max(1, $rootLimit ?? $this->settings->rootPageSize($siteId, $commentableType->key));
        $resolvedReplyLimit = max(0, $replyLimit ?? $this->settings->replyPageSize($siteId, $commentableType->key));
        $maxDepth = $this->settings->maxDepth($siteId, $commentableType->key);
        $languageId = $this->optionalAttribute($commentable, 'language_id');

        /** @var EloquentCollection<int, Comment> $roots */
        $roots = Comment::query()
            ->with(['author'])
            ->where('commentable_type', $commentable->getMorphClass())
            ->where('commentable_id', $commentable->getKey())
            ->where('status', CommentStatus::Approved)
            ->when(
                is_numeric($languageId),
                fn (Builder $query): Builder => $query->where('language_id', (int) $languageId),
            )
            ->whereNull('parent_id')
            ->oldest('submitted_at')
            ->oldest('id')
            ->limit($resolvedRootLimit)
            ->get();

        if ($roots->isEmpty()) {
            return [];
        }

        return array_values($roots
            ->map(fn (Comment $comment): PublicCommentData => $this->toData(
                comment: $comment,
                languageId: is_numeric($languageId) ? (int) $languageId : null,
                replyLimit: $resolvedReplyLimit,
                maxDepth: $maxDepth,
                replyLimitsByPublicId: $replyLimitsByPublicId,
            ))
            ->values()
            ->all());
    }

    private function canRead(int $siteId, string $commentableType): bool
    {
        return $this->settings->enabled($siteId, $commentableType)
            && $this->settings->publicationPolicy($siteId, $commentableType) !== CommentPublicationPolicy::Disabled;
    }

    private function optionalAttribute(Model $model, string $key): mixed
    {
        return array_key_exists($key, $model->getAttributes())
            ? $model->getAttribute($key)
            : null;
    }

    /**
     * @param  array<string, int>  $replyLimitsByPublicId
     */
    private function toData(
        Comment $comment,
        ?int $languageId,
        int $replyLimit,
        int $maxDepth,
        array $replyLimitsByPublicId,
    ): PublicCommentData {
        $author = $comment->author;
        $submittedAt = $comment->submitted_at;
        $children = collect();
        $replyCount = 0;

        if ((int) $comment->depth < $maxDepth) {
            $childrenQuery = $this->approvedChildrenQuery($comment, $languageId);
            $replyCount = (clone $childrenQuery)->count();
            $visibleLimit = max(0, $replyLimitsByPublicId[(string) $comment->public_id] ?? $replyLimit);

            /** @var EloquentCollection<int, Comment> $children */
            $children = $visibleLimit > 0
                ? $childrenQuery
                    ->limit($visibleLimit)
                    ->get()
                : collect();
        }

        throw_unless($author instanceof CommentAuthor, RuntimeException::class, 'Public comments require an author.');
        throw_unless($submittedAt instanceof CarbonImmutable, RuntimeException::class, 'Public comments require a submitted timestamp.');

        return new PublicCommentData(
            publicId: (string) $comment->public_id,
            body: $comment->body,
            authorName: (string) $author->name,
            submittedAt: $submittedAt,
            depth: (int) $comment->depth,
            replyCount: $replyCount,
            hasMoreReplies: $replyCount > $children->count(),
            children: array_values($children
                ->map(fn (Comment $child): PublicCommentData => $this->toData(
                    comment: $child,
                    languageId: $languageId,
                    replyLimit: $replyLimit,
                    maxDepth: $maxDepth,
                    replyLimitsByPublicId: $replyLimitsByPublicId,
                ))
                ->values()
                ->all()),
        );
    }

    /**
     * @return Builder<Comment>
     */
    private function approvedChildrenQuery(Comment $comment, ?int $languageId): Builder
    {
        return Comment::query()
            ->with(['author'])
            ->where('commentable_type', $comment->commentable_type)
            ->where('commentable_id', $comment->commentable_id)
            ->where('status', CommentStatus::Approved)
            ->where('parent_id', $comment->getKey())
            ->when(
                $languageId !== null,
                fn (Builder $query): Builder => $query->where('language_id', $languageId),
            )
            ->oldest('submitted_at')
            ->oldest('id');
    }
}
