<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Data\CommentableTypeData;
use Capell\Comments\Data\PublicCommentData;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Models\Comment;
use Capell\Comments\Support\CommentableRegistry;
use Capell\Comments\Support\CommentSettingsResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class BuildPublicThreadAction
{
    use AsAction;

    public function __construct(
        private readonly CommentableRegistry $commentableRegistry,
        private readonly CommentSettingsResolver $settings,
    ) {}

    /**
     * @return list<PublicCommentData>
     */
    public function handle(Model $commentable, int $rootLimit = 20): array
    {
        if (! $this->canRead($commentable)) {
            return [];
        }

        /** @var EloquentCollection<int, Comment> $roots */
        $roots = Comment::query()
            ->with(['author'])
            ->where('commentable_type', $commentable->getMorphClass())
            ->where('commentable_id', $commentable->getKey())
            ->where('status', CommentStatus::Approved)
            ->when(
                is_numeric($commentable->getAttribute('language_id')),
                fn (Builder $query): Builder => $query->where('language_id', (int) $commentable->getAttribute('language_id')),
            )
            ->whereNull('parent_id')
            ->oldest('submitted_at')
            ->limit(max(1, $rootLimit))
            ->get();

        if ($roots->isEmpty()) {
            return [];
        }

        /** @var list<int> $rootIds */
        $rootIds = $roots->pluck('id')->map(fn (mixed $id): int => (int) $id)->all();

        /** @var Collection<int, Comment> $comments */
        $comments = Comment::query()
            ->with(['author'])
            ->where('commentable_type', $commentable->getMorphClass())
            ->where('commentable_id', $commentable->getKey())
            ->where('status', CommentStatus::Approved)
            ->when(
                is_numeric($commentable->getAttribute('language_id')),
                fn (Builder $query): Builder => $query->where('language_id', (int) $commentable->getAttribute('language_id')),
            )
            ->where(function (Builder $query) use ($rootIds): void {
                $query
                    ->whereIn('id', $rootIds)
                    ->orWhereIn('root_id', $rootIds);
            })
            ->oldest('submitted_at')
            ->get();

        $byParent = $comments->groupBy(fn (Comment $comment): int => (int) ($comment->parent_id ?? 0));

        return $roots
            ->map(fn (Comment $comment): PublicCommentData => $this->toData($comment, $byParent))
            ->values()
            ->all();
    }

    private function canRead(Model $commentable): bool
    {
        $commentableType = $this->commentableRegistry->forModel($commentable);

        if (! $commentableType instanceof CommentableTypeData || ! $commentableType->isVisible($commentable)) {
            return false;
        }

        $siteId = $commentableType->siteId($commentable);

        return $siteId !== null
            && $this->settings->enabled($siteId, $commentableType->key)
            && $this->settings->publicationPolicy($siteId, $commentableType->key) !== CommentPublicationPolicy::Disabled;
    }

    /**
     * @param  Collection<int, Collection<int, Comment>>  $byParent
     */
    private function toData(Comment $comment, Collection $byParent): PublicCommentData
    {
        /** @var Collection<int, Comment> $children */
        $children = $byParent->get((int) $comment->getKey(), collect());

        return new PublicCommentData(
            publicId: (string) $comment->public_id,
            body: $comment->body,
            authorName: (string) $comment->author->name,
            submittedAt: $comment->submitted_at->toImmutable(),
            depth: (int) $comment->depth,
            replyCount: $children->count(),
            children: $children
                ->map(fn (Comment $child): PublicCommentData => $this->toData($child, $byParent))
                ->values()
                ->all(),
        );
    }
}
