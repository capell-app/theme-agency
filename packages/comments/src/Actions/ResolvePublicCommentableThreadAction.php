<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Data\CommentableTypeData;
use Capell\Comments\Data\PublicCommentableThreadData;
use Capell\Comments\Enums\CommentPublicationPolicy;
use Capell\Comments\Support\CommentableRegistry;
use Capell\Comments\Support\CommentSettingsResolver;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PublicCommentableThreadData|null run(Model $commentable, ?int $rootLimit = null, ?int $replyLimit = null, array<string, int> $replyLimitsByPublicId = [])
 */
final class ResolvePublicCommentableThreadAction
{
    use AsAction;

    public function __construct(
        private readonly CommentableRegistry $commentableRegistry,
        private readonly CommentSettingsResolver $settings,
    ) {}

    /**
     * @param  array<string, int>  $replyLimitsByPublicId
     */
    public function handle(Model $commentable, ?int $rootLimit = null, ?int $replyLimit = null, array $replyLimitsByPublicId = []): ?PublicCommentableThreadData
    {
        $commentableType = $this->commentableRegistry->forModel($commentable);

        if (! $commentableType instanceof CommentableTypeData || ! $commentableType->isVisible($commentable)) {
            return null;
        }

        $siteId = $commentableType->siteId($commentable);

        if ($siteId === null || ! $this->settings->enabled($siteId, $commentableType->key)) {
            return null;
        }

        if ($this->settings->publicationPolicy($siteId, $commentableType->key) === CommentPublicationPolicy::Disabled) {
            return null;
        }

        return new PublicCommentableThreadData(
            commentableType: $commentableType->key,
            label: $commentableType->label($commentable),
            url: $commentableType->url($commentable),
            siteId: $siteId,
            comments: BuildPublicThreadAction::run($commentable, $rootLimit, $replyLimit, $replyLimitsByPublicId),
        );
    }
}
