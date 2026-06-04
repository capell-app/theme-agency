<?php

declare(strict_types=1);

namespace Capell\Comments\Actions;

use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Models\Comment;
use Capell\Comments\Support\CommentableRegistry;
use Capell\Comments\Support\CommentSettingsResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class TransitionCommentStatusAction
{
    use AsAction;

    public function __construct(
        private readonly CommentableRegistry $commentableRegistry,
        private readonly CommentSettingsResolver $settings,
    ) {}

    public function handle(Comment $comment, CommentStatus $status, ?int $moderatorId = null, ?string $note = null): Comment
    {
        return DB::transaction(function () use ($comment, $status, $moderatorId, $note): Comment {
            /** @var Comment $lockedComment */
            $lockedComment = Comment::query()->lockForUpdate()->findOrFail($comment->getKey());
            $previousStatus = $lockedComment->status;

            if ($previousStatus === $status) {
                return $lockedComment;
            }

            if ($status === CommentStatus::Approved) {
                $this->assertCanApprove($lockedComment);
            }

            $timestamps = match ($status) {
                CommentStatus::Approved => ['approved_at' => now(), 'rejected_at' => null, 'marked_spam_at' => null, 'archived_at' => null],
                CommentStatus::Rejected => ['rejected_at' => now()],
                CommentStatus::Spam => ['marked_spam_at' => now()],
                CommentStatus::Archived => ['archived_at' => now()],
                CommentStatus::PendingApproval, CommentStatus::PendingEmailVerification => [],
            };

            $lockedComment->forceFill([
                'status' => $status,
                'moderated_by' => $moderatorId,
                'moderation_note' => $note,
                ...$timestamps,
            ])->save();

            CommentModerationEventAction::run(
                comment: $lockedComment,
                action: 'status_changed',
                previousStatus: $previousStatus,
                newStatus: $status,
                moderatorId: $moderatorId,
                note: $note,
            );

            $refreshedComment = $lockedComment->refresh();

            if ($previousStatus->isPubliclyVisible() !== $status->isPubliclyVisible()) {
                InvalidateCommentableCacheAction::run($refreshedComment->commentable);
            }

            if (! $previousStatus->isPubliclyVisible() && $status === CommentStatus::Approved) {
                RequestCommentReplyNotificationAction::run($refreshedComment);
            }

            return $refreshedComment;
        });
    }

    private function assertCanApprove(Comment $comment): void
    {
        $commentable = $comment->commentable;
        $commentableType = $commentable instanceof Model
            ? $this->commentableRegistry->forModel($commentable)?->key
            : null;

        if ($this->settings->requiresEmailVerification($comment->site_id, $commentableType) && $comment->email_verified_at === null) {
            throw ValidationException::withMessages([
                'status' => __('capell-comments::messages.email_must_be_verified'),
            ]);
        }

        if ($comment->parent_id === null) {
            return;
        }

        $parent = $comment->parent()->first();
        if ($parent instanceof Comment && $parent->isPubliclyVisible()) {
            return;
        }

        throw ValidationException::withMessages([
            'status' => __('capell-comments::messages.parent_must_be_approved'),
        ]);
    }
}
