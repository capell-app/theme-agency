<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Data\CommentReactionResultData;
use Capell\Comments\Enums\CommentReactionType;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentReaction;
use Capell\Comments\Support\VisitorHasher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

final class ToggleCommentReactionAction
{
    use AsAction;

    public function __construct(
        private readonly VisitorHasher $visitorHasher,
    ) {}

    public function handle(
        Model $commentable,
        string $commentPublicId,
        ?Model $user = null,
        ?string $ipAddress = null,
        ?string $userAgent = null,
        CommentReactionType $type = CommentReactionType::Like,
    ): CommentReactionResultData {
        return DB::transaction(function () use ($commentable, $commentPublicId, $user, $ipAddress, $userAgent, $type): CommentReactionResultData {
            /** @var Comment|null $comment */
            $comment = Comment::query()
                ->where('public_id', $commentPublicId)
                ->where('commentable_type', $commentable->getMorphClass())
                ->where('commentable_id', $commentable->getKey())
                ->where('status', CommentStatus::Approved)
                ->lockForUpdate()
                ->first();

            if (! $comment instanceof Comment) {
                throw ValidationException::withMessages([
                    'reaction' => __('capell-comments::messages.reaction_unavailable'),
                ]);
            }

            $actor = $this->actorAttributes($comment->site_id, $user, $ipAddress, $userAgent);
            $existingReaction = $this->existingReactionQuery($comment, $type, $actor)->first();

            if ($existingReaction instanceof CommentReaction) {
                $existingReaction->delete();

                return new CommentReactionResultData(
                    commentPublicId: (string) $comment->public_id,
                    reactionCount: $this->reactionCount($comment, $type),
                    reacted: false,
                );
            }

            CommentReaction::query()->create([
                'site_id' => $comment->site_id,
                'comment_id' => $comment->getKey(),
                'type' => $type,
                ...$actor,
            ]);

            return new CommentReactionResultData(
                commentPublicId: (string) $comment->public_id,
                reactionCount: $this->reactionCount($comment, $type),
                reacted: true,
            );
        });
    }

    /**
     * @return array{user_type?: string, user_id?: int|string, visitor_ip_hash?: string, visitor_user_agent_hash?: string}
     */
    private function actorAttributes(int $siteId, ?Model $user, ?string $ipAddress, ?string $userAgent): array
    {
        if ($user instanceof Model) {
            $userId = $user->getKey();

            if (! is_int($userId) && ! is_string($userId)) {
                throw ValidationException::withMessages([
                    'reaction' => __('capell-comments::messages.reaction_unavailable'),
                ]);
            }

            return [
                'user_type' => $user->getMorphClass(),
                'user_id' => $userId,
            ];
        }

        $visitorIpHash = $this->visitorHasher->hash($ipAddress, $siteId);

        if ($visitorIpHash === null) {
            throw ValidationException::withMessages([
                'reaction' => __('capell-comments::messages.reaction_unavailable'),
            ]);
        }

        $visitorUserAgentHash = $this->visitorHasher->hash($userAgent, $siteId) ?? $this->visitorHasher->hash('unknown', $siteId);

        if ($visitorUserAgentHash === null) {
            throw ValidationException::withMessages([
                'reaction' => __('capell-comments::messages.reaction_unavailable'),
            ]);
        }

        return [
            'visitor_ip_hash' => $visitorIpHash,
            'visitor_user_agent_hash' => $visitorUserAgentHash,
        ];
    }

    /**
     * @param  array{user_type?: string, user_id?: int|string, visitor_ip_hash?: string, visitor_user_agent_hash?: string}  $actor
     * @return Builder<CommentReaction>
     */
    private function existingReactionQuery(Comment $comment, CommentReactionType $type, array $actor): Builder
    {
        /** @var Builder<CommentReaction> $query */
        $query = CommentReaction::query()
            ->where('comment_id', $comment->getKey())
            ->where('type', $type);

        $userType = $actor['user_type'] ?? null;
        $userId = $actor['user_id'] ?? null;

        if (is_string($userType) && (is_int($userId) || is_string($userId))) {
            return $query
                ->where('user_type', $userType)
                ->where('user_id', $userId);
        }

        $visitorIpHash = $actor['visitor_ip_hash'] ?? '';
        $visitorUserAgentHash = $actor['visitor_user_agent_hash'] ?? '';

        return $query
            ->where('visitor_ip_hash', $visitorIpHash)
            ->where('visitor_user_agent_hash', $visitorUserAgentHash);
    }

    private function reactionCount(Comment $comment, CommentReactionType $type): int
    {
        return CommentReaction::query()
            ->where('comment_id', $comment->getKey())
            ->where('type', $type)
            ->count();
    }
}
