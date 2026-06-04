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
use Capell\Core\Models\Language;
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
        $languageId = is_numeric($languageId) ? (int) $languageId : null;

        /** @var EloquentCollection<int, Comment> $roots */
        $roots = Comment::query()
            ->with(['author'])
            ->where('commentable_type', $commentable->getMorphClass())
            ->where('commentable_id', $commentable->getKey())
            ->where('status', CommentStatus::Approved)
            ->when(
                $languageId !== null,
                fn (Builder $query): Builder => $query->where('language_id', $languageId),
            )
            ->whereNull('parent_id')
            ->oldest('submitted_at')
            ->oldest('id')
            ->limit($resolvedRootLimit)
            ->get();

        if ($roots->isEmpty()) {
            return [];
        }

        $locale = $this->localeForLanguageId($languageId);

        return $this->toDataList(
            comments: $roots,
            languageId: $languageId,
            locale: $locale,
            replyLimit: $resolvedReplyLimit,
            maxDepth: $maxDepth,
            replyLimitsByPublicId: $replyLimitsByPublicId,
        );
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
     * @param  EloquentCollection<int, Comment>  $comments
     * @param  array<string, int>  $replyLimitsByPublicId
     * @return list<PublicCommentData>
     */
    private function toDataList(
        EloquentCollection $comments,
        ?int $languageId,
        string $locale,
        int $replyLimit,
        int $maxDepth,
        array $replyLimitsByPublicId,
    ): array {
        $replyCountsByParentId = $this->approvedChildrenCountsByParentId($comments, $languageId, $maxDepth);

        return array_values($comments
            ->map(fn (Comment $comment): PublicCommentData => $this->toData(
                comment: $comment,
                languageId: $languageId,
                locale: $locale,
                replyLimit: $replyLimit,
                maxDepth: $maxDepth,
                replyLimitsByPublicId: $replyLimitsByPublicId,
                replyCountsByParentId: $replyCountsByParentId,
            ))
            ->values()
            ->all());
    }

    /**
     * @param  array<string, int>  $replyLimitsByPublicId
     * @param  array<int, int>  $replyCountsByParentId
     */
    private function toData(
        Comment $comment,
        ?int $languageId,
        string $locale,
        int $replyLimit,
        int $maxDepth,
        array $replyLimitsByPublicId,
        array $replyCountsByParentId,
    ): PublicCommentData {
        $author = $comment->author;
        $submittedAt = $comment->submitted_at;
        /** @var EloquentCollection<int, Comment> $children */
        $children = new EloquentCollection;
        $replyCount = $replyCountsByParentId[(int) $comment->getKey()] ?? 0;

        if ((int) $comment->depth < $maxDepth && $replyCount > 0) {
            $visibleLimit = max(0, $replyLimitsByPublicId[(string) $comment->public_id] ?? $replyLimit);

            /** @var EloquentCollection<int, Comment> $children */
            $children = $visibleLimit > 0
                ? $this->approvedChildrenQuery($comment, $languageId)
                    ->limit($visibleLimit)
                    ->get()
                : new EloquentCollection;
        }

        throw_unless($author instanceof CommentAuthor, RuntimeException::class, 'Public comments require an author.');

        throw_unless($submittedAt instanceof CarbonImmutable, RuntimeException::class, 'Public comments require a submitted timestamp.');

        return new PublicCommentData(
            publicId: (string) $comment->public_id,
            body: $comment->body,
            authorName: (string) $author->name,
            submittedAt: $submittedAt,
            submittedAtForHumans: $submittedAt->settings(['locale' => $locale])->diffForHumans(),
            depth: (int) $comment->depth,
            replyCount: $replyCount,
            hasMoreReplies: $replyCount > $children->count(),
            children: $children->isEmpty()
                ? []
                : $this->toDataList(
                    comments: $children,
                    languageId: $languageId,
                    locale: $locale,
                    replyLimit: $replyLimit,
                    maxDepth: $maxDepth,
                    replyLimitsByPublicId: $replyLimitsByPublicId,
                ),
        );
    }

    /**
     * @param  EloquentCollection<int, Comment>  $comments
     * @return array<int, int>
     */
    private function approvedChildrenCountsByParentId(EloquentCollection $comments, ?int $languageId, int $maxDepth): array
    {
        $parentIds = $comments
            ->filter(fn (Comment $comment): bool => (int) $comment->depth < $maxDepth)
            ->map(fn (Comment $comment): int => (int) $comment->getKey())
            ->filter(fn (int $commentId): bool => $commentId > 0)
            ->values()
            ->all();

        if ($parentIds === []) {
            return [];
        }

        /** @var EloquentCollection<int, Comment> $counts */
        $counts = Comment::query()
            ->select('parent_id')
            ->selectRaw('count(*) as comments_count')
            ->whereIn('parent_id', $parentIds)
            ->where('status', CommentStatus::Approved)
            ->when(
                $languageId !== null,
                fn (Builder $query): Builder => $query->where('language_id', $languageId),
            )
            ->groupBy('parent_id')
            ->get();

        $results = [];

        foreach ($counts as $count) {
            $parentId = $count->getAttribute('parent_id');
            $commentCount = $count->getAttribute('comments_count');

            if (is_numeric($parentId) && is_numeric($commentCount)) {
                $results[(int) $parentId] = (int) $commentCount;
            }
        }

        return $results;
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

    private function localeForLanguageId(?int $languageId): string
    {
        $fallback = $this->normaliseLocale(config('app.locale'), 'en');

        if ($languageId === null) {
            return $fallback;
        }

        $language = Language::query()
            ->select(['id', 'locale', 'code'])
            ->find($languageId);

        if (! $language instanceof Language) {
            return $fallback;
        }

        return $this->normaliseLocale($language->locale, $this->normaliseLocale($language->code, $fallback));
    }

    private function normaliseLocale(mixed $locale, string $fallback): string
    {
        if (! is_string($locale)) {
            return $fallback;
        }

        $locale = trim($locale);

        return $locale === '' ? $fallback : $locale;
    }
}
