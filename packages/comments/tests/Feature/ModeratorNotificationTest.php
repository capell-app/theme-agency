<?php

declare(strict_types=1);

use Capell\Comments\Enums\CommentStatus;
use Capell\Comments\Events\CommentCreated;
use Capell\Comments\Listeners\NotifyModeratorsOfNewComment;
use Capell\Comments\Models\Comment;
use Capell\Comments\Models\CommentAuthor;
use Capell\Comments\Notifications\ModerateCommentNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('registers the moderator notification listener for created comments', function (): void {
    $listeners = Event::getRawListeners()[CommentCreated::class] ?? [];

    expect($listeners)->toContain(NotifyModeratorsOfNewComment::class);
});

it('notifies configured moderators for comments waiting on review', function (): void {
    Notification::fake();
    config()->set('capell-comments.notifications.moderators', [
        'moderator@example.com',
        'moderator@example.com',
        'invalid-email',
        'second@example.com',
    ]);

    $page = $this->createCommentsPage();
    $author = CommentAuthor::factory()->create(['site_id' => $page->site_id, 'name' => 'Public Reader']);
    $comment = Comment::factory()->pendingApproval()->create([
        'site_id' => $page->site_id,
        'comment_author_id' => $author->getKey(),
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
    ]);

    (new NotifyModeratorsOfNewComment)->handle(new CommentCreated($comment));

    Notification::assertSentOnDemandTimes(ModerateCommentNotification::class, 2);
    Notification::assertSentOnDemand(
        ModerateCommentNotification::class,
        static function (ModerateCommentNotification $notification): bool {
            $mail = $notification->toMail(new class {});

            return $mail->subject === __('capell-comments::messages.moderator_notification_subject')
                && in_array('Author: Public Reader', $mail->introLines, true);
        },
    );
});

it('does not notify moderators for spam or approved comments', function (CommentStatus $status): void {
    Notification::fake();
    config()->set('capell-comments.notifications.moderators', ['moderator@example.com']);

    $page = $this->createCommentsPage();
    $comment = Comment::factory()->create([
        'site_id' => $page->site_id,
        'status' => $status,
        'commentable_type' => $page->getMorphClass(),
        'commentable_id' => $page->getKey(),
    ]);

    (new NotifyModeratorsOfNewComment)->handle(new CommentCreated($comment));

    Notification::assertNothingSent();
})->with([
    'approved' => [CommentStatus::Approved],
    'spam' => [CommentStatus::Spam],
]);
