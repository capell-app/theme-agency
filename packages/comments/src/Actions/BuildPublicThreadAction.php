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
use Illuminate\Support\Collection;
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
     * @return list<PublicCommentData>
     */
    public function handle(Model $commentable, int $rootLimit = 20): array
    {
        if (! $this->canRead($commentable)) {
            return [];
        }

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
                is_numeric($languageId),
                fn (Builder $query): Builder => $query->where('language_id', (int) $languageId),
            )
            ->where(function (Builder $query) use ($rootIds): void {
                $query
                    ->whereIn('id', $rootIds)
                    ->orWhereIn('root_id', $rootIds);
            })
            ->oldest('submitted_at')
            ->get();

        $byParent = $comments->groupBy(fn (Comment $comment): int => (int) ($comment->parent_id ?? 0));

        return array_values($roots
            ->map(fn (Comment $comment): PublicCommentData => $this->toData($comment, $byParent))
            ->values()
            ->all());
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

    private function optionalAttribute(Model $model, string $key): mixed
    {
        return array_key_exists($key, $model->getAttributes())
            ? $model->getAttribute($key)
            : null;
    }

    /**
     * @param  Collection<int, Collection<int, Comment>>  $byParent
     */
    private function toData(Comment $comment, Collection $byParent): PublicCommentData
    {
        /** @var Collection<int, Comment> $children */
        $children = $byParent->get((int) $comment->getKey(), collect());
        $author = $comment->author;
        $submittedAt = $comment->submitted_at;

        throw_unless($author instanceof CommentAuthor, RuntimeException::class, 'Public comments require an author.');
        throw_unless($submittedAt instanceof CarbonImmutable, RuntimeException::class, 'Public comments require a submitted timestamp.');

        return new PublicCommentData(
            publicId: (string) $comment->public_id,
            body: $comment->body,
            authorName: (string) $author->name,
            submittedAt: $submittedAt,
            depth: (int) $comment->depth,
            replyCount: $children->count(),
            children: array_values($children
                ->map(fn (Comment $child): PublicCommentData => $this->toData($child, $byParent))
                ->values()
                ->all()),
        );
    }
}
