<?php

declare(strict_types=1);

use Capell\Comments\Actions\BuildPublicThreadAction;
use Capell\Comments\Actions\ToggleCommentReactionAction;
use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentReaction;
use Illuminate\Validation\ValidationException;

it('toggles a guest reaction and exposes the aggregate count in public thread data', function (): void {
    $page = $this->createCommentsPage();
    $comment = Comment::factory()->create([
        'site_id' => $page->site_id,
        'language_id' => $page->language_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'body' => 'Reactable comment',
    ]);

    $added = ToggleCommentReactionAction::run(
        commentable: $page,
        commentPublicId: (string) $comment->public_id,
        ipAddress: '192.0.2.10',
        userAgent: null,
    );

    $publicComments = BuildPublicThreadAction::run($page);

    expect($added->reacted)->toBeTrue()
        ->and($added->reactionCount)->toBe(1)
        ->and(CommentReaction::query()->where('comment_id', $comment->getKey())->count())->toBe(1)
        ->and($publicComments[0]->reactionCount)->toBe(1);

    $removed = ToggleCommentReactionAction::run(
        commentable: $page,
        commentPublicId: (string) $comment->public_id,
        ipAddress: '192.0.2.10',
        userAgent: null,
    );

    expect($removed->reacted)->toBeFalse()
        ->and($removed->reactionCount)->toBe(0)
        ->and(CommentReaction::query()->where('comment_id', $comment->getKey())->count())->toBe(0);
});

it('rejects reactions for unavailable or non-public comments', function (): void {
    $page = $this->createCommentsPage();
    $comment = Comment::factory()->create([
        'site_id' => $page->site_id,
        'language_id' => $page->language_id,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
        'status' => CommentStatus::PendingApproval,
        'approved_at' => null,
    ]);

    ToggleCommentReactionAction::run(
        commentable: $page,
        commentPublicId: (string) $comment->public_id,
        ipAddress: '192.0.2.10',
        userAgent: 'Comments test',
    );
})->throws(ValidationException::class);
